<?php

use App\Models\Cbse\CbseAcademicYear;
use App\Models\Cbse\CbseQualification;
use App\Models\Cbse\CbseSubject;
use App\Models\Cbse\CbseStudent;
use App\Models\Cbse\CbseResult;

$year = CbseAcademicYear::firstOrCreate(
    ['name' => '2022-2023'],
    ['start_date' => '2022-04-01', 'end_date' => '2023-03-31', 'is_active' => true]
);

$class10 = CbseQualification::where('qualification_type', 'CLASS_10')->first();

if (!$class10) {
    die("Class 10 qualification not found");
}

$data = [
    ['roll' => '11192439', 'name' => 'AKSHAY', '184' => 56, '085' => 73, '041' => null, '241' => 53, '086' => 53, '087' => 86, '402' => 72],
    ['roll' => '11192440', 'name' => 'ASHOK PATEL', '184' => 98, '085' => 96, '041' => 75, '241' => null, '086' => 95, '087' => 96, '402' => 84],
    ['roll' => '11192441', 'name' => 'BHARAT SONI', '184' => 81, '085' => 90, '041' => 79, '241' => null, '086' => 97, '087' => 88, '402' => 78],
    ['roll' => '11192442', 'name' => 'BHAVYA MEWARA', '184' => 62, '085' => 74, '041' => null, '241' => 56, '086' => 54, '087' => 80, '402' => 64],
    ['roll' => '11192443', 'name' => 'BHUVAN SINGH BHATI', '184' => 88, '085' => 89, '041' => 46, '241' => null, '086' => 63, '087' => 73, '402' => 83],
    ['roll' => '11192444', 'name' => 'DAKSH PRAKASH VISHNOI', '184' => 74, '085' => 64, '041' => null, '241' => 50, '086' => 43, '087' => 48, '402' => 71],
    ['roll' => '11192445', 'name' => 'DEV PRATAP SINGH CHAUHAN', '184' => 66, '085' => 84, '041' => 60, '241' => null, '086' => 55, '087' => 74, '402' => 69],
    ['roll' => '11192446', 'name' => 'HARSHIT KULHARI', '184' => 84, '085' => 87, '041' => null, '241' => 89, '086' => 95, '087' => 95, '402' => 82],
    ['roll' => '11192447', 'name' => 'HARSH VARDHAN SINGH', '184' => 82, '085' => 88, '041' => 55, '241' => null, '086' => 71, '087' => 75, '402' => 81],
    ['roll' => '11192448', 'name' => 'HITESH SINGH OAD', '184' => 43, '085' => 58, '041' => null, '241' => 46, '086' => 40, '087' => 49, '402' => 61],
    ['roll' => '11192449', 'name' => 'KARTIK PAREEK', '184' => 62, '085' => 90, '041' => 59, '241' => null, '086' => 60, '087' => 57, '402' => 64],
    ['roll' => '11192450', 'name' => 'KULDEEP BISHNOI', '184' => 75, '085' => 88, '041' => null, '241' => 65, '086' => 71, '087' => 67, '402' => 78],
    ['roll' => '11192451', 'name' => 'MANISH JAJRA', '184' => 72, '085' => 87, '041' => null, '241' => 52, '086' => 49, '087' => 66, '402' => 75],
    ['roll' => '11192452', 'name' => 'MAYANK CHOUDHARY', '184' => 88, '085' => 85, '041' => 53, '241' => null, '086' => 68, '087' => 62, '402' => 77],
    ['roll' => '11192453', 'name' => 'RAJVEER MEWARA', '184' => 59, '085' => 77, '041' => 56, '241' => null, '086' => 56, '087' => 64, '402' => 75],
    ['roll' => '11192454', 'name' => 'RITIKA NAIN', '184' => 77, '085' => 88, '041' => null, '241' => 71, '086' => 64, '087' => 74, '402' => 84],
    ['roll' => '11192455', 'name' => 'ROHIT MEWARA', '184' => 80, '085' => 90, '041' => 68, '241' => null, '086' => 80, '087' => 76, '402' => 87],
    ['roll' => '11192456', 'name' => 'SHIV RAJ SINGH INDA', '184' => 48, '085' => 50, '041' => null, '241' => 36, '086' => 35, '087' => 52, '402' => 56],
    ['roll' => '11192457', 'name' => 'SUBHASH BISHNOI', '184' => 91, '085' => 89, '041' => 86, '241' => null, '086' => 71, '087' => 73, '402' => 89],
    ['roll' => '11192458', 'name' => 'VIJAY SONI', '184' => 77, '085' => 83, '041' => null, '241' => 68, '086' => 61, '087' => 83, '402' => 70],
    ['roll' => '11192459', 'name' => 'UDAY GODARA', '184' => 69, '085' => 81, '041' => null, '241' => 59, '086' => 65, '087' => 69, '402' => 88],
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
            'gender' => 'M', // Assume M
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
                    'exam_year' => 2023, // 2022-2023 session -> 2023 exam
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
