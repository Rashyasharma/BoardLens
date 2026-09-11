<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\Component;

$today = now()->startOfDay();
$count = 0;

// Find all components created today
$recentComponents = Component::where('created_at', '>=', $today)->get();

foreach ($recentComponents as $component) {
    $subject = Subject::find($component->subject_id);
    if (!$subject) continue;

    // Find the latest set for the subject
    $latestSet = $subject->componentSets()
        ->where('is_default', false)
        ->orderByRaw("COALESCE(end_year, start_year, 0) DESC")
        ->first();

    if ($latestSet && $component->component_set_id !== $latestSet->id) {
        $component->component_set_id = $latestSet->id;
        $component->save();
        $count++;
    }
}

echo "Moved $count recently created components to their latest active ComponentSets.\n";
