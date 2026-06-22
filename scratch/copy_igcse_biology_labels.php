<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$biology = \App\Models\Subject::where('subject_code', '0610')->first();
$chemistry = \App\Models\Subject::where('subject_code', '0620')->first();
$physics = \App\Models\Subject::where('subject_code', '0625')->first();

if (!$biology) {
    echo "Biology 0610 not found\n";
    exit(1);
}

function copyLabels($src, $dest) {
    if (!$dest) {
        echo "Destination subject not found\n";
        return;
    }
    echo "Copying component labels/marks from {$src->subject_name} ({$src->subject_code}) to {$dest->subject_name} ({$dest->subject_code})...\n";
    
    foreach ($src->components as $srcComp) {
        $destComp = $dest->components()->where('component_code', $srcComp->component_code)->first();
        if ($destComp) {
            $destComp->component_name = str_replace('0610', $dest->subject_code, $srcComp->component_name);
            $destComp->total_marks = $srcComp->total_marks;
            $destComp->component_label = str_replace('0610', $dest->subject_code, $srcComp->component_label);
            $destComp->save();
            echo "Updated component {$destComp->component_code} to name='{$destComp->component_name}', max_marks={$destComp->total_marks}, label='{$destComp->component_label}'\n";
        } else {
            // Create component if it doesn't exist
            $newCompName = str_replace('0610', $dest->subject_code, $srcComp->component_name);
            $newLabel = str_replace('0610', $dest->subject_code, $srcComp->component_label);
            
            $dest->components()->create([
                'component_code' => $srcComp->component_code,
                'component_name' => $newCompName,
                'component_type' => $srcComp->component_type,
                'total_marks' => $srcComp->total_marks,
                'scaling_factor' => $srcComp->scaling_factor,
                'is_mandatory' => $srcComp->is_mandatory,
                'description' => $srcComp->description,
                'component_label' => $newLabel
            ]);
            echo "Created component {$srcComp->component_code} name='{$newCompName}', max_marks={$srcComp->total_marks}, label='{$newLabel}'\n";
        }
    }
}

copyLabels($biology, $chemistry);
copyLabels($biology, $physics);

echo "Done!\n";
