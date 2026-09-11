<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\Qualification;
use App\Models\School;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\User;

echo "Starting import...\n";

// Ensure series exists for June 2026
$series = ExamSeries::firstOrCreate(
    ['year' => 2026, 'month' => 'June'],
    [
        'series_code' => 'JUN-2026',
        'series_name' => 'June 2026',
        'is_active' => true
    ]
);

// Ensure school exists
$school = School::firstOrCreate(
    ['school_name' => 'Lucky International School'],
    ['school_code' => 'IN016']
);

// Default admin user for 'uploaded_by'
$admin = User::first(); // Assuming an admin exists

// Qualifications
$asLevel = Qualification::where('qualification_type', 'AS_A_LEVEL')->first();
if (!$asLevel) {
    $asLevel = Qualification::create(['qualification_type' => 'AS_A_LEVEL', 'qualification_name' => 'GCE AS and A Level', 'is_active' => true]);
}

$aLevel = Qualification::where('qualification_type', 'A_LEVEL')->first();
if (!$aLevel) {
    // We don't necessarily need to create A_LEVEL if it's been merged, but keeping it for safety in the script context
    // Actually, I'll just delete this block since the user said there shouldn't be any GCE AS Level
}

// Data
$data = [
    ["DIVYANSH JAIN", "16/02/2010", "IN016 / 0008", ["9618" => ["grade" => "e", "pum" => 43]]],
    ["AYAN ALI KHAN", "21/08/2009", "IN016 / 4001", ["9702" => ["grade" => "c", "pum" => 68], "9701" => ["grade" => "c", "pum" => 60], "9700" => ["grade" => "d", "pum" => 59]]],
    ["AYUSHMAN SOLANKI", "23/07/2009", "IN016 / 4002", ["9709" => ["grade" => "d", "pum" => 52], "9700" => ["grade" => "U", "pum" => 0], "9701" => ["grade" => "U", "pum" => 0]]],
    ["BHARAT PUNIYA", "03/08/2009", "IN016 / 4003", ["9231" => ["grade" => "A*", "pum" => 90], "9702" => ["grade" => "a", "pum" => 86], "9479" => ["grade" => "e", "pum" => 40]]],
    ["DRASHTI DHOOT", "21/01/2010", "IN016 / 4004", ["9700" => ["grade" => "d", "pum" => 52], "9702" => ["grade" => "d", "pum" => 52], "9701" => ["grade" => "e", "pum" => 46]]],
    ["JINAL SOLANKI", "05/11/2008", "IN016 / 4005", ["9702" => ["grade" => "b", "pum" => 70], "9709" => ["grade" => "d", "pum" => 59], "9701" => ["grade" => "e", "pum" => 42]]],
    ["KARTIK RANKAWAT", "20/07/2009", "IN016 / 4006", ["9709" => ["grade" => "c", "pum" => 64], "9702" => ["grade" => "e", "pum" => 44], "9618" => ["grade" => "e", "pum" => 42], "9701" => ["grade" => "U", "pum" => 0]]],
    ["MAYANK MALVIYA", "20/08/2009", "IN016 / 4007", ["9709" => ["grade" => "e", "pum" => 48], "9626" => ["grade" => "U", "pum" => 0], "9701" => ["grade" => "U", "pum" => 0]]],
    ["MAYANK MEWARA", "11/08/2009", "IN016 / 4008", ["9702" => ["grade" => "a", "pum" => 82], "9700" => ["grade" => "b", "pum" => 76], "9701" => ["grade" => "c", "pum" => 64]]],
    ["NIHARIKA SHARMA", "24/09/2009", "IN016 / 4009", ["9700" => ["grade" => "e", "pum" => 46], "9702" => ["grade" => "e", "pum" => 45], "9701" => ["grade" => "U", "pum" => 0]]],
    ["UTSAV MAHESHWARI", "14/08/2009", "IN016 / 4010", ["9702" => ["grade" => "d", "pum" => 58], "9709" => ["grade" => "d", "pum" => 57], "9618" => ["grade" => "d", "pum" => 50], "9701" => ["grade" => "e", "pum" => 41]]],
    ["YOGITA SURESH JAIN", "06/10/2008", "IN016 / 4011", ["9702" => ["grade" => "c", "pum" => 63], "9709" => ["grade" => "c", "pum" => 60], "9701" => ["grade" => "d", "pum" => 51], "9618" => ["grade" => "d", "pum" => 50]]],
    ["ANIRUDH", "14/05/2009", "IN016 / 4012", ["9609" => ["grade" => "d", "pum" => 57], "9708" => ["grade" => "d", "pum" => 51], "9706" => ["grade" => "e", "pum" => 40], "9626" => ["grade" => "U", "pum" => 0]]],
    ["CHRISANNE NADINE D'SILVA", "13/02/2008", "IN016 / 4013", ["9609" => ["grade" => "a", "pum" => 80], "9479" => ["grade" => "e", "pum" => 42], "9708" => ["grade" => "U", "pum" => 0]]],
    ["JUNED MOYAL", "26/11/2008", "IN016 / 4015", ["9709" => ["grade" => "c", "pum" => 66], "9708" => ["grade" => "c", "pum" => 65], "9706" => ["grade" => "d", "pum" => 53], "9626" => ["grade" => "d", "pum" => 51]]],
    ["MOHAMMED REHAN", "27/12/2008", "IN016 / 4016", ["9706" => ["grade" => "e", "pum" => 43], "9609" => ["grade" => "e", "pum" => 41], "9626" => ["grade" => "U", "pum" => 0], "9708" => ["grade" => "U", "pum" => 0]]],
    ["RAGHVENDRA PRATAP SINGH", "14/02/2009", "IN016 / 4018", ["9706" => ["grade" => "e", "pum" => 48], "9609" => ["grade" => "e", "pum" => 44], "9708" => ["grade" => "e", "pum" => 42], "9626" => ["grade" => "U", "pum" => 0]]],
    ["RAJVEER SINGH TANWAR", "21/04/2008", "IN016 / 4019", ["9609" => ["grade" => "d", "pum" => 55], "9626" => ["grade" => "U", "pum" => 0], "9706" => ["grade" => "U", "pum" => 0], "9708" => ["grade" => "U", "pum" => 0]]],
    ["SHAURYA LODHA", "31/07/2009", "IN016 / 4020", ["9709" => ["grade" => "a", "pum" => 82], "9706" => ["grade" => "d", "pum" => 59], "9708" => ["grade" => "d", "pum" => 58], "9990" => ["grade" => "d", "pum" => 52]]],
    ["VINEET BISHNOI", "04/10/2009", "IN016 / 4022", ["9609" => ["grade" => "U", "pum" => 0], "9626" => ["grade" => "U", "pum" => 0], "9706" => ["grade" => "U", "pum" => 0], "9708" => ["grade" => "U", "pum" => 0]]],
    ["VIVEK KUMAR YADAV", "08/08/2009", "IN016 / 4023", ["9609" => ["grade" => "c", "pum" => 64], "9626" => ["grade" => "e", "pum" => 44], "9706" => ["grade" => "U", "pum" => 0], "9708" => ["grade" => "U", "pum" => 0]]],
    ["ANAND KANWAR PADIYAR", "28/06/2008", "IN016 / 5016", ["9702" => ["grade" => "b", "pum" => 76], "9618" => ["grade" => "d", "pum" => 54]]],
    ["BHANUPRIYA RANA", "25/04/2008", "IN016 / 5017", ["9702" => ["grade" => "e", "pum" => 44], "9700" => ["grade" => "e", "pum" => 41]]],
    ["HIMANSHI AGARWAL", "17/03/2008", "IN016 / 5018", ["9702" => ["grade" => "d", "pum" => 51], "9700" => ["grade" => "e", "pum" => 40]]],
    ["KALYANI YADAV", "27/03/2008", "IN016 / 5020", ["9702" => ["grade" => "U", "pum" => 0]]],
    ["KAMLESH CHAUDHARY", "09/11/2007", "IN016 / 5021", ["9618" => ["grade" => "e", "pum" => 48], "9702" => ["grade" => "e", "pum" => 47]]],
    ["MANISHA YADAV", "26/11/2007", "IN016 / 5022", ["9702" => ["grade" => "U", "pum" => 0]]],
    ["PRIYAL CHOPRA", "03/09/2008", "IN016 / 5023", ["8021" => ["grade" => "b", "pum" => 71], "9702" => ["grade" => "b", "pum" => 70], "9618" => ["grade" => "c", "pum" => 66]]],
    ["PRIYANKA KHILERY", "21/12/2008", "IN016 / 5024", ["9618" => ["grade" => "d", "pum" => 54], "9702" => ["grade" => "e", "pum" => 46]]],
    ["VEDANSHI GAJJA", "18/10/2008", "IN016 / 5029", ["9609" => ["grade" => "e", "pum" => 40], "9706" => ["grade" => "U", "pum" => 0]]]
];

