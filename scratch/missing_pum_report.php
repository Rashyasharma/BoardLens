<?php
// Find missing PUM chronologically by exam series, filtered (exclude X, Q, PENDING, empty grades)
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Define series order for sorting
$monthOrder = ['March' => 1, 'June' => 2, 'November' => 3];

// Get all results with PUM = 0 and a valid grade
$missing = DB::table('subject_results as sr')
    ->join('subjects as s', 'sr.subject_id', '=', 's.id')
    ->join('exam_series as es', 'sr.series_id', '=', 'es.id')
    ->join('candidate_enrollments as ce', 'sr.enrollment_id', '=', 'ce.id')
    ->join('candidates as c', 'ce.candidate_id', '=', 'c.id')
    ->where('sr.pum', 0)
    ->whereNotNull('sr.grade')
    ->where('sr.grade', '!=', '')
    ->select(
        'es.month', 'es.year', 'es.series_code',
        's.subject_name', 's.subject_code',
        'c.candidate_name', 'c.candidate_number',
        'sr.grade', 'sr.pum', 'sr.status as result_status'
    )
    ->orderBy('es.year')
    ->orderByRaw("CASE es.month WHEN 'March' THEN 1 WHEN 'June' THEN 2 WHEN 'November' THEN 3 ELSE 4 END")
    ->orderBy('s.subject_name')
    ->orderBy('c.candidate_name')
    ->get();

echo "=== MISSING PUM REPORT (Chronological by Exam Series) ===\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n";
echo "Total results with PUM=0: " . $missing->count() . "\n\n";

$currentSeries = '';
$seriesCount = 0;

foreach ($missing as $row) {
    $seriesKey = "{$row->month} {$row->year}";
    
    if ($seriesKey !== $currentSeries) {
        if ($currentSeries !== '') {
            echo "  --- Series subtotal: {$seriesCount} missing ---\n\n";
        }
        $currentSeries = $seriesKey;
        $seriesCount = 0;
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "  📅 {$seriesKey} ({$row->series_code})\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }
    
    $gradeLabel = $row->grade;
    $statusFlag = '';
    if (in_array(strtoupper($row->grade), ['X', 'Q', 'PENDING'])) {
        $statusFlag = ' [EXCLUDED - No real grade]';
    }
    if (strtoupper($row->grade) === 'U' || strtoupper($row->grade) === 'UNGRADED') {
        $statusFlag = ' [U/Ungraded]';
    }
    
    echo "  {$row->candidate_name} ({$row->candidate_number}) | {$row->subject_name} ({$row->subject_code}) | Grade: {$gradeLabel}{$statusFlag}\n";
    $seriesCount++;
}

if ($currentSeries !== '') {
    echo "  --- Series subtotal: {$seriesCount} missing ---\n";
}

// Summary by series
echo "\n\n=== SUMMARY BY EXAM SERIES ===\n";
$summary = DB::table('subject_results as sr')
    ->join('exam_series as es', 'sr.series_id', '=', 'es.id')
    ->select(
        'es.month', 'es.year',
        DB::raw('COUNT(*) as total'),
        DB::raw('SUM(CASE WHEN sr.pum > 0 THEN 1 ELSE 0 END) as with_pum'),
        DB::raw('SUM(CASE WHEN sr.pum = 0 THEN 1 ELSE 0 END) as without_pum')
    )
    ->groupBy('es.month', 'es.year')
    ->orderBy('es.year')
    ->orderByRaw("CASE es.month WHEN 'March' THEN 1 WHEN 'June' THEN 2 WHEN 'November' THEN 3 ELSE 4 END")
    ->get();

echo str_pad("Exam Series", 22) . str_pad("Total", 8) . str_pad("Has PUM", 10) . str_pad("Missing", 10) . "Coverage\n";
echo str_repeat("-", 60) . "\n";

foreach ($summary as $s) {
    $pct = $s->total > 0 ? round(($s->with_pum / $s->total) * 100, 1) : 0;
    $icon = $pct == 100 ? '✅' : ($pct >= 80 ? '🟡' : '🔴');
    echo str_pad("{$s->month} {$s->year}", 22) 
        . str_pad($s->total, 8) 
        . str_pad($s->with_pum, 10) 
        . str_pad($s->without_pum, 10) 
        . "{$pct}% {$icon}\n";
}
