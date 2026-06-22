<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Subject;
use App\Models\SubjectResult;

$subjects = Subject::where('subject_code', 'like', '0510%')->get();
foreach ($subjects as $s) {
    echo "Subject: {$s->subject_name} ({$s->subject_code}), ID: {$s->id}\n";
    $results = SubjectResult::where('subject_id', $s->id)->with(['enrollment.candidate', 'series', 'componentMarks.component'])->get();
    echo "  Total Results: " . $results->count() . "\n";
    foreach ($results as $r) {
        $cand = $r->enrollment->candidate;
        echo "    Result ID: {$r->id} | Candidate: {$cand->candidate_name} ({$cand->candidate_number}) | Series: {$r->series->series_name} | PUM: {$r->pum} | Grade: {$r->grade}\n";
        echo "      Component Marks:\n";
        foreach ($r->componentMarks as $cm) {
            echo "        - Component: {$cm->component->component_code} ({$cm->component->component_name}) | Marks: {$cm->obtained_marks}/{$cm->total_marks} ({$cm->percentage}%)\n";
        }
    }
}
