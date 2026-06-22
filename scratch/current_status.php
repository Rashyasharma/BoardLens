<?php
// Comprehensive status check with correct table names
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CURRENT DATABASE STATUS ===\n\n";
$total = DB::table('subject_results')->count();
$withPum = DB::table('subject_results')->where('pum', '>', 0)->count();
$zeroPum = DB::table('subject_results')->where('pum', 0)->count();
echo "Total results: $total\n";
echo "With PUM > 0: $withPum\n";
echo "PUM = 0: $zeroPum\n\n";

// Check for Riya Bhandary
echo "=== RIYA BHANDARY CHECK ===\n";
$riya = DB::table('candidates')->where('candidate_name', 'like', '%Riya%Bhand%')->get();
if ($riya->isEmpty()) {
    // Try broader search
    $riya = DB::table('candidates')->where('candidate_name', 'like', '%BHAND%')->get();
}
if ($riya->isEmpty()) {
    $riya = DB::table('candidates')->where('candidate_name', 'like', '%Riya%')->get();
}

echo "Found " . $riya->count() . " matching candidates\n";
foreach ($riya as $c) {
    echo "\nCandidate: {$c->candidate_name} (ID: {$c->id}, Number: {$c->candidate_number})\n";
    
    // Get results via subject_results which has series_id directly
    $results = DB::table('subject_results as sr')
        ->join('subjects as s', 'sr.subject_id', '=', 's.id')
        ->join('exam_series as es', 'sr.series_id', '=', 'es.id')
        ->join('candidate_enrollments as ce', 'sr.enrollment_id', '=', 'ce.id')
        ->where('ce.candidate_id', $c->id)
        ->select('s.subject_name', 's.subject_code', 'es.month', 'es.year', 'sr.grade', 'sr.pum', 'sr.status')
        ->orderBy('es.year')
        ->orderBy('es.month')
        ->get();
    
    foreach ($results as $r) {
        echo "  {$r->month} {$r->year} | {$r->subject_name} ({$r->subject_code}) | Grade: {$r->grade} | PUM: {$r->pum} | Status: {$r->status}\n";
    }
}

echo "\n=== MISSING PUM SUMMARY (PUM=0 with valid grade) ===\n";
$missing = DB::table('subject_results as sr')
    ->join('subjects as s', 'sr.subject_id', '=', 's.id')
    ->join('exam_series as es', 'sr.series_id', '=', 'es.id')
    ->where('sr.pum', 0)
    ->whereNotNull('sr.grade')
    ->where('sr.grade', '!=', '')
    ->whereNotIn('sr.grade', ['X', 'Q', 'PENDING', ''])
    ->select('es.month', 'es.year', 's.subject_name', 's.subject_code', DB::raw('COUNT(*) as cnt'))
    ->groupBy('es.month', 'es.year', 's.subject_name', 's.subject_code')
    ->orderBy('es.year')
    ->orderBy('es.month')
    ->get();

foreach ($missing as $m) {
    echo "  {$m->month} {$m->year} | {$m->subject_name} ({$m->subject_code}) | Missing PUM count: {$m->cnt}\n";
}

// Check Statement of Result PDFs available
echo "\n=== STATEMENT OF RESULT PDFs AVAILABLE ===\n";
$pdfDir = 'D:\\Rashya Sharma\\CIE\\Other Docs\\CIE ALL Broadsheets\\Statement of Result';
if (is_dir($pdfDir)) {
    $files = glob($pdfDir . '/*.pdf');
    foreach ($files as $f) {
        echo "  " . basename($f) . "\n";
    }
    echo "Total PDFs: " . count($files) . "\n";
} else {
    echo "  Directory not found!\n";
}
