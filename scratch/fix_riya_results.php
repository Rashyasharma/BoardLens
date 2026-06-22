<?php
// Fix Riya Bhandari Nov 2025 results by creating correct enrollments and copying the results
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use App\Models\Subject;
use App\Models\SubjectResult;
use App\Models\ExamSeries;
use App\Models\CandidateEnrollment;

$series = ExamSeries::where('series_code', 'NOV-2025')->first();
$cand = Candidate::where('candidate_name', 'RIYA BHANDARI')
    ->where('candidate_number', '0027')
    ->first();

if (!$cand || !$series) {
    die("Candidate or series not found!");
}

echo "=== FIXING ENROLLMENTS & RESULTS FOR RIYA BHANDARI ===" . PHP_EOL;

// Get subject results under the general enrollment
$generalEnrollment = CandidateEnrollment::where('candidate_id', $cand->id)
    ->where('series_id', $series->id)
    ->whereNull('subject_id')
    ->first();

if (!$generalEnrollment) {
    die("General enrollment not found!");
}

$results = SubjectResult::where('enrollment_id', $generalEnrollment->id)->get();
echo "Found " . $results->count() . " results to copy." . PHP_EOL;

foreach ($results as $r) {
    $subject = Subject::find($r->subject_id);
    if (!$subject) {
        continue;
    }
    
    echo "Processing Subject: {$subject->subject_code} ({$subject->subject_name})" . PHP_EOL;
    
    // Create correct subject-specific enrollment
    $subjectEnrollment = CandidateEnrollment::firstOrCreate(
        [
            'candidate_id' => $cand->id,
            'series_id' => $series->id,
            'qualification_id' => $subject->qualification_id,
            'subject_id' => $subject->id
        ],
        [
            'enrolled_date' => now()->toDateString(),
            'enrollment_status' => 'enrolled'
        ]
    );
    
    echo "  - Subject-specific enrollment ID: {$subjectEnrollment->id}" . PHP_EOL;
    
    // Check if correct SubjectResult exists
    $correctResult = SubjectResult::where('enrollment_id', $generalEnrollment->id) // Note: AiImportController confirming matches against general enrollment
        ->where('subject_id', $subject->id)
        ->where('series_id', $series->id)
        ->first();
        
    if ($correctResult) {
        echo "  - Correct result row exists. Updating PUM to actual values..." . PHP_EOL;
        // Map actual PUMs from parsed PDF
        if ($subject->subject_code === '9700') {
            $correctResult->pum = 40.0;
            $correctResult->grade = 'E';
        } elseif ($subject->subject_code === '9702') {
            $correctResult->pum = 53.0;
            $correctResult->grade = 'D';
        }
        $correctResult->save();
        echo "  - Saved! PUM: {$correctResult->pum}, Grade: {$correctResult->grade}" . PHP_EOL;
    }
}
