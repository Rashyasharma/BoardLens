<?php

use App\Models\Cbse\CbseAcademicYear;
use App\Models\Cbse\CbseQualification;
use App\Models\Cbse\CbseSubject;
use App\Models\Cbse\CbseStudent;
use App\Models\Cbse\CbseResult;

$year = CbseAcademicYear::firstOrCreate(
    ['name' => '2024-2025'],
    ['start_date' => '2024-04-01', 'end_date' => '2025-03-31', 'is_active' => true]
);

$class10 = CbseQualification::where('qualification_type', 'CLASS_10')->first();

if (!$class10) {
    die("Class 10 qualification not found");
}

$data = [
    ['roll' => '11203697', 'name' => 'PANKAJ PAL SINGH BHATI', '184' => 91, '085' => 96, '041' => 96, '241' => null, '086' => 90, '087' => 90, '018' => null, '402' => 98],
    ['roll' => '11203704', 'name' => 'MANJU JAJRA', '184' => 91, '085' => 94, '041' => null, '241' => 90, '086' => 93, '087' => 95, '018' => null, '402' => 97],
    ['roll' => '11203696', 'name' => 'SOHAM AJMERA', '184' => 88, '085' => 85, '041' => 92, '241' => null, '086' => 92, '087' => 94, '018' => null, '402' => 98],
    ['roll' => '11203694', 'name' => 'KRISHANPAL', '184' => 90, '085' => 90, '041' => 95, '241' => null, '086' => 90, '087' => 87, '018' => null, '402' => 95],
    ['roll' => '11203692', 'name' => 'BHAGESH MAHESHWARI', '184' => 84, '085' => 91, '041' => 89, '241' => null, '086' => 89, '087' => 89, '018' => null, '402' => 91],
    ['roll' => '11202706', 'name' => 'KAVYA YOGENDRABHAI PATEL', '184' => 95, '085' => 86, '041' => null, '241' => 72, '086' => 85, '087' => 83, '018' => null, '402' => 96],
    ['roll' => '11203712', 'name' => 'TVISHA SHARMA', '184' => 98, '085' => null, '041' => null, '241' => 70, '086' => 75, '087' => 91, '018' => 86, '402' => 93],
    ['roll' => '11203703', 'name' => 'KUNJAL DHANADIA', '184' => 87, '085' => 86, '041' => null, '241' => 74, '086' => 76, '087' => 87, '018' => null, '402' => 96],
    ['roll' => '11203707', 'name' => 'OJASVI PARIHAR', '184' => 90, '085' => 80, '041' => null, '241' => 83, '086' => 82, '087' => 86, '018' => null, '402' => 90],
    ['roll' => '11203693', 'name' => 'HITESH CHOUDHARY', '184' => 87, '085' => 86, '041' => 83, '241' => null, '086' => 84, '087' => 81, '018' => null, '402' => 80],
    ['roll' => '11203710', 'name' => 'TEESHA MAKWANA', '184' => 88, '085' => 74, '041' => null, '241' => 90, '086' => 77, '087' => 72, '018' => null, '402' => 92],
    ['roll' => '11203711', 'name' => 'MOKSH MEHTA', '184' => 79, '085' => 76, '041' => null, '241' => 77, '086' => 70, '087' => 95, '018' => null, '402' => 88],
    ['roll' => '11203708', 'name' => 'PRINCE', '184' => 75, '085' => 92, '041' => null, '241' => 63, '086' => 66, '087' => 88, '018' => null, '402' => 85],
    ['roll' => '11203705', 'name' => 'JAIVARDHAN RANA', '184' => 85, '085' => 71, '041' => null, '241' => 52, '086' => 62, '087' => 92, '018' => null, '402' => 93],
    ['roll' => '11203700', 'name' => 'CHETNA JANGID', '184' => 85, '085' => 82, '041' => null, '241' => 59, '086' => 70, '087' => 66, '018' => null, '402' => 91],
    ['roll' => '11203699', 'name' => 'ABHINAB SRIVASTAVA', '184' => 84, '085' => 84, '041' => null, '241' => 67, '086' => 64, '087' => 76, '018' => null, '402' => 82],
    ['roll' => '11203709', 'name' => 'SUBHASH BISHNOI', '184' => 78, '085' => 84, '041' => null, '241' => 48, '086' => 55, '087' => 81, '018' => null, '402' => 84],
    ['roll' => '11203714', 'name' => 'MAANAS', '184' => 82, '085' => 70, '041' => null, '241' => 56, '086' => 55, '087' => 82, '018' => null, '402' => 91],
    ['roll' => '11203698', 'name' => 'KHUSHAL SINGH RAJPUROHIT', '184' => 79, '085' => 82, '041' => 55, '241' => null, '086' => 60, '087' => 67, '018' => null, '402' => 92],
    ['roll' => '11203695', 'name' => 'TRAPTI SOLANKI', '184' => 75, '085' => 74, '041' => 67, '241' => null, '086' => 74, '087' => 72, '018' => null, '402' => 81],
    ['roll' => '11203702', 'name' => 'GAUTAM', '184' => 63, '085' => 74, '041' => null, '241' => 35, '086' => 37, '087' => 63, '018' => null, '402' => 67],
    ['roll' => '11203713', 'name' => 'KAUSHAL PRAJAPAT', '184' => 66, '085' => 56, '041' => null, '241' => 45, '086' => 33, '087' => 53, '018' => null, '402' => 77],
    ['roll' => '11203701', 'name' => 'DIKSHA KHATRI', '184' => 55, '085' => 81, '041' => null, '241' => 48, '086' => 36, '087' => 46, '018' => null, '402' => 64],
    ['roll' => '11203715', 'name' => 'RONAK JANGID', '184' => 46, '085' => 46, '041' => null, '241' => 36, '086' => 40, '087' => 45, '018' => null, '402' => 76],
];

// Ensure subjects exist
$subjectsMap = [
    '184' => 'English Language & Literature',
    '085' => 'Hindi Course B',
    '041' => 'Mathematics (Standard)',
    '241' => 'Mathematics (Basic)',
    '086' => 'Science',
    '087' => 'Social Science',
    '018' => 'French',
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
            'gender' => 'M', // Default to M to pass constraint
            'qualification_type' => 'CLASS_10',
            'status' => 'active'
        ]
    );

    foreach (['184', '085', '041', '241', '086', '087', '018', '402'] as $code) {
        if (isset($row[$code]) && $row[$code] !== null) {
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
                    'exam_year' => 2025, // 2024-2025 session -> 2025 exam
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
