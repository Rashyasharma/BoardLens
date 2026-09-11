<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$subject = \App\Models\Subject::where('subject_code', '9709')->first();
$comps = \App\Models\Component::where('subject_id', $subject->id)->get();
$byCode = [];

foreach($comps as $c) {
    if(!isset($byCode[$c->component_code])) {
        $byCode[$c->component_code] = $c;
    } else {
        $original = $byCode[$c->component_code];
        echo "Found duplicate for {$c->component_code}: {$c->id} -> merging into {$original->id}\n";
        
        \App\Models\ComponentMarks::where('component_id', $c->id)->update(['component_id' => $original->id]);
        $c->delete();
    }
}

$res = \App\Models\SubjectResult::where('subject_id', $subject->id)->where('status', 'component_marks_added')->first();
echo "Candidate: {$res->enrollment->candidate->candidate_name}\n";
echo "Total Obtained: {$res->total_obtained_marks}\n";
echo "Total Marks: {$res->total_marks}\n";
echo "Overall Pct: {$res->overall_percentage}\n";
