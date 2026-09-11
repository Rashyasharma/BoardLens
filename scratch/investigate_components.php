<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$components = DB::select("
    SELECT c.id, c.component_code, c.total_marks, c.component_set_id, s.subject_code, c.created_at
    FROM components c
    JOIN subjects s ON c.subject_id = s.id
    WHERE s.subject_code IN ('0580', '0510', '9706', '0654')
    ORDER BY s.subject_code, c.component_code
");

echo "--- Components for 0580, 0510, 9706, 0654 ---" . PHP_EOL;
foreach ($components as $c) {
    echo "Subj: {$c->subject_code} | Comp: {$c->component_code} | Total: {$c->total_marks} | Set: {$c->component_set_id} | Created: {$c->created_at}" . PHP_EOL;
}
