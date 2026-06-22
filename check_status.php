<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$result = App\Models\SubjectResult::first();
echo "Status: " . ($result->status ?? 'NULL') . "\n";
