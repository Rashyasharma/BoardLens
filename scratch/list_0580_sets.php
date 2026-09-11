<?php
$db = new SQLite3('C:/Users/HP11/CambridgeInsights_db/database.sqlite');
$r = $db->query("
    SELECT id, subject_id, start_year, end_year, label 
    FROM component_sets 
    WHERE subject_id IN (SELECT id FROM subjects WHERE subject_code = '0580')
");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . PHP_EOL;
}
