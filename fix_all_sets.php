<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\Component;

$count = 0;
$components = Component::all();

foreach ($components as $component) {
    $subject = Subject::find($component->subject_id);
    if (!$subject) continue;

    $latestSet = \App\Models\ComponentSet::findLatestForSubject($subject->id);
    if (!$latestSet) {
        $latestSet = \App\Models\ComponentSet::create([
            'subject_id' => $subject->id,
            'label' => 'Default',
            'is_default' => true
        ]);
        echo "Created default ComponentSet for Subject: {$subject->id}\n";
    }

    if ($latestSet && $component->component_set_id !== $latestSet->id) {
        $component->component_set_id = $latestSet->id;
        $component->save();
        $count++;
    }
}

echo "Moved $count components to their latest active ComponentSets.\n";
