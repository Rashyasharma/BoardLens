<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Candidate;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\Qualification;
use App\Models\School;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use App\Models\Component;
use App\Models\ComponentMarks;
use App\Models\User;

$filePath = "D:\\Rashya Sharma\\CIE\\June 2026\\Result\\IN016 AS A Level Provisional Component Marks June 2026.xlsx";
$spreadsheet = IOFactory::load($filePath);

$admin = User::first();
$series = ExamSeries::firstOrCreate(
    ['year' => 2026, 'month' => 'June'],
    [
        'series_code' => 'JUN-2026',
        'series_name' => 'June 2026',
        'is_active' => true
    ]
);
$school = School::firstOrCreate(
    ['school_name' => 'Lucky International School'],
    ['school_code' => 'IN016']
);
$asLevel = Qualification::firstOrCreate(
    ['qualification_type' => 'AS_A_LEVEL'],
    ['qualification_name' => 'GCE AS and A Level', 'is_active' => true]
);

foreach ($spreadsheet->getSheetNames() as $sheetName) {
    if ($sheetName === 'Guide to Report' || $sheetName === 'Summary') {
        continue;
    }

    $sheet = $spreadsheet->getSheetByName($sheetName);
    $rows = $sheet->toArray(null, true, true, true);
    
    // Row 3 has component names, Row 4 has headers
    $row3 = $rows[3] ?? [];
    $row4 = $rows[4] ?? [];
    
    // Find component columns
    $componentsData = []; // colLetter => componentCode
    foreach ($row3 as $col => $val) {
        if ($val && str_contains($val, 'Component')) {
            $compCode = trim(str_replace('Component', '', $val));
            $componentsData[$col] = $compCode; // e.g., 'F' => '12'
        }
    }

    $currentSyllabusCode = null;

    // Process students (starting row 5)
    for ($i = 5; $i <= count($rows); $i++) {
        $row = $rows[$i];
        $syl = trim($row['A'] ?? '');
        
        if (!empty($syl) && is_numeric($syl)) {
            $currentSyllabusCode = $syl;
        }

        $candNo = trim($row['D'] ?? '');
        $candName = trim($row['E'] ?? '');
        
        // Only process rows that have a valid candidate number
        if (empty($candNo) || !is_numeric($candNo)) {
            continue;
        }

        if (!$currentSyllabusCode) {
            continue; // Skip if we haven't found a syllabus code yet
        }

        $syllabusCode = $currentSyllabusCode;
        
        $candidate = Candidate::firstOrCreate(
            ['school_id' => $school->id, 'candidate_number' => $candNo],
            ['candidate_name' => $candName, 'enrollment_date' => '2025-01-01']
        );

        $subject = Subject::firstOrCreate(
            ['subject_code' => $syllabusCode],
            [
                'subject_name' => 'Unknown Subject ' . $syllabusCode,
                'qualification_id' => $asLevel->id,
                'total_marks' => 100
            ]
        );

        $enrollment = CandidateEnrollment::firstOrCreate(
            ['candidate_id' => $candidate->id, 'series_id' => $series->id, 'subject_id' => $subject->id],
            ['qualification_id' => $subject->qualification_id, 'enrollment_status' => 'enrolled', 'enrolled_date' => '2025-01-01']
        );

        $subjectResult = SubjectResult::firstOrCreate(
            ['enrollment_id' => $enrollment->id, 'subject_id' => $subject->id, 'series_id' => $series->id],
            [
                'grade' => $row['O'] ?? 'U', // Syllabus grade
                'pum' => (!empty($row['N']) ? $row['N'] : 0),    // Default to 0 as PUM placeholder if not available
                'uploaded_by' => $admin ? $admin->id : null,
                'status' => 'complete'
            ]
        );
        
        // Ensure subjectResult status allows component marks
        if ($subjectResult->status === 'pending_components') {
            $subjectResult->status = 'component_marks_added';
            $subjectResult->save();
        }

        foreach ($componentsData as $col => $compCode) {
            $rawMark = $row[$col];
            if ($rawMark === null || trim($rawMark) === '') {
                continue;
            }

            $component = Component::firstOrCreate(
                ['subject_id' => $subject->id, 'component_code' => $compCode],
                [
                    'component_name' => 'Component ' . $compCode,
                    'component_type' => 'paper',
                    'total_marks' => 100 // Default placeholder
                ]
            );

            ComponentMarks::updateOrCreate(
                ['subject_result_id' => $subjectResult->id, 'component_id' => $component->id],
                [
                    'enrollment_id' => $enrollment->id,
                    'obtained_marks' => (float) $rawMark,
                    'total_marks' => 100, // Default placeholder
                    'percentage' => (float) $rawMark, // Placeholder
                    'uploaded_by' => $admin ? $admin->id : null
                ]
            );
        }
    }
}

echo "Component marks imported successfully!\n";
