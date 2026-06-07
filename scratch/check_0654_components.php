<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$subject = \App\Models\Subject::where('subject_code', '0654')->first();
if ($subject) {
    echo "Subject: {$subject->subject_name} | ID: {$subject->id}\n";
    $components = $subject->components;
    echo "Components Count: " . count($components) . "\n";
    foreach ($components as $c) {
        echo " - Comp ID: {$c->id} | Code: {$c->component_code} | Name: {$c->component_name}\n";
    }

    $results = \App\Models\SubjectResult::where('subject_id', $subject->id)->get();
    echo "Results Count: " . count($results) . "\n";
    foreach ($results->take(5) as $r) {
        $marks = $r->componentMarks;
        echo " - Result ID: {$r->id} | Marks Count: " . count($marks) . "\n";
        foreach ($marks as $m) {
            echo "   * Mark ID: {$m->id} | Comp ID: {$m->component_id} | Obtained: {$m->obtained_marks}\n";
        }
    }
} else {
    echo "Subject 0654 not found!\n";
}
