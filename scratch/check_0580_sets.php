<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sets = DB::select("SELECT id, label, start_year, end_year FROM component_sets WHERE subject_id IN (SELECT id FROM subjects WHERE subject_code = '0580')");
print_r($sets);
