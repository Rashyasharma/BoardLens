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

$quals = Qualification::all()->keyBy('qualification_type');
$subjects = Subject::all();
$seriesCache = ExamSeries::all()->keyBy('series_code');
$candidatesCache = Candidate::where('school_id', $schoolId)->get()->keyBy('candidate_number');

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

$parsedFiles = [];
foreach ($files as $file) {
    if (preg_match('/(?:for\s+)?(March|June|November)\s+(\d{4})/i', basename($file), $m)) {
        $month = ucfirst(strtolower($m[1]));
        $year = (int)$m[2];
        $monthWeight = ['March' => 1, 'June' => 2, 'November' => 3][$month] ?? 0;
        $parsedFiles[] = [
            'path' => $file,
            'year' => $year,
            'month' => $month,
            'weight' => $year * 10 + $monthWeight
        ];
    }
}

usort($parsedFiles, function($a, $b) {
    return $a['weight'] <=> $b['weight'];
});

$batchSize = isset($argv[1]) ? (int)$argv[1] : count($parsedFiles);
$startIndex = isset($argv[2]) ? (int)$argv[2] : 0;

$report = [];

for ($i = $startIndex; $i < min(count($parsedFiles), $startIndex + $batchSize); $i++) {
    $fileData = $parsedFiles[$i];
    $file = $fileData['path'];
    $seriesCode = strtoupper(substr($fileData['month'], 0, 3)) . '-' . $fileData['year'];
    
    echo "Processing [{$seriesCode}] -> " . basename($file) . "...\n";
    
    $parsed = $parser->parse($file);
    if (isset($parsed['error']) || empty($parsed['candidates'])) {
        echo "Failed or empty.\n";
        continue;
    }
    
    $examSeries = $seriesCache->get($seriesCode);
    if (!$examSeries) {
        $examSeries = ExamSeries::create([
            'series_code' => $seriesCode,
            'year' => $fileData['year'],
            'month' => $fileData['month'],
            'is_active' => true,
        ]);
        $seriesCache->put($seriesCode, $examSeries);
    }
    
    $stats = [
        'series' => $seriesCode,
        'year' => $fileData['year'],
        'candidates' => count($parsed['candidates']),
        'subjects_imported' => [],
        'midpoints_applied' => 0,
        'grades_received' => []
    ];
    
    foreach ($parsed['candidates'] as $candData) {
        $candNo = str_pad($candData['candidate_number'], 4, '0', STR_PAD_LEFT);
        $candName = $candData['candidate_name'];
        
        $candidate = Candidate::findOrCreateByNameAndNumber($schoolId, $candNo, $candName);
        
        foreach ($candData['results'] as $subjectCode => $res) {
            $subjQual = $parsed['subjects_mapped'][$subjectCode]['qualification'] ?? $parsed['qualification'];
            $qualId = $quals->get($subjQual)->id ?? $quals->first()->id;
            
            $subject = $subjects->where('subject_code', $subjectCode)->where('qualification_id', $qualId)->first();
            if (!$subject) {
                $subject = $subjects->where('subject_code', $subjectCode)->first();
            }
            if (!$subject) continue;
            
            $stats['subjects_imported'][$subjectCode] = ($stats['subjects_imported'][$subjectCode] ?? 0) + 1;
            
            $enrollment = CandidateEnrollment::firstOrCreate([
                'candidate_id' => $candidate->id,
                'series_id' => $examSeries->id,
                'subject_id' => $subject->id,
            ], [
                'candidate_number' => $candNo,
                'qualification_id' => $qualId,
                'enrollment_status' => 'enrolled',
                'enrolled_date' => now()
            ]);
            
            $grade = $res['grade'];
            $pum = $res['pum'];
            
            $stats['grades_received'][] = ['grade' => $grade, 'pum' => $pum, 'subject' => $subjectCode];
            
            $midpointApplied = false;
            if ($pum <= 0.0 && !in_array($grade, ['U', 'X', 'Q', 'ENTRY', 'PENDING'])) {
                $pum = getCorrectMidpointPum($grade, $subjQual);
                $midpointApplied = true;
                $stats['midpoints_applied']++;
            }
            
            $isPassed = in_array(strtoupper($grade), ['A*','A','B','C','D','E','AA','BB','CC','DD','EE']);
            if (in_array(strtolower($grade), ['a','b','c','d','e'])) {
                $isPassed = true;
            }
            
            $existingRes = SubjectResult::where('enrollment_id', $enrollment->id)->where('subject_id', $subject->id)->first();
            
            if ($existingRes) {
                $existingRes->update([
                    'grade' => $grade,
                    'pum' => $pum,
                    'is_passed' => $isPassed
                ]);
            } else {
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
            }
        }
    }
    
    $report[] = $stats;
}

echo json_encode(['report' => $report, 'nextIndex' => $startIndex + $batchSize, 'total' => count($parsedFiles)]);
