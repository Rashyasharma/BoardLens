<?php
$db = new SQLite3('C:/Users/HP11/CambridgeInsights_db/database.sqlite');
$r = $db->query("
    SELECT id, component_code, component_name, component_label, component_set_id
    FROM components
    WHERE id IN ('019ee442-2a39-731f-bc33-d08bf283899c', '019ee442-2a3a-70b9-8153-8ccd35564091')
");
while ($row = $r->fetchArray(SQLITE3_ASSOC)) {
    echo json_encode($row) . PHP_EOL;
}
