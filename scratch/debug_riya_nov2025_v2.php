<?php
// Focus on Riya Bhandary and the SoR parsing issue
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Check for Riya Bhandary in database
echo "=== RIYA BHANDARY IN DATABASE ===\n";
$riya = DB::table('candidates')->where('candidate_name', 'like', '%BHAND%')->get();
if ($riya->isEmpty()) {
    $riya = DB::table('candidates')->where('candidate_name', 'like', '%bhand%')->get();
}
if ($riya->isEmpty()) {
    echo "NOT FOUND in database!\n";
    // Search more broadly
    echo "\nSearching broadly for 'Riya'...\n";
    $riya = DB::table('candidates')->where('candidate_name', 'like', '%Riya%')->get();
    foreach ($riya as $c) {
        echo "  Found: {$c->candidate_name} (Number: {$c->candidate_number})\n";
    }
} else {
    foreach ($riya as $c) {
        echo "Candidate: {$c->candidate_name} (ID: {$c->id}, Number: {$c->candidate_number})\n";
        
        $results = DB::table('subject_results as sr')
            ->join('subjects as s', 'sr.subject_id', '=', 's.id')
            ->join('exam_series as es', 'sr.series_id', '=', 'es.id')
            ->join('candidate_enrollments as ce', 'sr.enrollment_id', '=', 'ce.id')
            ->where('ce.candidate_id', $c->id)
            ->select('s.subject_name', 's.subject_code', 'es.month', 'es.year', 'sr.grade', 'sr.pum', 'sr.status')
            ->orderBy('es.year')
            ->orderBy('es.month')
            ->get();
        
        if ($results->isEmpty()) {
            echo "  No results found!\n";
        }
        foreach ($results as $r) {
            $pumLabel = $r->pum > 0 ? $r->pum : '❌ MISSING';
            echo "  {$r->month} {$r->year} | {$r->subject_name} ({$r->subject_code}) | Grade: {$r->grade} | PUM: {$pumLabel} | Status: {$r->status}\n";
        }
    }
}

// 2. Check the SoR PDF for November 2025 to find Riya
echo "\n=== CHECKING SoR PDF for November 2025 ===\n";
$pdfPath = 'D:\\Rashya Sharma\\CIE\\Other Docs\\CIE ALL Broadsheets\\Statement of Result\\Electronic Statements of Results for November 2025.pdf';
if (file_exists($pdfPath)) {
    echo "PDF exists, size: " . filesize($pdfPath) . " bytes\n";
    // Use Python to parse it
    $cmd = 'python -c "
import subprocess
try:
    import pdfplumber
except:
    subprocess.check_call([\'pip\', \'install\', \'pdfplumber\'])
    import pdfplumber

pdf = pdfplumber.open(r\'' . str_replace("'", "\\'", $pdfPath) . '\')
found = False
for i, page in enumerate(pdf.pages):
    text = page.extract_text() or \'\'
    if \'BHAND\' in text.upper() or \'RIYA\' in text.upper():
        print(f\'Page {i+1}:\')
        # Print surrounding context
        lines = text.split(chr(10))
        for j, line in enumerate(lines):
            if \'BHAND\' in line.upper() or \'RIYA\' in line.upper():
                start = max(0, j-2)
                end = min(len(lines), j+10)
                for k in range(start, end):
                    marker = \' >>> \' if k == j else \'     \'
                    print(f\'{marker}{lines[k]}\')
                print()
                found = True
if not found:
    print(\'RIYA/BHANDARY not found in this PDF\')
pdf.close()
"';
    passthru($cmd);
} else {
    echo "PDF not found at: $pdfPath\n";
}

// 3. Also check the import_all_pums.php logic
echo "\n=== CHECKING IMPORT SCRIPT LOGIC ===\n";
$importScript = file_get_contents(__DIR__ . '/import_all_pums.php');
echo "Import script exists: " . (strlen($importScript) > 0 ? 'YES' : 'NO') . "\n";
echo "Script size: " . strlen($importScript) . " bytes\n";
