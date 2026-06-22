<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$marks = DB::select("
    SELECT cm.obtained_marks, cm.total_marks, c.component_code, c.component_name, s.subject_name, s.subject_code, COUNT(*) as count
    FROM component_marks cm
    JOIN components c ON cm.component_id = c.id
    JOIN subject_results sr ON cm.subject_result_id = sr.id
    JOIN exam_series es ON sr.series_id = es.id
    JOIN subjects s ON sr.subject_id = s.id
    WHERE es.series_name = 'March 2023'
    GROUP BY c.component_code, c.component_name, s.subject_name, s.subject_code, cm.total_marks
");

foreach ($marks as $m) {
    echo "Subject: {$m->subject_name} ({$m->subject_code}) | Component: {$m->component_code} ({$m->component_name}) | Total Marks: {$m->total_marks} | Count: {$m->count}\n";
}
