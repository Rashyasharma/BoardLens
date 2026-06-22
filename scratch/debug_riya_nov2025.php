<?php
// Find Riya Bhandari results in Nov 2025 and see why import_all_pums.php didn't match.
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use App\Models\Subject;
use App\Models\SubjectResult;
use App\Models\ExamSeries;

$series = ExamSeries::where('series_code', 'NOV-2025')->first();
$cand = Candidate::where('candidate_name', 'RIYA BHANDARI')->first();

echo "Candidate ID: " . ($cand ? $cand->id : 'none') . " | Candidate Number: " . ($cand ? $cand->candidate_number : 'none') . PHP_EOL;

if ($cand && $series) {
    // Find enrollments
    $enrollments = \App\Models\CandidateEnrollment::where('candidate_id', $cand->id)
        ->where('series_id', $series->id)
        ->get();
        
    echo "Enrollments in NOV-2025 count: " . $enrollments->count() . PHP_EOL;
    foreach ($enrollments as $e) {
        $subject = $e->subject_id ? Subject::find($e->subject_id) : null;
        echo "  - Enrollment ID: {$e->id} | Subject: " . ($subject ? "{$subject->subject_code} ({$subject->subject_name})" : "General") . PHP_EOL;
        
        if (!$e->subject_id) {
            // Find subject results for general enrollment
            $results = SubjectResult::where('enrollment_id', $e->id)->with('subject')->get();
            foreach ($results as $r) {
                echo "    - Result ID: {$r->id} | Subject: {$r->subject->subject_code} | Grade: {$r->grade} | PUM: {$r->pum}" . PHP_EOL;
            }
        }
    }
}
