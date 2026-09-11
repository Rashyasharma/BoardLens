<?php
$db = new SQLite3('C:/Users/HP11/CambridgeInsights_db/database.sqlite');

// 1. Find the subject
echo "=== SUBJECTS ===\n";
$res = $db->query("SELECT s.id, s.subject_name, s.subject_code, q.qualification_name FROM subjects s JOIN qualifications q ON s.qualification_id = q.id WHERE s.subject_name LIKE '%English%Second%'");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n=== COMPONENT SETS ===\n";
$res = $db->query("SELECT cs.id, cs.subject_id, cs.start_year, cs.end_year, cs.is_default, cs.label FROM component_sets cs JOIN subjects s ON s.id = cs.subject_id WHERE s.subject_name LIKE '%English%Second%' ORDER BY cs.start_year");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n=== COMPONENTS (all) ===\n";
$res = $db->query("SELECT c.id, c.component_code, c.component_name, c.component_label, c.total_marks, c.subject_id, c.component_set_id FROM components c JOIN subjects s ON s.id = c.subject_id WHERE s.subject_name LIKE '%English%Second%' ORDER BY c.component_set_id, c.component_code");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . "\n";
}
