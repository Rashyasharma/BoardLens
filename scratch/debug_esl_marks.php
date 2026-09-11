<?php
$db = new SQLite3('C:/Users/HP11/CambridgeInsights_db/database.sqlite');

$subjectId = '01ksffsqqn44k67s3fxpwq8edc'; // ESL 0510

// Check component_marks for ESL 2024-2026 components
$set2024Id = 'c70326c4-21a7-4260-8642-d5dc243964eb';

echo "=== Components in 2024-2026 set ===\n";
$res = $db->query("SELECT id, component_code, component_label, total_marks FROM components WHERE component_set_id = '$set2024Id' ORDER BY component_code");
$comps2024 = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . "\n";
    $comps2024[] = $row['id'];
}

echo "\n=== component_marks counts for ESL 2024-2026 components ===\n";
foreach ($comps2024 as $cid) {
    $r = $db->querySingle("SELECT COUNT(*) FROM component_marks WHERE component_id = '$cid'");
    $cInfo = $db->querySingle("SELECT component_code || ' - ' || component_label FROM components WHERE id = '$cid'");
    echo "$cInfo: $r marks records\n";
}

echo "\n=== All ESL component_marks grouped by component ===\n";
$res = $db->query("SELECT c.component_code, c.component_label, c.component_set_id, COUNT(cm.id) as cnt
    FROM component_marks cm
    JOIN components c ON c.id = cm.component_id
    WHERE c.subject_id = '$subjectId'
    GROUP BY c.id, c.component_code, c.component_label, c.component_set_id
    ORDER BY c.component_set_id, c.component_code");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . "\n";
}

echo "\n=== Subject results with series for ESL ===\n";
$res = $db->query("SELECT sr.id, sr.series_id, es.series_code, es.year, es.month, sr.grade
    FROM subject_results sr
    JOIN exam_series es ON es.id = sr.series_id
    WHERE sr.subject_id = '$subjectId'
    ORDER BY es.year DESC, es.month
    LIMIT 20");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . "\n";
}
