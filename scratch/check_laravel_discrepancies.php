<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- Similar Component Codes (e.g. 1 vs 01) ---" . PHP_EOL;
$res = DB::select("
    SELECT c1.id as id1, c1.component_code as code1, c2.id as id2, c2.component_code as code2, s.subject_code 
    FROM components c1 
    JOIN components c2 ON c1.subject_id = c2.subject_id AND c1.id != c2.id 
    JOIN subjects s ON c1.subject_id = s.id
    WHERE CAST(c1.component_code AS INTEGER) = CAST(c2.component_code AS INTEGER) 
    AND c1.component_code != c2.component_code
");
print_r($res);

echo "\n--- Components without Component Set mapping ---" . PHP_EOL;
$res = DB::select("
    SELECT COUNT(c.id) as orphan_components, s.subject_code, s.subject_name
    FROM components c
    JOIN subjects s ON c.subject_id = s.id
    WHERE c.component_set_id IS NULL OR c.component_set_id = ''
    GROUP BY s.subject_code, s.subject_name
");
print_r($res);

echo "\n--- Invalid Marks (e.g., > Total Marks) ---" . PHP_EOL;
$res = DB::select("
    SELECT cm.id, cm.obtained_marks, cm.total_marks, c.component_code, s.subject_code
    FROM component_marks cm
    JOIN components c ON cm.component_id = c.id
    JOIN subject_results sr ON cm.subject_result_id = sr.id
    JOIN subjects s ON sr.subject_id = s.id
    WHERE cm.obtained_marks > cm.total_marks
");
print_r($res);
