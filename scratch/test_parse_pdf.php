<?php
// Script to test parsing a single PDF for Nov 2025 and inspect candidate 0027 RIYA BHANDARI
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AiSpreadsheetParser;

$pdfFile = "D:\\Rashya Sharma\\CIE\\Other Docs\\CIE ALL Broadsheets\\Statement of Result\\Electronic Statements of Results for November 2025.pdf";
$parser = app(AiSpreadsheetParser::class);

echo "Parsing NOV 2025 Statement of Result PDF..." . PHP_EOL;
$parsed = $parser->parse($pdfFile);

foreach ($parsed['candidates'] as $cand) {
    if ($cand['candidate_number'] === '0027') {
        echo "Found candidate in PDF!" . PHP_EOL;
        echo "Name: " . $cand['candidate_name'] . PHP_EOL;
        echo "Number: " . $cand['candidate_number'] . PHP_EOL;
        print_r($cand['results']);
    }
}
