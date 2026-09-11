<?php
$db = new SQLite3('C:/Users/HP11/CambridgeInsights_db/database.sqlite');
$db->exec("BEGIN TRANSACTION");

// Re-import the component marks by executing the importer script. Let's inspect its data again.
$db->exec("DELETE FROM component_marks");

$db->exec("COMMIT");
echo "Component marks truncated. Now re-run import." . PHP_EOL;
