<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;
use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\Qualification;
use App\Services\AiSpreadsheetParser;
use Illuminate\Support\Str;

$pdfDir = "D:\\Rashya Sharma\\CIE\\Other Docs\\CIE ALL Broadsheets\\Statement of Result";
$parser = app(AiSpreadsheetParser::class);

$schoolId = '019e5ed2-be69-7193-b485-69770f96e60c'; // default school
$uploaderId = '019e5ed2-bf97-7316-bceb-62683a3c8666'; // admin user

// Pre-load quals, subjects, series
$quals = Qualification::all()->keyBy('qualification_type');
$subjects = Subject::all();
$seriesCache = ExamSeries::all()->keyBy('series_code');

$candidatesCache = Candidate::where('school_id', $schoolId)->get()->keyBy('candidate_number');
$enrollmentsCache = CandidateEnrollment::get()->groupBy('candidate_id');

function getCorrectMidpointPum(string $grade, string $qualification): float
{
    $g = trim($grade);
    if ($qualification === 'AS_A_LEVEL' && in_array($g, ['a', 'b', 'c', 'd', 'e'])) {
        $asMap = ['a' => 90.0, 'b' => 74.5, 'c' => 64.5, 'd' => 54.5, 'e' => 44.5];
        return $asMap[$g] ?? 0.0;
    }
    
    switch (strtoupper($g)) {
        case 'A*': case 'A*A*': return 95.0;
        case 'A': case 'AA': return 84.5;
        case 'B': case 'BB': return 74.5;
        case 'C': case 'CC': return 64.5;
        case 'D': case 'DD': return 54.5;
        case 'E': case 'EE': return 44.5;
        case 'F': case 'FF': return 34.5;
        case 'G': case 'GG': return 24.5;
        default: return 0.0;
    }
}

$files = glob($pdfDir . '/*.pdf');
echo "Found " . count($files) . " PDF files to parse." . PHP_EOL;

$totalUpdated = 0;
$totalCreated = 0;

foreach ($files as $file) {
    echo "Processing " . basename($file) . "..." . PHP_EOL;
    $parsed = $parser->parse($file);
    if (isset($parsed['error']) || empty($parsed['candidates'])) {
        echo "  Failed or empty: " . ($parsed['error'] ?? 'No candidates found') . PHP_EOL;
        continue;
    }
    
    $seriesData = $parsed['series'] ?? null;
    if (!$seriesData) {
        // Try parsing from filename
        if (preg_match('/(March|June|November)\s+(\d{4})/i', basename($file), $m)) {
            $seriesData = ['month' => ucfirst(strtolower($m[1])), 'year' => $m[2]];
        } else {
            echo "  Cannot determine series for " . basename($file) . PHP_EOL;
            continue;
        }
    }
    
    $seriesCode = strtoupper(substr($seriesData['month'], 0, 3)) . '-' . $seriesData['year'];
    $examSeries = $seriesCache->get($seriesCode);
    if (!$examSeries) {
        $examSeries = ExamSeries::create([
            'series_code' => $seriesCode,
            'year' => $seriesData['year'],
            'month' => $seriesData['month'],
            'is_active' => true,
        ]);
        $seriesCache->put($seriesCode, $examSeries);
        echo "  Created series $seriesCode" . PHP_EOL;
    }
    
    foreach ($parsed['candidates'] as $candData) {
        $candNo = str_pad($candData['candidate_number'], 4, '0', STR_PAD_LEFT);
        $candName = $candData['candidate_name'];
        
        $candidate = $candidatesCache->get($candNo);
        if (!$candidate) {
            $candidate = Candidate::create([
                'candidate_number' => $candNo,
                'candidate_name' => $candName,
                'school_id' => $schoolId,
                'enrollment_date' => now(),
                'status' => 'active',
            ]);
            $candidatesCache->put($candNo, $candidate);
        }
        
        foreach ($candData['results'] as $subjectCode => $res) {
            // Find subject
            $subjQual = $parsed['subjects_mapped'][$subjectCode]['qualification'] ?? $parsed['qualification'];
            $qualId = $quals->get($subjQual)->id ?? $quals->first()->id;
            
            // Computer Science 2020-2021 uses 9608, rest 9618 logic isn't strictly needed if we just match code
            $subject = $subjects->where('subject_code', $subjectCode)->where('qualification_id', $qualId)->first();
            if (!$subject) {
                // Try fallback mapped code
                $subject = $subjects->where('subject_code', $subjectCode)->first();
            }
            if (!$subject) continue;
            
            // Ensure subject enrollment
            $enrollment = CandidateEnrollment::firstOrCreate([
                'candidate_id' => $candidate->id,
                'series_id' => $examSeries->id,
                'subject_id' => $subject->id,
            ], [
                'qualification_id' => $qualId,
                'enrollment_status' => 'enrolled',
                'enrolled_date' => now()
            ]);
            
            $grade = $res['grade'];
            $pum = $res['pum'];
            
            // Apply midpoint if needed
            if ($pum <= 0.0 && !in_array($grade, ['U', 'X', 'Q', 'ENTRY', 'PENDING'])) {
                $pum = getCorrectMidpointPum($grade, $subjQual);
            }
            
            $isPassed = in_array(strtoupper($grade), ['A*','A','B','C','D','E','AA','BB','CC','DD','EE']);
            if (in_array(strtolower($grade), ['a','b','c','d','e'])) {
                $isPassed = true;
            }
            
            $existingRes = SubjectResult::where('enrollment_id', $enrollment->id)->where('subject_id', $subject->id)->first();
            
            if ($existingRes) {
                // Update
                if ($existingRes->grade !== $grade || $existingRes->pum != $pum) {
                    $existingRes->update([
                        'grade' => $grade,
                        'pum' => $pum,
                        'is_passed' => $isPassed
                    ]);
                    $totalUpdated++;
                }
            } else {
                // Create
                SubjectResult::create([
                    'enrollment_id' => $enrollment->id,
                    'subject_id' => $subject->id,
                    'series_id' => $examSeries->id,
                    'grade' => $grade,
                    'pum' => $pum,
                    'is_passed' => $isPassed,
                    'status' => 'complete',
                    'result_uploaded_at' => now(),
                    'uploaded_by' => $uploaderId
                ]);
                $totalCreated++;
            }
        }
    }
}

echo "Completed processing Statement of Results PDFs." . PHP_EOL;
echo "Created $totalCreated new results." . PHP_EOL;
echo "Updated $totalUpdated existing results." . PHP_EOL;
