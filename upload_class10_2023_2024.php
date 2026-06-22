<?php

use App\Models\Cbse\CbseAcademicYear;
use App\Models\Cbse\CbseQualification;
use App\Models\Cbse\CbseSubject;
use App\Models\Cbse\CbseStudent;
use App\Models\Cbse\CbseResult;

$year = CbseAcademicYear::firstOrCreate(
    ['name' => '2023-2024'],
    ['start_date' => '2023-04-01', 'end_date' => '2024-03-31', 'is_active' => true]
);

$class10 = CbseQualification::where('qualification_type', 'CLASS_10')->first();

if (!$class10) {
    die("Class 10 qualification not found");
}

$data = [
    ['roll' => '11198785', 'name' => 'ADITYA', '184' => 36, '085' => 36, '041' => null, '241' => 28, '086' => 33, '087' => 55, '402' => 51],
    ['roll' => '11198786', 'name' => 'ANIMESH KUMAR', '184' => 81, '085' => 78, '041' => 67, '241' => null, '086' => 78, '087' => 94, '402' => 89],
    ['roll' => '11198787', 'name' => 'CHITRANSH KANODIA', '184' => 82, '085' => 85, '041' => 70, '241' => null, '086' => 76, '087' => 92, '402' => 85],
    ['roll' => '11198788', 'name' => 'DAKSH BAFNA', '184' => 79, '085' => 81, '041' => null, '241' => 70, '086' => 59, '087' => 70, '402' => 82],
    ['roll' => '11198789', 'name' => 'DEVRUDRA SINGH', '184' => 67, '085' => 68, '041' => null, '241' => 60, '086' => 43, '087' => 84, '402' => 74],
    ['roll' => '11198790', 'name' => 'HARSHIKA POONIA', '184' => 73, '085' => 95, '041' => 72, '241' => null, '086' => 76, '087' => 96, '402' => 89],
    ['roll' => '11198791', 'name' => 'JAY KUMAR', '184' => 46, '085' => 55, '041' => null, '241' => 47, '086' => 41, '087' => 74, '402' => 73],
    ['roll' => '11198792', 'name' => 'MOHAMMED TOFIQ', '184' => 45, '085' => 60, '041' => null, '241' => 63, '086' => 34, '087' => 62, '402' => 56],
    ['roll' => '11198794', 'name' => 'SANGRAM SINGH OAD', '184' => 38, '085' => 44, '041' => null, '241' => 36, '086' => 40, '087' => 46, '402' => 53],
    ['roll' => '11198795', 'name' => 'SWASTIK PUROHIT', '184' => 76, '085' => 91, '041' => 59, '241' => null, '086' => 69, '087' => 91, '402' => 89],
    ['roll' => '11198796', 'name' => 'VANIA MANTRI', '184' => 85, '085' => 95, '041' => 81, '241' => null, '086' => 92, '087' => 96, '402' => 95],
    ['roll' => '11198797', 'name' => 'VEDANT OZA', '184' => 68, '085' => 77, '041' => null, '241' => 59, '086' => 48, '087' => 70, '402' => 78],
    ['roll' => '11198798', 'name' => 'VARUNPAL SINGH BHATI', '184' => 93, '085' => 94, '041' => 90, '241' => null, '086' => 92, '087' => 96, '402' => 96],
];

// Ensure subjects exist
$subjectsMap = [
    '184' => 'English Language & Literature',
    '085' => 'Hindi Course B',
    '041' => 'Mathematics (Standard)',
    '241' => 'Mathematics (Basic)',
    '086' => 'Science',
    '087' => 'Social Science',
    '402' => 'Information Technology',
];

$subjectIds = [];
foreach ($subjectsMap as $code => $name) {
    $sub = CbseSubject::firstOrCreate(
        ['subject_code' => $code, 'qualification_id' => $class10->id],
        [
            'subject_name' => $name,
            'theory_marks' => 80,
            'practical_marks' => 20,
            'theory_passing_marks' => 26,
            'practical_passing_marks' => 6,
        ]
    );
    $subjectIds[$code] = $sub->id;
}

foreach ($data as $row) {
    $student = CbseStudent::firstOrCreate(
        ['admission_number' => $row['roll']],
        [
            'student_name' => $row['name'],
            'gender' => 'M', // Unknown, defaulting to M to pass constraint
            'qualification_type' => 'CLASS_10',
            'status' => 'active'
        ]
    );

    foreach (['184', '085', '041', '241', '086', '087', '402'] as $code) {
        if ($row[$code] !== null) {
            $totalObtained = $row[$code];
            // Compute percentage and grade (simple assumption: total_marks = 100)
            $percentage = $totalObtained; 
            $grade = CbseResult::computeGrade($percentage);
            $isPassed = $percentage >= 33;
            $isCompartment = !$isPassed && $percentage >= 25;

            CbseResult::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'subject_id' => $subjectIds[$code],
                    'academic_year_id' => $year->id,
                ],
                [
                    'qualification_id' => $class10->id,
                    'exam_year' => 2024, // 2023-2024 session -> 2024 exam
                    'roll_number' => $row['roll'],
                    'total_obtained' => $totalObtained,
                    'total_marks' => 100,
                    'percentage' => $percentage,
                    'grade' => $grade,
                    'is_passed' => $isPassed,
                    'is_absent' => false,
                    'is_compartment' => $isCompartment,
                ]
            );
        }
    }
}
echo "Imported successfully!\n";
