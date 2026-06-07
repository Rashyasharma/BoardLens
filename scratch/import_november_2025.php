<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Qualification;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use App\Models\UploadLog;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

$filePath = "C:\\Users\\HP11\\Desktop\\new\\Electronic Results File for November 2025 IGCSE.xls";
if (!file_exists($filePath)) {
    die("File does not exist at $filePath\n");
}

echo "Loading file using PhpOffice\\PhpSpreadsheet\\IOFactory::load...\n";
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);

echo "Total rows read: " . count($rows) . "\n";

// The sheet is a broadsheet structure.
// Row 1 contains the headers: Cand. No, Candidate Name, then subjects, then grade counts (A*, A, B...)
$headerRow = $rows[1];
$subjectsMapping = []; // column_key => Subject Model

// Target series
$series = ExamSeries::where('year', 2025)->where('month', 'November')->first();
if (!$series) {
    die("Error: Exam Series for November 2025 not found in database!\n");
}
$seriesId = $series->id;
echo "Found Exam Series: {$series->series_name} (ID: {$seriesId})\n";

// Target qualification (IGCSE)
$qualification = Qualification::where('qualification_type', 'IGCSE')->first();
if (!$qualification) {
    die("Error: IGCSE qualification not found!\n");
}
$qualificationId = $qualification->id;
echo "Found Qualification: {$qualification->qualification_name} (ID: {$qualificationId})\n";

// Default school and user (admin)
$schoolId = '019e5ed2-be69-7193-b485-69770f96e60c'; // Lucky International School
$uploaderId = '019e5ed2-bf97-7316-bceb-62683a3c8666'; // Admin User

// Let's identify the subject columns
// Valid subjects from sheet header:
// BUSINESS STUDIES, COMBINED SCIENCE, ENGLISH AS A SECOND LANGUAGE, HINDI AS A SECOND LANGUAGE, INFORMATION AND COMMUNICATION
$knownSubjects = [
    'BUSINESS STUDIES' => '0450',
    'COMBINED SCIENCE' => '0653',
    'ENGLISH AS A SECOND LANGUAGE' => '0510',
    'HINDI AS A SECOND LANGUAGE' => '0549',
    'INFORMATION AND COMMUNICATION' => '0417'
];

foreach ($headerRow as $col => $val) {
    if (!$val) continue;
    $valUpper = strtoupper(trim($val));
    if (isset($knownSubjects[$valUpper])) {
        // Find subject in DB
        $code = $knownSubjects[$valUpper];
        $subj = Subject::where('subject_code', $code)->where('qualification_id', $qualificationId)->first();
        if ($subj) {
            $subjectsMapping[$col] = $subj;
            echo "Column {$col}: Mapped '{$val}' to Subject ID {$subj->id} (Code: {$subj->subject_code})\n";
        } else {
            echo "WARNING: Subject '{$val}' (Code: {$code}) not found in database for IGCSE!\n";
        }
    }
}

if (empty($subjectsMapping)) {
    die("Error: No subjects could be mapped from the header row!\n");
}

$pumMap = [
    'A*' => 90.0,
    'A' => 80.0,
    'B' => 70.0,
    'C' => 60.0,
    'D' => 50.0,
    'E' => 40.0,
    'F' => 30.0,
    'G' => 20.0,
    'U' => 0.0
];

$processedCount = 0;
$failedCount = 0;
$errors = [];

DB::beginTransaction();

try {
    for ($i = 2; $i <= count($rows); $i++) {
        $row = $rows[$i];
        $candNo = isset($row['A']) ? trim($row['A']) : null;
        $candName = isset($row['B']) ? trim($row['B']) : null;
        
        if (empty($candNo) && empty($candName)) {
            continue; // Skip empty rows
        }
        
        if (empty($candNo) || empty($candName)) {
            $failedCount++;
            $errors[] = ["row" => $i, "error" => "Candidate number or name is empty"];
            echo "Row {$i}: Failed - Candidate number or name is empty.\n";
            continue;
        }
        
        // Pad candidate number to 4 digits
        $candNo = str_pad($candNo, 4, '0', STR_PAD_LEFT);
        
        // Find or create candidate
        $candidate = Candidate::where('school_id', $schoolId)
            ->where('candidate_number', $candNo)
            ->where('candidate_name', $candName)
            ->first();
            
        if (!$candidate) {
            $candidate = Candidate::create([
                'candidate_number' => $candNo,
                'candidate_name' => $candName,
                'school_id' => $schoolId,
                'enrollment_date' => now()->toDateString(),
                'status' => 'active'
            ]);
            echo "Row {$i}: Created candidate {$candNo} - {$candName}\n";
        }
        
        // Ensure general enrollment exists
        $generalEnrollment = CandidateEnrollment::firstOrCreate(
            [
                'candidate_id' => $candidate->id,
                'series_id' => $seriesId,
                'qualification_id' => $qualificationId,
                'subject_id' => null,
            ],
            [
                'enrolled_date' => now()->toDateString(),
                'enrollment_status' => 'enrolled',
            ]
        );
        
        // Loop through mapped subject columns
        foreach ($subjectsMapping as $col => $subject) {
            $gradeVal = isset($row[$col]) ? trim($row[$col]) : null;
            if ($gradeVal === null || $gradeVal === '' || strtolower($gradeVal) === 'nan') {
                continue; // Candidate did not take this subject
            }
            
            // Clean grade value (remove trailing '^' if any)
            $grade = preg_replace('/[^A-Za-z0-9*]/', '', $gradeVal);
            
            $pum = isset($pumMap[$grade]) ? $pumMap[$grade] : 0.0;
            
            // Ensure subject-specific enrollment exists
            CandidateEnrollment::firstOrCreate(
                [
                    'candidate_id' => $candidate->id,
                    'series_id' => $seriesId,
                    'qualification_id' => $qualificationId,
                    'subject_id' => $subject->id,
                ],
                [
                    'enrolled_date' => now()->toDateString(),
                    'enrollment_status' => 'enrolled',
                ]
            );
            
            // Create or update SubjectResult
            $result = SubjectResult::updateOrCreate(
                [
                    'enrollment_id' => $generalEnrollment->id,
                    'subject_id' => $subject->id,
                    'series_id' => $seriesId,
                ],
                [
                    'grade' => $grade,
                    'pum' => $pum,
                    'status' => 'pending_components',
                    'result_uploaded_at' => now(),
                    'uploaded_by' => $uploaderId,
                ]
            );
            
            echo "  Subject: {$subject->subject_name} | Grade: {$grade} | PUM: {$pum}\n";
            $processedCount++;
        }
    }
    
    // Create Upload Log
    UploadLog::create([
        'uploaded_by' => $uploaderId,
        'school_id' => $schoolId,
        'series_id' => $seriesId,
        'subject_id' => null, // Multiple subjects
        'file_name' => basename($filePath),
        'file_path' => $filePath,
        'upload_type' => 'candidate_data',
        'records_processed' => $processedCount,
        'records_failed' => $failedCount,
        'status' => $failedCount > 0 ? ($processedCount > 0 ? 'partial' : 'failed') : 'success',
        'error_details' => $errors,
        'uploaded_at' => now()
    ]);
    
    DB::commit();
    echo "\nSUCCESS: Imported {$processedCount} subject results. Failed: {$failedCount}.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR during transaction: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
