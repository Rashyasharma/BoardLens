<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$seriesId = '019e5ed2-be1b-7098-8157-5242cabfec27';
$subjectId = '019e5ed2-bbf3-71c9-82ab-224723e37b0c';

$enrollments = App\Models\CandidateEnrollment::with('candidate')
    ->where('series_id', $seriesId)
    ->where('subject_id', $subjectId)
    ->join('candidates', 'candidate_enrollments.candidate_id', '=', 'candidates.id')
    ->orderBy('candidates.candidate_number')
    ->select('candidate_enrollments.*')
    ->get();

echo "Enrollments count: " . $enrollments->count() . "\n";

$candidateIds = $enrollments->pluck('candidate_id')->toArray();

$subjectResults = App\Models\SubjectResult::with('componentMarks.component')
    ->where('series_id', $seriesId)
    ->where('subject_id', $subjectId)
    ->whereHas('enrollment', function($q) use ($candidateIds) {
        $q->whereIn('candidate_id', $candidateIds);
    })
    ->get()
    ->keyBy('enrollment_id');

echo "SubjectResults count: " . $subjectResults->count() . "\n";

if ($subjectResults->count() > 0) {
    $firstEnrId = $enrollments->first()->id;
    $res = $subjectResults->get($firstEnrId);
    echo "First enrollment ID: " . $firstEnrId . "\n";
    echo "Result matched? " . ($res ? 'YES' : 'NO') . "\n";
    if ($res) {
        echo "Result PUM: " . $res->pum . "\n";
        echo "Component Marks count: " . $res->componentMarks->count() . "\n";
    } else {
        // Find which enrollment has a result
        $keys = $subjectResults->keys()->toArray();
        echo "Valid keys in subjectResults: " . implode(', ', $keys) . "\n";
    }
}
