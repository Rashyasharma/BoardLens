<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = "D:\\Rashya Sharma\\CIE\\June 2026\\Result\\IN016 AS A Level Provisional Component Marks June 2026.xlsx";
$spreadsheet = IOFactory::load($filePath);
$sheetNames = $spreadsheet->getSheetNames();
print_r($sheetNames);

$sheet = $spreadsheet->getSheetByName($sheetNames[1] ?? $sheetNames[0]);
$rows = $sheet->toArray(null, true, true, true);
$count = 0;
foreach ($rows as $row) {
    print_r($row);
    if (++$count >= 15) break;
}
