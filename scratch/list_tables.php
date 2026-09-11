<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// List all tables
$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
echo "=== ALL TABLES ===\n";
foreach ($tables as $t) {
    echo $t->name . "\n";
}
