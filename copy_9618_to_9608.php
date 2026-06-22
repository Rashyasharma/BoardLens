<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\ComponentSet;
use App\Models\Component;

$sourceCode = '9618';
$targetCode = '9608';

$sourceSubject = Subject::where('subject_code', $sourceCode)->first();
if (!$sourceSubject) {
    die("Source subject $sourceCode not found.\n");
}

$targetSubject = Subject::where('subject_code', $targetCode)->first();
if (!$targetSubject) {
    $targetSubject = $sourceSubject->replicate();
    $targetSubject->subject_code = $targetCode;
    $targetSubject->save();
    echo "Created subject $targetCode ({$targetSubject->subject_name})\n";
} else {
    echo "Subject $targetCode already exists.\n";
}

$sourceSets = ComponentSet::where('subject_id', $sourceSubject->id)->get();

foreach ($sourceSets as $sourceSet) {
    // Check if target set already exists for this year range
    $targetSet = ComponentSet::where('subject_id', $targetSubject->id)
        ->where('valid_from', $sourceSet->valid_from)
        ->where('valid_to', $sourceSet->valid_to)
        ->first();

    if (!$targetSet) {
        $targetSet = $sourceSet->replicate();
        $targetSet->subject_id = $targetSubject->id;
        $targetSet->save();
        echo "Created ComponentSet {$targetSet->valid_from} - {$targetSet->valid_to}\n";
    }

    $sourceComponents = Component::where('component_set_id', $sourceSet->id)->get();
    foreach ($sourceComponents as $sourceComp) {
        $targetComp = Component::where('component_set_id', $targetSet->id)
            ->where('component_code', $sourceComp->component_code)
            ->first();

        if (!$targetComp) {
            $targetComp = $sourceComp->replicate();
            $targetComp->subject_id = $targetSubject->id;
            $targetComp->component_set_id = $targetSet->id;
            $targetComp->save();
            echo "  Created Component {$targetComp->component_code} ({$targetComp->component_label})\n";
        } else {
            // Update the label and other details if it exists
            $targetComp->component_label = $sourceComp->component_label;
            $targetComp->component_name = $sourceComp->component_name;
            $targetComp->total_marks = $sourceComp->total_marks;
            $targetComp->scaling_factor = $sourceComp->scaling_factor;
            $targetComp->save();
            echo "  Updated Component {$targetComp->component_code} ({$targetComp->component_label})\n";
        }
    }
}

echo "Done copying from $sourceCode to $targetCode.\n";
