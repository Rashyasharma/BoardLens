<?php
/**
 * Robust PUM Import Script v2
 * 
 * Improvements over v1:
 * 1. Matches candidates by name AND series (not just candidate_number which is not unique)
 * 2. Handles multiple candidate records for the same person
 * 3. Better logging of what's matched and what's not
 */
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AiSpreadsheetParser;
use Illuminate\Support\Facades\DB;

$pdfDir = "D:\\Rashya Sharma\\CIE\\Other Docs\\CIE ALL Broadsheets\\Statement of Result";
$parser = app(AiSpreadsheetParser::class);

$pdfFiles = glob($pdfDir . "/*.pdf");
echo "Found " . count($pdfFiles) . " PDF files to process.\n\n";

$totalMatches = 0;
$totalUpdated = 0;
$totalNotFound = 0;
$notFoundDetails = [];

foreach ($pdfFiles as $pdfFile) {
    $basename = basename($pdfFile);
    echo "=== Processing: {$basename} ===\n";
    
    try {
        $parsed = $parser->parse($pdfFile);
        
        $seriesMonth = $parsed['series']['month'] ?? null;
        $seriesYear = $parsed['series']['year'] ?? null;
        
        if (!$seriesMonth || !$seriesYear) {
            echo "  ERROR: Could not determine series from PDF. Skipping.\n\n";
            continue;
        }
        
        echo "  Series: {$seriesMonth} {$seriesYear}\n";
        
        // Find matching exam_series by month and year
        $series = DB::table('exam_series')
            ->where('month', $seriesMonth)
            ->where('year', $seriesYear)
            ->first();
        
        if (!$series) {
            // Also try series_code format
            $seriesCode = strtoupper(substr($seriesMonth, 0, 3)) . '-' . $seriesYear;
            $series = DB::table('exam_series')->where('series_code', $seriesCode)->first();
        }
        
        if (!$series) {
            echo "  WARNING: Series {$seriesMonth} {$seriesYear} not found in database. Skipping.\n\n";
            continue;
        }
        
        $candidates = $parsed['candidates'] ?? [];
        echo "  Parsed " . count($candidates) . " candidates from PDF\n";
        
        $fileMatches = 0;
        $fileUpdated = 0;
        $fileNotFound = 0;
        
        foreach ($candidates as $cand) {
            $candNo = $cand['candidate_number'] ?? '';
            $candName = strtoupper(trim($cand['candidate_name'] ?? ''));
            
            if (empty($candName) && empty($candNo)) continue;
            
            foreach ($cand['results'] as $subCode => $res) {
                $pum = $res['pum'] ?? 0;
                $grade = $res['grade'] ?? '';
                
                if ($pum <= 0) continue;
                if (in_array(strtoupper($grade), ['ENTRY', 'X', 'Q', 'PENDING'])) continue;
                
                // Find the subject
                $subject = DB::table('subjects')->where('subject_code', $subCode)->first();
                if (!$subject) continue;
                
                // Strategy 1: Find by candidate_number + series
                $result = DB::table('subject_results as sr')
                    ->join('candidate_enrollments as ce', 'sr.enrollment_id', '=', 'ce.id')
                    ->join('candidates as c', 'ce.candidate_id', '=', 'c.id')
                    ->where('sr.series_id', $series->id)
                    ->where('sr.subject_id', $subject->id)
                    ->where('c.candidate_number', $candNo)
                    ->select('sr.id', 'sr.pum', 'sr.grade')
                    ->first();
                
                // Strategy 2: If not found by number, try by name
                if (!$result && !empty($candName)) {
                    $result = DB::table('subject_results as sr')
                        ->join('candidate_enrollments as ce', 'sr.enrollment_id', '=', 'ce.id')
                        ->join('candidates as c', 'ce.candidate_id', '=', 'c.id')
                        ->where('sr.series_id', $series->id)
                        ->where('sr.subject_id', $subject->id)
                        ->whereRaw('UPPER(c.candidate_name) = ?', [$candName])
                        ->select('sr.id', 'sr.pum', 'sr.grade')
                        ->first();
                }
                
                // Strategy 3: Try fuzzy name match (LIKE)
                if (!$result && !empty($candName)) {
                    $nameParts = explode(' ', $candName);
                    if (count($nameParts) >= 2) {
                        $result = DB::table('subject_results as sr')
                            ->join('candidate_enrollments as ce', 'sr.enrollment_id', '=', 'ce.id')
                            ->join('candidates as c', 'ce.candidate_id', '=', 'c.id')
                            ->where('sr.series_id', $series->id)
                            ->where('sr.subject_id', $subject->id)
                            ->where('c.candidate_name', 'like', '%' . $nameParts[0] . '%' . $nameParts[count($nameParts)-1] . '%')
                            ->select('sr.id', 'sr.pum', 'sr.grade')
                            ->first();
                    }
                }
                
                if ($result) {
                    $fileMatches++;
                    $totalMatches++;
                    
                    // Only update if PUM is currently 0 or different from SoR value
                    if ((float)$result->pum == 0 || (float)$result->pum != (float)$pum) {
                        DB::table('subject_results')
                            ->where('id', $result->id)
                            ->update([
                                'pum' => $pum,
                                'updated_at' => now()
                            ]);
                        $fileUpdated++;
                        $totalUpdated++;
                    }
                } else {
                    $fileNotFound++;
                    $totalNotFound++;
                    $notFoundDetails[] = "{$seriesMonth} {$seriesYear} | {$candName} ({$candNo}) | {$subCode} | Grade: {$grade} | PUM: {$pum}";
                }
            }
        }
        
        echo "  Matched: {$fileMatches}, Updated: {$fileUpdated}, Not Found: {$fileNotFound}\n\n";
        
    } catch (\Exception $e) {
        echo "  ERROR: " . $e->getMessage() . "\n\n";
    }
}

echo "=== SUMMARY ===\n";
echo "Total Matches: {$totalMatches}\n";
echo "Total PUMs Updated: {$totalUpdated}\n";
echo "Total Not Found in DB: {$totalNotFound}\n";

if (!empty($notFoundDetails)) {
    echo "\n=== NOT FOUND DETAILS (first 50) ===\n";
    foreach (array_slice($notFoundDetails, 0, 50) as $detail) {
        echo "  {$detail}\n";
    }
}

// Final status
echo "\n=== FINAL PUM STATUS ===\n";
$total = DB::table('subject_results')->count();
$withPum = DB::table('subject_results')->where('pum', '>', 0)->count();
$zeroPum = DB::table('subject_results')->where('pum', 0)->count();
echo "Total results: {$total}\n";
echo "With PUM > 0: {$withPum}\n";
echo "PUM = 0: {$zeroPum}\n";
