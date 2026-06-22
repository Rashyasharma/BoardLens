<?php
/**
 * Check PUM values for AS/A Level subjects
 */

$db = new SQLite3('database/database.sqlite');

echo "=== AS/A Level subjects with ALL zero PUM ===" . PHP_EOL;
$r = $db->query("
    SELECT s.subject_code, s.subject_name, 
           COUNT(*) as total, 
           SUM(CASE WHEN sr.pum > 0 THEN 1 ELSE 0 END) as has_pum,
           SUM(CASE WHEN sr.pum = 0 OR sr.pum IS NULL THEN 1 ELSE 0 END) as zero_pum,
           AVG(CASE WHEN sr.pum > 0 THEN sr.pum ELSE NULL END) as real_avg_pum
    FROM subject_results sr
    JOIN subjects s ON sr.subject_id = s.id
    JOIN qualifications q ON s.qualification_id = q.id
    WHERE q.qualification_type = 'AS_A_LEVEL'
    GROUP BY s.subject_code, s.subject_name
    ORDER BY zero_pum DESC
");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { echo json_encode($row) . PHP_EOL; }

echo PHP_EOL . "=== Biology (9700) - sample 10 rows ===" . PHP_EOL;
$r = $db->query("
    SELECT sr.grade, sr.pum, sr.status, cand.candidate_number, cand.candidate_name
    FROM subject_results sr
    JOIN subjects s ON sr.subject_id = s.id
    JOIN candidate_enrollments ce ON sr.enrollment_id = ce.id
    JOIN candidates cand ON ce.candidate_id = cand.id
    WHERE s.subject_code = '9700'
    LIMIT 20
");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { echo json_encode($row) . PHP_EOL; }

echo PHP_EOL . "=== Economics (9708) - sample 10 rows ===" . PHP_EOL;
$r = $db->query("
    SELECT sr.grade, sr.pum, sr.status, cand.candidate_number, cand.candidate_name
    FROM subject_results sr
    JOIN subjects s ON sr.subject_id = s.id
    JOIN candidate_enrollments ce ON sr.enrollment_id = ce.id
    JOIN candidates cand ON ce.candidate_id = cand.id
    WHERE s.subject_code = '9708'
    LIMIT 20
");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { echo json_encode($row) . PHP_EOL; }
