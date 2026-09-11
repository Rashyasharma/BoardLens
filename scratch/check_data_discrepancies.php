<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Component;
use App\Models\Subject;

$db = new SQLite3(__DIR__ . '/../database/database.sqlite');

echo "--- Component Duplicates ---" . PHP_EOL;
$res = $db->query("
    SELECT c.subject_id, s.subject_code, s.subject_name, q.qualification_type, c.component_code, COUNT(*) as cnt, GROUP_CONCAT(c.id) as ids 
    FROM components c 
    JOIN subjects s ON c.subject_id = s.id 
    JOIN qualifications q ON s.qualification_id = q.id
    GROUP BY c.subject_id, c.component_code 
    HAVING cnt > 1
");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    print_r($row);
}

echo "\n--- Similar Component Codes (e.g. 1 vs 01) ---" . PHP_EOL;
$res = $db->query("
    SELECT c1.id as id1, c1.component_code as code1, c2.id as id2, c2.component_code as code2, s.subject_code 
    FROM components c1 
    JOIN components c2 ON c1.subject_id = c2.subject_id AND c1.id != c2.id 
    JOIN subjects s ON c1.subject_id = s.id
    WHERE CAST(c1.component_code AS INTEGER) = CAST(c2.component_code AS INTEGER) 
    AND c1.component_code != c2.component_code
");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    print_r($row);
}

echo "\n--- Components without Component Set mapping ---" . PHP_EOL;
$res = $db->query("
    SELECT COUNT(c.id) as orphan_components, s.subject_code
    FROM components c
    JOIN subjects s ON c.subject_id = s.id
    WHERE c.component_set_id IS NULL OR c.component_set_id = ''
    GROUP BY s.subject_code
");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    print_r($row);
}
