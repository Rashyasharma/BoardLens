<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['subjects','candidate_enrollments','subject_results','qualifications','exam_series'];
foreach ($tables as $t) {
    echo "\n=== COLUMNS: $t ===\n";
    $cols = DB::select("PRAGMA table_info('$t')");
    foreach ($cols as $c) echo "  " . $c->name . " (" . $c->type . ")\n";
}

echo "\n=== SAMPLE subjects ===\n";
$subs = DB::table('subjects')->limit(10)->get();
foreach ($subs as $s) echo json_encode($s) . "\n";
