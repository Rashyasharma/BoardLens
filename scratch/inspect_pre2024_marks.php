<?php

require 'C:/Users/HP11/Desktop/My Projects/CambridgeInsights/vendor/autoload.php';
$app = require_once 'C:/Users/HP11/Desktop/My Projects/CambridgeInsights/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ComponentMarks;
use Illuminate\Support\Facades\DB;

$marks = DB::select("
    SELECT cm.id, cm.obtained_marks, cm.total_marks as local_total,
           c.component_code, c.component_name, es.series_name, es.year
    FROM component_marks cm
    JOIN components c ON cm.component_id = c.id
    JOIN subject_results sr ON cm.subject_result_id = sr.id
    JOIN exam_series es ON sr.series_id = es.id
    WHERE c.subject_id = '01ksffsqqn44k67s3fxpwq8edc' AND es.year < 2024
");

echo "Total component marks rows found: " . count($marks) . "\n\n";

$grouped = [];
foreach ($marks as $m) {
    $key = $m->series_name . ' | Code: ' . $m->component_code . ' - ' . $m->component_name . ' | Row Total: ' . $m->local_total;
    if (!isset($grouped[$key])) {
        $grouped[$key] = [
            'count' => 0,
            'min_obtained' => 999,
            'max_obtained' => -999,
        ];
    }
    $grouped[$key]['count']++;
    $grouped[$key]['min_obtained'] = min($grouped[$key]['min_obtained'], $m->obtained_marks);
    $grouped[$key]['max_obtained'] = max($grouped[$key]['max_obtained'], $m->obtained_marks);
}

foreach ($grouped as $key => $info) {
    echo "{$key} => Count: {$info['count']}, Range: {$info['min_obtained']} to {$info['max_obtained']}\n";
}
