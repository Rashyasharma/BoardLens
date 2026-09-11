<?php
$db = new SQLite3('C:/Users/HP11/CambridgeInsights_db/database.sqlite');
$r = $db->query("
    SELECT cm.obtained_marks, cm.total_marks, s.subject_code, s.subject_name, es.series_code, cand.candidate_name, cm.component_id
    FROM component_marks cm
    JOIN subject_results sr ON cm.subject_result_id = sr.id
    JOIN subjects s ON sr.subject_id = s.id
    JOIN exam_series es ON sr.series_id = es.id
    JOIN candidate_enrollments ce ON sr.enrollment_id = ce.id
    JOIN candidates cand ON ce.candidate_id = cand.id
    WHERE cand.candidate_name LIKE '%VIPUL%' AND s.subject_code = '0580'
");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . PHP_EOL;
}
