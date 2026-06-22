<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$marks = DB::select("
    SELECT cm.obtained_marks, cm.total_marks, c.component_code, c.component_name, s.subject_name, s.subject_code, cand.candidate_name, cand.candidate_number
    FROM component_marks cm
    JOIN components c ON cm.component_id = c.id
    JOIN subject_results sr ON cm.subject_result_id = sr.id
    JOIN exam_series es ON sr.series_id = es.id
    JOIN candidate_enrollments ce ON sr.enrollment_id = ce.id
    JOIN candidates cand ON ce.candidate_id = cand.id
    JOIN subjects s ON sr.subject_id = s.id
    WHERE es.series_name = 'March 2023' AND s.subject_code LIKE '0510%'
");

echo "Total March 2023 English Component Marks: " . count($marks) . "\n";
foreach ($marks as $m) {
    echo "- Student: {$m->candidate_name} ({$m->candidate_number}) | Component: {$m->component_code} ({$m->component_name}) | Marks: {$m->obtained_marks}/{$m->total_marks}\n";
}
