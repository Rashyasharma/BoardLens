<?php
$db = new SQLite3('C:/Users/HP11/CambridgeInsights_db/database.sqlite');
$r = $db->query("
    SELECT c.id, c.component_code, c.component_name, c.component_label, c.component_set_id, cs.start_year, cs.end_year 
    FROM components c 
    LEFT JOIN component_sets cs ON c.component_set_id = cs.id
    WHERE c.subject_id IN (SELECT id FROM subjects WHERE subject_code = '0580')
");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . PHP_EOL;
}
