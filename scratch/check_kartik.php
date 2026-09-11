<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cands = App\Models\Candidate::where('candidate_name', 'like', '%KARTIK%')->get();
foreach($cands as $cand) {
    echo "Candidate: " . $cand->candidate_name . " (No: " . $cand->candidate_number . ")\n";
    $enrs = App\Models\CandidateEnrollment::where('candidate_id', $cand->id)->with(['subject', 'series'])->get();
    foreach($enrs as $e) {
        $result = App\Models\SubjectResult::where('enrollment_id', $e->id)->first();
        $grade = $result ? $result->grade : 'N/A';
        echo "  " . $e->series->year . ' ' . $e->series->month . ' - ' . $e->subject->subject_name . ' (' . $e->subject->subject_code . ') Grade: ' . $grade . "\n";
    }
    echo "\n";
}
