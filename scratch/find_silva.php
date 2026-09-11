<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== SEARCH CHRISANNE ===\n";
$r = DB::table('candidates')->where('candidate_name','LIKE','%CHRISANNE%')->get();
foreach ($r as $c) echo json_encode($c) . "\n";
if ($r->isEmpty()) echo "Not found by CHRISANNE\n";

echo "\n=== SEARCH SILVA ===\n";
$r2 = DB::table('candidates')->where('candidate_name','LIKE','%SILVA%')->get();
foreach ($r2 as $c) echo json_encode($c) . "\n";
if ($r2->isEmpty()) echo "Not found by SILVA\n";

echo "\n=== First 5 candidates ===\n";
$sample = DB::table('candidates')->limit(5)->get();
foreach ($sample as $c) echo $c->id . ' | ' . $c->candidate_name . "\n";

echo "\n=== Travel & Tourism in subjects ===\n";
$tt = DB::table('subjects')
    ->where('name','LIKE','%Travel%')
    ->orWhere('name','LIKE','%Tourism%')
    ->get();
echo json_encode($tt, JSON_PRETTY_PRINT) . "\n";
