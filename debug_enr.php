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

$first = $enrollments->first();
echo "First row ID from query: " . $first->id . "\n";
echo "First row candidate ID: " . $first->candidate_id . "\n";

$dbEnr = App\Models\CandidateEnrollment::find($first->id);
if ($dbEnr) {
    echo "This ID exists in DB! subject_id: " . $dbEnr->subject_id . "\n";
}

$dbEnrByCandidate = App\Models\CandidateEnrollment::where('candidate_id', $first->candidate_id)->where('series_id', $seriesId)->get();
foreach ($dbEnrByCandidate as $enr) {
    echo "Found enrollment for candidate: ID=" . $enr->id . ", subject_id=" . ($enr->subject_id ?? 'NULL') . "\n";
}
