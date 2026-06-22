<?php
// Find which series candidate 0027 RIYA BHANDARI actually has enrollments and subject results for.
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use App\Models\SubjectResult;

$cand = Candidate::where('candidate_name', 'RIYA BHANDARI')
    ->where('candidate_number', '0027')
    ->first();

if ($cand) {
    echo "Candidate ID: {$cand->id}" . PHP_EOL;
    
    // Find all candidate enrollments
    $enrollments = \App\Models\CandidateEnrollment::where('candidate_id', $cand->id)->get();
    echo "Enrollments count: " . $enrollments->count() . PHP_EOL;
    foreach ($enrollments as $e) {
        $series = \App\Models\ExamSeries::find($e->series_id);
        $subject = $e->subject_id ? \App\Models\Subject::find($e->subject_id) : null;
        echo "  - Enrollment ID: {$e->id} | Series: " . ($series ? $series->series_name : 'none') . " | Subject: " . ($subject ? $subject->subject_code : 'General') . PHP_EOL;
    }
    
    // Find all subject results
    $results = SubjectResult::whereHas('enrollment', function($q) use ($cand) {
        $q->where('candidate_id', $cand->id);
    })->with(['subject', 'series'])->get();
    
    echo "Subject Results count: " . $results->count() . PHP_EOL;
    foreach ($results as $r) {
        echo "  - Result ID: {$r->id} | Series: {$r->series->series_name} | Subject: {$r->subject->subject_code} | Grade: {$r->grade} | Enrollment ID: {$r->enrollment_id} | Series ID on Result: {$r->series_id}" . PHP_EOL;
    }
}
