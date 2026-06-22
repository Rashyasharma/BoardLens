<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Find ALL Vikram records
echo "=== ALL VIKRAM KUMAR PRAJAPAT RECORDS ===\n";
$vikrams = DB::table('candidates')->where('candidate_name', 'like', '%VIKRAM%PRAJAPAT%')->get();

foreach ($vikrams as $v) {
    echo "\nCandidate: {$v->candidate_name} (ID: {$v->id}, Number: {$v->candidate_number})\n";
    
    $results = DB::table('subject_results as sr')
        ->join('subjects as s', 'sr.subject_id', '=', 's.id')
        ->join('exam_series as es', 'sr.series_id', '=', 'es.id')
        ->join('candidate_enrollments as ce', 'sr.enrollment_id', '=', 'ce.id')
        ->where('ce.candidate_id', $v->id)
        ->select('sr.id', 's.subject_name', 's.subject_code', 'es.month', 'es.year', 'sr.grade', 'sr.pum', 'sr.status')
        ->orderBy('es.year')
        ->orderBy('es.month')
        ->get();
    
    foreach ($results as $r) {
        $flag = (stripos($r->subject_name, 'Account') !== false) ? ' <<<< ACCOUNTING' : '';
        echo "  {$r->month} {$r->year} | {$r->subject_name} ({$r->subject_code}) | Grade: {$r->grade} | PUM: {$r->pum}{$flag}\n";
    }
}

// Check what the SoR PDF says about Vikram in June 2025
echo "\n=== CHECKING June 2025 SoR PDF for VIKRAM ===\n";
$cmd = 'python -c "
import fitz
pdf = fitz.open(r\'D:\\Rashya Sharma\\CIE\\Other Docs\\CIE ALL Broadsheets\\Statement of Result\\Electronic Statements of Results for June 2025.pdf\')
for i, page in enumerate(pdf):
    text = page.get_text()
    if \'VIKRAM\' in text.upper() or \'PRAJAPAT\' in text.upper():
        print(f\'=== Page {i+1} ===\')
        print(text)
        print()
pdf.close()
"';
passthru($cmd);
