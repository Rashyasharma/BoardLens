<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Candidate;
use App\Models\Subject;
use App\Models\ExamSeries;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;

// 1. Remove 0 PUMs for grades other than U or Q or UNGRADED
$updated = DB::table('subject_results')
    ->where('pum', 0)
    ->whereNotIn(DB::raw('UPPER(grade)'), ['U', 'Q', 'UNGRADED'])
    ->update(['pum' => '']);

echo "Updated $updated subject_results to have '' pum where it was 0 (and not U/Q/UNGRADED).\n";

// 2. Insert PUMs from the PDF for June 2026 series
$data = [
    '0008' => ['9618' => 43],
    '4001' => ['9702' => 68, '9701' => 60, '9700' => 59],
    '4002' => ['9709' => 52],
    '4003' => ['9231' => 90, '9702' => 86, '9479' => 40],
    '4004' => ['9700' => 52, '9702' => 52, '9701' => 46],
    '4005' => ['9702' => 70, '9709' => 59, '9701' => 42],
    '4006' => ['9709' => 64, '9702' => 44, '9618' => 42],
    '4007' => ['9709' => 48],
    '4008' => ['9702' => 82, '9700' => 76, '9701' => 64],
    '4009' => ['9700' => 46, '9702' => 45],
    '4010' => ['9702' => 58, '9709' => 57, '9618' => 50, '9701' => 41],
    '4011' => ['9702' => 63, '9709' => 60, '9701' => 51, '9618' => 50],
    '4012' => ['9609' => 57, '9708' => 51, '9706' => 40],
    '4013' => ['9609' => 80, '9479' => 42],
    '4015' => ['9709' => 66, '9708' => 65, '9706' => 53, '9626' => 51],
    '4016' => ['9706' => 43, '9609' => 41],
    '4018' => ['9706' => 48, '9609' => 44, '9708' => 42],
    '4019' => ['9609' => 55],
    '4020' => ['9709' => 82, '9706' => 59, '9708' => 58, '9990' => 52],
    '4022' => [],
    '4023' => ['9609' => 64, '9626' => 44],
    '5016' => ['9702' => 76, '9618' => 54],
    '5017' => ['9702' => 44, '9700' => 41],
    '5018' => ['9702' => 51, '9700' => 40],
    '5020' => [],
    '5021' => ['9618' => 48, '9702' => 47],
    '5022' => [],
    '5023' => ['8021' => 71, '9702' => 70, '9618' => 66],
    '5024' => ['9618' => 54, '9702' => 46],
    '5029' => ['9609' => 40]
];

$series = ExamSeries::where('year', 2026)->where('month', 'June')->first();
if (!$series) {
    die("Series not found.\n");
}

$foundCount = 0;
$updatedCount = 0;

foreach ($data as $candNo => $subjects) {
    $cand = Candidate::where('candidate_number', str_pad($candNo, 4, '0', STR_PAD_LEFT))->first();
    if (!$cand) {
        echo "Candidate not found: $candNo\n";
        continue;
    }
    
    foreach ($subjects as $code => $pum) {
        $subj = Subject::where('subject_code', $code)->first();
        if (!$subj) {
            echo "Subject not found: $code\n";
            continue;
        }
        
        $enr = CandidateEnrollment::where('candidate_id', $cand->id)
            ->where('series_id', $series->id)
            ->where('subject_id', $subj->id)
            ->first();
            
        if ($enr) {
            $foundCount++;
            $result = SubjectResult::where('enrollment_id', $enr->id)->first();
            if ($result) {
                if ($result->pum != $pum) {
                    echo "Updating PUM for {$candNo} - {$code} from '{$result->pum}' to '{$pum}'\n";
                    $result->pum = $pum;
                    $result->save();
                    $updatedCount++;
                } else {
                    echo "Skipped {$candNo} - {$code}: Already '{$pum}'\n";
                }
            } else {
                echo "Result not found for enrollment {$enr->id}\n";
            }
        } else {
            echo "Enrollment not found for {$candNo} - {$code}\n";
        }
    }
}

echo "Total matching enrollments found: {$foundCount}\n";
echo "Total PUMs updated: {$updatedCount}\n";
