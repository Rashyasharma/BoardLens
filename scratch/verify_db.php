<?php
/**
 * Comprehensive BoardLens Cambridge Data Verification Script
 * Cross-references SQLite database with broadsheet Excel files
 */

$db = new SQLite3('database/database.sqlite');

// Check table structure for component_marks
echo "=== TABLE STRUCTURE: component_marks ===" . PHP_EOL;
$r = $db->query("PRAGMA table_info(component_marks)");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { 
    echo "  " . $row['name'] . " (" . $row['type'] . ")" . ($row['notnull'] ? ' NOT NULL' : ' NULLABLE') . PHP_EOL; 
}

echo PHP_EOL . "=== TABLE STRUCTURE: subject_results ===" . PHP_EOL;
$r = $db->query("PRAGMA table_info(subject_results)");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { 
    echo "  " . $row['name'] . " (" . $row['type'] . ")" . ($row['notnull'] ? ' NOT NULL' : ' NULLABLE') . PHP_EOL; 
}

echo PHP_EOL . "=== COMPONENT MARKS STATS ===" . PHP_EOL;
$r = $db->query("SELECT COUNT(*) as total FROM component_marks");
$row = $r->fetchArray(SQLITE3_ASSOC);
echo "Total component marks records: " . $row['total'] . PHP_EOL;

echo PHP_EOL . "=== COMPONENT MARKS SAMPLE (first 50) ===" . PHP_EOL;
$r = $db->query("SELECT cm.id, cm.obtained_marks, cm.total_marks, cm.percentage, c.component_code, c.component_name, s.subject_code, s.subject_name, sr.grade as subject_grade, sr.pum, es.series_code, cand.candidate_number, cand.candidate_name FROM component_marks cm JOIN components c ON cm.component_id=c.id JOIN subject_results sr ON cm.subject_result_id=sr.id JOIN subjects s ON sr.subject_id=s.id JOIN exam_series es ON sr.series_id=es.id JOIN candidate_enrollments ce ON sr.enrollment_id=ce.id JOIN candidates cand ON ce.candidate_id=cand.id ORDER BY es.series_code, s.subject_code, cand.candidate_number, c.component_code LIMIT 50");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { echo json_encode($row) . PHP_EOL; }

echo PHP_EOL . "=== SUBJECT RESULTS WITH BLANK/MISSING GRADES ===" . PHP_EOL;
$r = $db->query("SELECT sr.id, sr.grade, sr.pum, sr.status, s.subject_code, s.subject_name, es.series_code, cand.candidate_number, cand.candidate_name FROM subject_results sr JOIN subjects s ON sr.subject_id=s.id JOIN exam_series es ON sr.series_id=es.id JOIN candidate_enrollments ce ON sr.enrollment_id=ce.id JOIN candidates cand ON ce.candidate_id=cand.id WHERE sr.grade IS NULL OR sr.grade = '' ORDER BY es.series_code, s.subject_code");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { echo json_encode($row) . PHP_EOL; }

echo PHP_EOL . "=== SUBJECT RESULTS WITH NULL/BLANK PUM ===" . PHP_EOL;
$r = $db->query("SELECT sr.id, sr.grade, sr.pum, sr.status, s.subject_code, s.subject_name, es.series_code, cand.candidate_number, cand.candidate_name FROM subject_results sr JOIN subjects s ON sr.subject_id=s.id JOIN exam_series es ON sr.series_id=es.id JOIN candidate_enrollments ce ON sr.enrollment_id=ce.id JOIN candidates cand ON ce.candidate_id=cand.id WHERE sr.pum IS NULL OR sr.pum = '' ORDER BY es.series_code, s.subject_code");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { echo json_encode($row) . PHP_EOL; }

echo PHP_EOL . "=== ENROLLMENTS WITHOUT RESULTS ===" . PHP_EOL;
$r = $db->query("SELECT ce.id, s.subject_code, s.subject_name, es.series_code, cand.candidate_number, cand.candidate_name FROM candidate_enrollments ce JOIN candidates cand ON ce.candidate_id=cand.id JOIN exam_series es ON ce.series_id=es.id LEFT JOIN subjects s ON ce.subject_id=s.id LEFT JOIN subject_results sr ON sr.enrollment_id=ce.id WHERE sr.id IS NULL ORDER BY es.series_code, s.subject_code");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { echo json_encode($row) . PHP_EOL; }

echo PHP_EOL . "=== ENROLLMENT-SUBJECT INTEGRITY: Enrollments where subject_id is NULL ===" . PHP_EOL;
$r = $db->query("SELECT ce.id, ce.subject_id, es.series_code, cand.candidate_number, cand.candidate_name FROM candidate_enrollments ce JOIN candidates cand ON ce.candidate_id=cand.id JOIN exam_series es ON ce.series_id=es.id WHERE ce.subject_id IS NULL");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { echo json_encode($row) . PHP_EOL; }

echo PHP_EOL . "=== COMPONENT MARKS: check for NULL obtained_marks ===" . PHP_EOL;
$r = $db->query("SELECT cm.id, c.component_code, c.component_name, s.subject_code, s.subject_name, es.series_code, cand.candidate_number FROM component_marks cm JOIN components c ON cm.component_id=c.id JOIN subject_results sr ON cm.subject_result_id=sr.id JOIN subjects s ON sr.subject_id=s.id JOIN exam_series es ON sr.series_id=es.id JOIN candidate_enrollments ce ON sr.enrollment_id=ce.id JOIN candidates cand ON ce.candidate_id=cand.id WHERE cm.obtained_marks IS NULL ORDER BY es.series_code, s.subject_code");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { echo json_encode($row) . PHP_EOL; }

echo PHP_EOL . "=== ALL SERIES RESULT COUNTS ===" . PHP_EOL;
$r = $db->query("SELECT es.series_code, q.qualification_type, COUNT(DISTINCT sr.id) as results, COUNT(DISTINCT cm.id) as comp_marks FROM subject_results sr JOIN exam_series es ON sr.series_id=es.id JOIN candidate_enrollments ce ON sr.enrollment_id=ce.id JOIN qualifications q ON ce.qualification_id=q.id LEFT JOIN component_marks cm ON cm.subject_result_id=sr.id GROUP BY es.series_code, q.qualification_type ORDER BY es.series_code");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { echo json_encode($row) . PHP_EOL; }

echo PHP_EOL . "=== DUPLICATE SUBJECT MAPPINGS CHECK ===" . PHP_EOL;
echo "(subjects where same code maps to multiple qualification_ids)" . PHP_EOL;
$r = $db->query("SELECT subject_code, COUNT(DISTINCT qualification_id) as qual_count, GROUP_CONCAT(DISTINCT subject_name) as names FROM subjects GROUP BY subject_code HAVING qual_count > 1");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { echo json_encode($row) . PHP_EOL; }

echo PHP_EOL . "=== ALL SUBJECT RESULTS (full dump - all series, all subjects) ===" . PHP_EOL;
$r = $db->query("SELECT es.series_code, q.qualification_type, s.subject_code, s.subject_name, sr.grade, sr.pum, sr.status, cand.candidate_number, cand.candidate_name FROM subject_results sr JOIN subjects s ON sr.subject_id=s.id JOIN exam_series es ON sr.series_id=es.id JOIN candidate_enrollments ce ON sr.enrollment_id=ce.id JOIN qualifications q ON ce.qualification_id=q.id JOIN candidates cand ON ce.candidate_id=cand.id ORDER BY es.series_code, q.qualification_type, s.subject_code, cand.candidate_number");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) { echo json_encode($row) . PHP_EOL; }
