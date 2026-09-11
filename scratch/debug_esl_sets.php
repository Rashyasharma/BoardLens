<?php
$db = new SQLite3('C:/Users/HP11/CambridgeInsights_db/database.sqlite');

$subjectId = '01ksffsqqn44k67s3fxpwq8edc';

// Simulate what the controller does: get all componentSets with their components
$res = $db->query("SELECT cs.id as set_id, cs.start_year, cs.end_year, cs.is_default, cs.label as set_label,
    c.id as comp_id, c.component_code, c.component_name, c.component_label, c.total_marks
    FROM component_sets cs
    LEFT JOIN components c ON c.component_set_id = cs.id
    WHERE cs.subject_id = '$subjectId'
    ORDER BY cs.start_year, c.component_code");

$sets = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    $sid = $row['set_id'];
    if (!isset($sets[$sid])) {
        $sets[$sid] = [
            'set_id' => $row['set_id'],
            'start_year' => $row['start_year'],
            'end_year' => $row['end_year'],
            'is_default' => $row['is_default'],
            'label' => $row['set_label'],
            'components' => []
        ];
    }
    if ($row['comp_id']) {
        $sets[$sid]['components'][] = [
            'code' => $row['component_code'],
            'name' => $row['component_name'],
            'label' => $row['component_label'],
            'total_marks' => $row['total_marks'],
        ];
    }
}

foreach ($sets as $set) {
    echo "SET: {$set['label']} (start={$set['start_year']}, end={$set['end_year']}, default={$set['is_default']})\n";
    foreach ($set['components'] as $c) {
        echo "  [{$c['code']}] {$c['label']} / {$c['name']} - {$c['total_marks']} marks\n";
    }
    echo "\n";
}

// Now check: what does the FLATTENED list look like (as the controller does it)?
echo "=== FLATTENED (what controller sends to view) ===\n";
$res = $db->query("SELECT c.component_code, c.component_name, c.component_label, c.total_marks, c.component_set_id,
    cs.start_year, cs.end_year, cs.label as set_label
    FROM components c
    JOIN component_sets cs ON cs.id = c.component_set_id
    WHERE c.subject_id = '$subjectId'
    ORDER BY c.component_code");
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    echo "[{$row['component_code']}] {$row['component_label']} | set: {$row['set_label']} ({$row['start_year']}-{$row['end_year']})\n";
}
