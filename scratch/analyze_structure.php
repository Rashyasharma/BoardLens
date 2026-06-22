<?php
/**
 * Deep analysis of broadsheet structure to understand grade column position
 * and correctly extract grades for each subject
 */
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$broadsheetDir = 'D:/Rashya Sharma/CIE/Other Docs/CIE ALL Broadsheets/Provisional Component Marks';

// Test with a recent file to understand the sheet structure
$testFile = $broadsheetDir . '/IGCSE/IN016 IGCSE Provisional Component Marks March 2026.xlsx';

echo "=== ANALYZING: March 2026 IGCSE ===" . PHP_EOL;
$spreadsheet = IOFactory::load($testFile);
$sheetNames = $spreadsheet->getSheetNames();
echo "Sheets: " . implode(', ', $sheetNames) . PHP_EOL . PHP_EOL;

foreach ($sheetNames as $sheetName) {
    $cleanName = trim($sheetName);
    if (!preg_match('/^\d{4}$/', $cleanName)) continue;
    
    $sheet = $spreadsheet->getSheetByName($sheetName);
    $rows = $sheet->toArray(null, true, true, true);
    
    echo "--- Subject: {$cleanName} ---" . PHP_EOL;
    
    // Print rows 1-4 (headers)
    for ($r = 1; $r <= min(4, count($rows)); $r++) {
        if (isset($rows[$r])) {
            $cols = [];
            foreach ($rows[$r] as $col => $val) {
                if ($val !== null && $val !== '') {
                    $cols[] = "{$col}=" . str_replace(["\n","\r"], ' ', trim($val));
                }
            }
            echo "  Row {$r}: " . implode(' | ', $cols) . PHP_EOL;
        }
    }
    
    // Print first data row (row 5) fully
    if (isset($rows[5])) {
        echo "  Row 5 (first data): ";
        $cols = [];
        foreach ($rows[5] as $col => $val) {
            $cols[] = "{$col}=" . ($val !== null ? trim($val) : 'NULL');
        }
        echo implode(' | ', $cols) . PHP_EOL;
    }
    echo PHP_EOL;
}

$spreadsheet->disconnectWorksheets();
unset($spreadsheet);

// Now analyze AS A Level too
echo PHP_EOL . "=== ANALYZING: March 2026 AS A Level ===" . PHP_EOL;
$testFile2 = $broadsheetDir . '/AS A/IN016 AS A Level Provisional Component Marks March 2026.xlsx';
$spreadsheet = IOFactory::load($testFile2);
$sheetNames = $spreadsheet->getSheetNames();
echo "Sheets: " . implode(', ', $sheetNames) . PHP_EOL . PHP_EOL;

foreach ($sheetNames as $sheetName) {
    $cleanName = trim($sheetName);
    if (!preg_match('/^\d{4}$/', $cleanName)) continue;
    
    $sheet = $spreadsheet->getSheetByName($sheetName);
    $rows = $sheet->toArray(null, true, true, true);
    
    echo "--- Subject: {$cleanName} ---" . PHP_EOL;
    
    for ($r = 1; $r <= min(4, count($rows)); $r++) {
        if (isset($rows[$r])) {
            $cols = [];
            foreach ($rows[$r] as $col => $val) {
                if ($val !== null && $val !== '') {
                    $cols[] = "{$col}=" . str_replace(["\n","\r"], ' ', trim($val));
                }
            }
            echo "  Row {$r}: " . implode(' | ', $cols) . PHP_EOL;
        }
    }
    
    if (isset($rows[5])) {
        echo "  Row 5 (first data): ";
        $cols = [];
        foreach ($rows[5] as $col => $val) {
            $cols[] = "{$col}=" . ($val !== null ? trim($val) : 'NULL');
        }
        echo implode(' | ', $cols) . PHP_EOL;
    }
    echo PHP_EOL;
}
