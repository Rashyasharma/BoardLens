<?php
// Script to test updating Riya Bhandari specifically using the code in import_all_pums.php
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use App\Models\Subject;
use App\Models\SubjectResult;
use App\Models\ExamSeries;

$series = ExamSeries::where('series_code', 'NOV-2025')->first();
$dbCand = Candidate::where('candidate_number', '0027')->first();

if (!$dbCand || !$series) {
    die("Candidate or series not found!");
}

$resultsFromPdf = [
    '9702' => ['grade' => 'D', 'pum' => 53],
    '9700' => ['grade' => 'E', 'pum' => 40]
];

foreach ($resultsFromPdf as $subCode => $res) {
    $pum = $res['pum'];
    $grade = $res['grade'];
    
    $subject = Subject::where('subject_code', $subCode)->first();
    if (!$subject) {
        echo "Subject {$subCode} not found!" . PHP_EOL;
        continue;
    }
    
    $dbResult = SubjectResult::where('subject_id', $subject->id)
        ->where('series_id', $series->id)
        ->whereHas('enrollment', function ($q) use ($dbCand) {
            $q->where('candidate_id', $dbCand->id);
        })
        ->first();
        
    if ($dbResult) {
        echo "Found DB Result for {$subCode}!" . PHP_EOL;
        echo "  - Existing PUM: {$dbResult->pum}, New PUM: {$pum}" . PHP_EOL;
        echo "  - Existing Grade: {$dbResult->grade}, New Grade: {$grade}" . PHP_EOL;
        
        if ((float)$dbResult->pum != (float)$pum) {
            $dbResult->pum = $pum;
            $dbResult->grade = $grade;
            $dbResult->save();
            echo "  - Updated successfully!" . PHP_EOL;
        } else {
            echo "  - Already matches!" . PHP_EOL;
        }
    } else {
        echo "DB Result NOT found for {$subCode}!" . PHP_EOL;
        // Let's print out what enrollments exist for this candidate and subject
        $enrollment = \App\Models\CandidateEnrollment::where('candidate_id', $dbCand->id)
            ->where('series_id', $series->id)
            ->first();
        echo "  - Enrollment exists: " . ($enrollment ? 'yes' : 'no') . PHP_EOL;
    }
}
