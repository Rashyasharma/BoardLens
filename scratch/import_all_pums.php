<?php
// Script to parse ALL Statement of Result PDFs and update the database with PUM values if matched.
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\AiSpreadsheetParser;
use App\Models\Candidate;
use App\Models\Subject;
use App\Models\SubjectResult;
use App\Models\ExamSeries;
use Illuminate\Support\Facades\DB;

$pdfDir = "D:\\Rashya Sharma\\CIE\\Other Docs\\CIE ALL Broadsheets\\Statement of Result";
$parser = app(AiSpreadsheetParser::class);

$pdfFiles = glob($pdfDir . "/*.pdf");
echo "Found " . count($pdfFiles) . " PDF files to process." . PHP_EOL;

$totalMatches = 0;
$totalUpdated = 0;

foreach ($pdfFiles as $pdfFile) {
    echo "Processing " . basename($pdfFile) . "... ";
    try {
        $parsed = $parser->parse($pdfFile);
        
        $seriesMonth = $parsed['series']['month'];
        $seriesYear = $parsed['series']['year'];
        $seriesCode = strtoupper(substr($seriesMonth, 0, 3)) . '-' . $seriesYear;
        
        // Find series
        $series = ExamSeries::where('series_code', $seriesCode)->first();
        if (!$series) {
            echo "Series {$seriesCode} not found in database. Skipping." . PHP_EOL;
            continue;
        }
        
        $candidates = $parsed['candidates'];
        $fileMatches = 0;
        $fileUpdated = 0;
        
        foreach ($candidates as $cand) {
            $candNo = $cand['candidate_number'];
            $candName = $cand['candidate_name'];
            
            // Look up candidate
            $dbCand = Candidate::where('candidate_number', $candNo)->first();
            if (!$dbCand) {
                // Try fuzzy name
                $dbCand = Candidate::where('candidate_name', 'like', $candName)->first();
            }
            
            if (!$dbCand) {
                continue;
            }
            
            foreach ($cand['results'] as $subCode => $res) {
                $pum = $res['pum'] ?? 0;
                $grade = $res['grade'] ?? '';
                
                if ($pum <= 0) {
                    continue;
                }
                
                // Find subject
                $subject = Subject::where('subject_code', $subCode)->first();
                if (!$subject) {
                    continue;
                }
                
                // Find subject result
                $dbResult = SubjectResult::where('subject_id', $subject->id)
                    ->where('series_id', $series->id)
                    ->whereHas('enrollment', function ($q) use ($dbCand) {
                        $q->where('candidate_id', $dbCand->id);
                    })
                    ->first();
                
                if ($dbResult) {
                    $fileMatches++;
                    $totalMatches++;
                    
                    // Update if PUM is 0 or different
                    if ((float)$dbResult->pum != (float)$pum) {
                        $dbResult->pum = $pum;
                        // Keep grade from pdf if available or keep existing
                        if (!empty($grade) && !in_array($grade, ['ENTRY'])) {
                            $dbResult->grade = $grade;
                        }
                        $dbResult->save();
                        $fileUpdated++;
                        $totalUpdated++;
                    }
                }
            }
        }
        
        echo "Found {$fileMatches} matches, updated {$fileUpdated} PUMs." . PHP_EOL;
        
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . PHP_EOL;
    }
}

echo "=== Processing Completed ===" . PHP_EOL;
echo "Total Matches Found: {$totalMatches}" . PHP_EOL;
echo "Total PUMs Updated: {$totalUpdated}" . PHP_EOL;
