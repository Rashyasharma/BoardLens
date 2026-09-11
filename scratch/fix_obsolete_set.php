<?php
$db = new SQLite3('C:/Users/HP11/CambridgeInsights_db/database.sqlite');
$db->exec("BEGIN TRANSACTION");

// Re-link component marks of set id '1d01b24f-dc12-4663-a7ba-994a11eaa32c' to the correct year-specific components
$db->exec("
    UPDATE component_marks
    SET component_id = (
        SELECT correct_c.id
        FROM components correct_c
        JOIN components old_c ON old_c.id = component_marks.component_id
        JOIN subject_results sr ON component_marks.subject_result_id = sr.id
        JOIN exam_series es ON sr.series_id = es.id
        JOIN component_sets correct_cs ON correct_c.component_set_id = correct_cs.id
        WHERE old_c.component_set_id = '1d01b24f-dc12-4663-a7ba-994a11eaa32c'
          AND correct_c.subject_id = old_c.subject_id
          AND correct_c.component_code = old_c.component_code
          AND es.year >= correct_cs.start_year
          AND (correct_cs.end_year IS NULL OR es.year <= correct_cs.end_year)
    )
    WHERE component_id IN (
        SELECT id FROM components WHERE component_set_id = '1d01b24f-dc12-4663-a7ba-994a11eaa32c'
    )
");

// Delete the obsolete/redundant set '1d01b24f-dc12-4663-a7ba-994a11eaa32c' components and sets to keep DB clean
$db->exec("DELETE FROM components WHERE component_set_id = '1d01b24f-dc12-4663-a7ba-994a11eaa32c'");
$db->exec("DELETE FROM component_sets WHERE id = '1d01b24f-dc12-4663-a7ba-994a11eaa32c'");

$db->exec("COMMIT");
echo "Obsolete component set '1d01b24f-dc12-4663-a7ba-994a11eaa32c' has been clean merged!" . PHP_EOL;
