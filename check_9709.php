<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\ComponentMarks;

$subject = Subject::where('subject_code', '9709')->first();
echo "Subject ID: " . ($subject ? $subject->id : 'Not Found') . "\n";

if ($subject) {
    $count = ComponentMarks::whereHas('subjectResult', function($q) use ($subject) {
        $q->where('subject_id', $subject->id);
    })->count();
    echo "Marks count: $count\n";
    
    // Check if its components have a component_set_id
    $components = App\Models\Component::where('subject_id', $subject->id)->get();
    foreach ($components as $c) {
        echo "Component {$c->component_code} has component_set_id: " . ($c->component_set_id ?? 'NULL') . "\n";
    }
}
