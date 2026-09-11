<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\Component;
use App\Models\ComponentSet;

// For all components with null component_set_id, assign them to a default component set.
$components = Component::whereNull('component_set_id')->get();
$count = 0;

foreach ($components as $component) {
    // Find or create a default component set for the subject
    $set = ComponentSet::firstOrCreate(
        ['subject_id' => $component->subject_id, 'is_default' => true],
        ['label' => 'Default']
    );

    $component->component_set_id = $set->id;
    $component->save();
    $count++;
}

echo "Fixed $count components by assigning them to a ComponentSet.\n";
