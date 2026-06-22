<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Candidate;
use App\Models\ComponentMarks;
use App\Models\SubjectResult;

$candidate = Candidate::where('candidate_name', 'like', '%RIYA BHANDARI%')->first();
if (!$candidate) {
    die("RIYA BHANDARI not found.\n");
}
echo "Candidate ID: {$candidate->id}, Name: {$candidate->candidate_name}\n";

$results = SubjectResult::whereIn('enrollment_id', function($q) use ($candidate) {
    $q->select('id')->from('candidate_enrollments')->where('candidate_id', $candidate->id);
})->with(['series', 'subject', 'componentMarks.component'])->get();

foreach ($results as $r) {
    echo "Series: {$r->series->series_name} | Subject: {$r->subject->subject_name} ({$r->subject->subject_code}) | PUM: {$r->pum} | Grade: {$r->grade}\n";
    echo "  Component Marks:\n";
    foreach ($r->componentMarks as $cm) {
        echo "    - Component: {$cm->component->component_code} - {$cm->component->component_name} | Obtained: {$cm->obtained_marks} / {$cm->total_marks}\n";
    }
}