foreach ($data as $row) {
    list($name, $dobStr, $centerCandNo, $results) = $row;
    
    // Parse DOB
    $dob = Carbon::createFromFormat('d/m/Y', $dobStr)->format('Y-m-d');
    
    // Parse Cand No
    $parts = explode('/', $centerCandNo);
    $candNo = trim($parts[1] ?? '0000');
    
    $candidate = Candidate::firstOrCreate(
        ['school_id' => $school->id, 'candidate_number' => $candNo, 'candidate_name' => $name],
        ['date_of_birth' => $dob, 'enrollment_date' => '2025-01-01', 'status' => 'active']
    );

    foreach ($results as $subjCode => $res) {
        $subject = Subject::where('subject_code', $subjCode)->first();
        if (!$subject) {
            $subject = Subject::create([
                'subject_code' => $subjCode,
                'subject_name' => 'Unknown Subject ' . $subjCode,
                'qualification_id' => $asLevel->id,
                'total_marks' => 100
            ]);
        }
        
        $enrollment = CandidateEnrollment::firstOrCreate(
            ['candidate_id' => $candidate->id, 'series_id' => $series->id, 'subject_id' => $subject->id],
            ['qualification_id' => $subject->qualification_id, 'enrollment_status' => 'enrolled', 'enrolled_date' => '2025-01-01']
        );
        
        SubjectResult::updateOrCreate(
            ['enrollment_id' => $enrollment->id, 'subject_id' => $subject->id, 'series_id' => $series->id],
            [
                'grade' => strtoupper(substr($res['grade'], 0, 1) === '*' ? 'A*' : $res['grade']),
                'pum' => $res['pum'],
                'is_passed' => ($res['grade'] !== 'U'),
                'uploaded_by' => $admin ? $admin->id : null,
                'status' => 'complete'
            ]
        );
    }
}
echo "Import completed for June 2026 data.\n";
