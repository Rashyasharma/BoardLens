<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SubjectResult;
use App\Models\ComponentMarks;
use Illuminate\Support\Facades\DB;

// Query all subject results for subjects with code starting with '0510'
$results = SubjectResult::whereHas('subject', function($q) {
    $q->where('subject_code', 'like', '0510%');
})->with(['subject', 'series', 'enrollment.candidate'])->get();

echo "Total results for 0510: " . $results->count() . "\n\n";

foreach ($results as $res) {
    $cand = $res->enrollment->candidate;
    echo "Cand: {$cand->candidate_name} ({$cand->candidate_number}) | Series: {$res->series->series_name} | Subject: {$res->subject->subject_code} ({$res->subject->subject_name})\n";
    
    // Find all component marks for this student/enrollment for English (either 0510 or 0510_OLD)
    $marks = ComponentMarks::where('enrollment_id', $res->enrollment_id)
        ->with('component.subject')
        ->get();
        
    echo "  Component Marks in database under enrollment_id:\n";
    foreach ($marks as $m) {
        $subjCode = $m->component->subject->subject_code ?? 'None';
        echo "    - Comp Code: {$m->component->component_code} | Name: {$m->component->component_name} | Marks: {$m->obtained_marks}/{$m->total_marks} | Subject: {$subjCode}\n";
    }
    
    echo "  Component Marks linked via subject_result_id:\n";
    $linkedMarks = ComponentMarks::where('subject_result_id', $res->id)->with('component.subject')->get();
    foreach ($linkedMarks as $m) {
        $subjCode = $m->component->subject->subject_code ?? 'None';
        echo "    - Comp Code: {$m->component->component_code} | Name: {$m->component->component_name} | Marks: {$m->obtained_marks}/{$m->total_marks} | Subject: {$subjCode}\n";
    }
    echo "\n";
}
