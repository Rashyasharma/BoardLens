<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CandidateEnrollment;
use App\Models\ComponentMarks;
use App\Services\MarkCalculationService;

// First check if there are null subject_ids
$enrollments = CandidateEnrollment::whereNull('subject_id')->get();
$fixedCount = 0;

foreach ($enrollments as $enrollment) {
    $mark = ComponentMarks::with('component')->where('enrollment_id', $enrollment->id)->first();
    if ($mark && $mark->component) {
        $enrollment->subject_id = $mark->component->subject_id;
        $enrollment->save();
        $fixedCount++;
    } else {
        $result = \App\Models\SubjectResult::where('enrollment_id', $enrollment->id)->first();
        if ($result && $result->subject_id) {
            $enrollment->subject_id = $result->subject_id;
            $enrollment->save();
            $fixedCount++;
        }
    }
}
echo "Fixed $fixedCount enrollments with null subject_id.\n";

// Update all ComponentMarks total_marks to match their component definition
$marks = ComponentMarks::with('component')->get();
$updatedMarks = 0;
foreach ($marks as $mark) {
    if ($mark->component && $mark->total_marks !== $mark->component->total_marks) {
        $mark->total_marks = $mark->component->total_marks;
        $mark->save();
        $updatedMarks++;
    }
}
echo "Synced $updatedMarks ComponentMarks total_marks with their components.\n";

// Now recalculate ALL candidate enrollments that have component marks
$calculationService = app(MarkCalculationService::class);
$enrollmentsToRecalculate = ComponentMarks::select('enrollment_id')->distinct()->pluck('enrollment_id');

$successCount = 0;
$failCount = 0;

foreach ($enrollmentsToRecalculate as $enrollmentId) {
    $enrollment = CandidateEnrollment::find($enrollmentId);
    if ($enrollment && $enrollment->subject_id) {
        try {
            $calculationService->calculateSubjectResult($enrollment);
            $successCount++;
        } catch (\Exception $e) {
            $failCount++;
            echo "Failed for {$enrollmentId}: " . $e->getMessage() . "\n";
        }
    }
}

echo "Recalculated results for $successCount enrollments. Failed: $failCount.\n";
