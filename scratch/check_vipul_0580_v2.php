<?php
$db = new SQLite3('C:/Users/HP11/CambridgeInsights_db/database.sqlite');
$r = $db->query("
    SELECT c.component_code, c.component_name, c.component_label, c.component_set_id, cs.start_year, cs.end_year, cm.obtained_marks, cm.total_marks, es.series_code, cand.candidate_name
    FROM component_marks cm 
    JOIN components c ON cm.component_id = c.id 
    JOIN subject_results sr ON cm.subject_result_id = sr.id 
    JOIN subjects s ON sr.subject_id = s.id 
    JOIN exam_series es ON sr.series_id = es.id 
    JOIN candidate_enrollments ce ON sr.enrollment_id = ce.id 
    JOIN candidates cand ON ce.candidate_id = cand.id 
    LEFT JOIN component_sets cs ON c.component_set_id = cs.id 
    WHERE cand.candidate_name LIKE '%VIPUL%' AND s.subject_code = '0580'
");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . PHP_EOL;
}
