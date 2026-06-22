<?php
// Script to revert calculated/midpoint PUM values back to 0.
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;

// Find all results where the PUM matches one of the midpoints:
// 95.0, 90.0, 84.5, 74.5, 64.5, 54.5, 44.5, 34.5, 24.5
$midpoints = [95.0, 90.0, 84.5, 74.5, 64.5, 54.5, 44.5, 34.5, 24.5];

echo "=== REVERTING CALCULATED PUM VALUES ===" . PHP_EOL;

$results = SubjectResult::whereIn('pum', $midpoints)->get();
echo "Found " . $results->count() . " records matching midpoint PUM values." . PHP_EOL;

$revertedCount = 0;
foreach ($results as $r) {
    $r->pum = 0.0;
    $r->save();
    $revertedCount++;
}

echo "Successfully reverted {$revertedCount} calculated midpoint PUM values back to 0!" . PHP_EOL;
