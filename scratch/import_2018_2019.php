<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cbse\CbseAcademicYear;
use App\Models\Cbse\CbseQualification;
use App\Models\Cbse\CbseSubject;
use App\Models\Cbse\CbseStudent;
use App\Models\Cbse\CbseResult;

// 1. Create Academic Year
$academicYear = CbseAcademicYear::firstOrCreate(
    ['name' => '2018-2019'],
    [
        'start_date' => '2018-06-01',
        'end_date' => '2019-05-31',
        'is_active' => true
    ]
);

// 2. Retrieve Qualification
$qualification = CbseQualification::where('qualification_type', 'CLASS_10')->first();
if (!$qualification) {
    $qualification = CbseQualification::create([
        'qualification_type' => 'CLASS_10',
        'qualification_name' => 'Secondary (Class 10)',
        'board_code' => '241',
        'description' => 'CBSE Secondary School Certificate',
        'is_active' => true
    ]);
}

// 3. Define and Create Subjects
$subjectsMapping = [
    'english_comm' => ['code' => '101', 'name' => 'English Communicative', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
    'hindi_course_b' => ['code' => '085', 'name' => 'Hindi Course B', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
    'mathematics' => ['code' => '041', 'name' => 'Mathematics (Standard)', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
    'science' => ['code' => '086', 'name' => 'Science', 'theory' => 80, 'practical' => 20, 'type' => 'Practical'],
    'social_science' => ['code' => '087', 'name' => 'Social Science', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
    'foundation_of_it' => ['code' => '165', 'name' => 'Foundation of IT', 'theory' => 50, 'practical' => 50, 'type' => 'Practical'],
];

$subjects = [];
foreach ($subjectsMapping as $key => $preset) {
    $subject = CbseSubject::where('qualification_id', $qualification->id)
        ->where('subject_code', $preset['code'])
        ->first();
    if (!$subject) {
        $subject = CbseSubject::create([
            'qualification_id' => $qualification->id,
            'subject_code' => $preset['code'],
            'subject_name' => $preset['name'],
            'theory_marks' => $preset['theory'],
            'practical_marks' => $preset['practical'],
            'practical_type' => $preset['type'],
            'passing_percentage' => 33.00,
            'theory_passing_marks' => round($preset['theory'] * 0.33, 2),
            'is_active' => true
        ]);
    }
    $subjects[$key] = $subject;
}

// 4. Student Data from Image
$studentsData = [
    [
        'roll' => '1178294', 'name' => 'DARSHAN BAFNA', 'gender' => 'M', 'status' => 'PASS',
        'scores' => ['english_comm' => [64, 'D1'], 'hindi_course_b' => [61, 'D1'], 'mathematics' => [67, 'B2'], 'science' => [51, 'C2'], 'social_science' => [55, 'D1'], 'foundation_of_it' => [73, 'D1']]
    ],
    [
        'roll' => '1178292', 'name' => 'DHANANJAI BHANSALI', 'gender' => 'M', 'status' => 'PASS',
        'scores' => ['english_comm' => [84, 'B1'], 'hindi_course_b' => [88, 'B1'], 'mathematics' => [66, 'B2'], 'science' => [67, 'B2'], 'social_science' => [78, 'B2'], 'foundation_of_it' => [80, 'C1']]
    ],
    [
        'roll' => '1178301', 'name' => 'GAGAN BISHNOI', 'gender' => 'M', 'status' => 'PASS',
        'scores' => ['english_comm' => [84, 'B1'], 'hindi_course_b' => [90, 'A2'], 'mathematics' => [69, 'B2'], 'science' => [80, 'B1'], 'social_science' => [93, 'A2'], 'foundation_of_it' => [92, 'A2']]
    ],
    [
        'roll' => '1178290', 'name' => 'GURANGAD SINGH KHANGURA', 'gender' => 'M', 'status' => 'PASS',
        'scores' => ['english_comm' => [93, 'A1'], 'hindi_course_b' => [90, 'A2'], 'mathematics' => [54, 'C1'], 'science' => [56, 'C1'], 'social_science' => [95, 'A1'], 'foundation_of_it' => [94, 'A2']]
    ],
    [
        'roll' => '1178296', 'name' => 'KRITIKA BISHNOI', 'gender' => 'F', 'status' => 'PASS',
        'scores' => ['english_comm' => [93, 'A1'], 'hindi_course_b' => [96, 'A1'], 'mathematics' => [73, 'B1'], 'science' => [85, 'A2'], 'social_science' => [95, 'A1'], 'foundation_of_it' => [97, 'A1']]
    ],
    [
        'roll' => '1178291', 'name' => 'KUMARI JYOTI', 'gender' => 'F', 'status' => 'PASS',
        'scores' => ['english_comm' => [80, 'B2'], 'hindi_course_b' => [93, 'A2'], 'mathematics' => [65, 'B2'], 'science' => [76, 'B1'], 'social_science' => [74, 'B2'], 'foundation_of_it' => [92, 'A2']]
    ],
    [
        'roll' => '1178293', 'name' => 'MOHIT PUNAR', 'gender' => 'M', 'status' => 'PASS',
        'scores' => ['english_comm' => [94, 'A1'], 'hindi_course_b' => [92, 'A2'], 'mathematics' => [83, 'A2'], 'science' => [78, 'B1'], 'social_science' => [97, 'A1'], 'foundation_of_it' => [98, 'A1']]
    ],
    [
        'roll' => '1178304', 'name' => 'NAVDEEP SINGH', 'gender' => 'M', 'status' => 'PASS',
        'scores' => ['english_comm' => [78, 'C1'], 'hindi_course_b' => [73, 'C2'], 'mathematics' => [69, 'B2'], 'science' => [52, 'C2'], 'social_science' => [76, 'B2'], 'foundation_of_it' => [95, 'A2']]
    ],
    [
        'roll' => '1178295', 'name' => 'RENU MEWARA', 'gender' => 'F', 'status' => 'PASS',
        'scores' => ['english_comm' => [83, 'B2'], 'hindi_course_b' => [93, 'A2'], 'mathematics' => [71, 'B1'], 'science' => [76, 'B1'], 'social_science' => [90, 'A2'], 'foundation_of_it' => [93, 'A2']]
    ],
    [
        'roll' => '1178298', 'name' => 'SAMBHAV CHOPRA', 'gender' => 'M', 'status' => 'PASS',
        'scores' => ['english_comm' => [71, 'C2'], 'hindi_course_b' => [52, 'D2'], 'mathematics' => [44, 'C2'], 'science' => [33, 'D2'], 'social_science' => [58, 'C2'], 'foundation_of_it' => [74, 'C2']]
    ],
    [
        'roll' => '1178303', 'name' => 'SHAILENDRA KUMAR TAK', 'gender' => 'M', 'status' => 'PASS',
        'scores' => ['english_comm' => [73, 'C1'], 'hindi_course_b' => [82, 'B2'], 'mathematics' => [79, 'B1'], 'science' => [57, 'C1'], 'social_science' => [55, 'D1'], 'foundation_of_it' => [93, 'A2']]
    ],
    [
        'roll' => '1178289', 'name' => 'SURBHI TAK', 'gender' => 'F', 'status' => 'COMP',
        'scores' => ['english_comm' => [52, 'D2'], 'hindi_course_b' => [52, 'D2'], 'mathematics' => [33, 'D2'], 'science' => [22, 'E'], 'social_science' => [19, 'E'], 'foundation_of_it' => [62, 'D2']]
    ],
    [
        'roll' => '1178302', 'name' => 'TANMAY MATTAD', 'gender' => 'M', 'status' => 'PASS',
        'scores' => ['english_comm' => [66, 'C2'], 'hindi_course_b' => [75, 'C2'], 'mathematics' => [41, 'D1'], 'science' => [33, 'D2'], 'social_science' => [51, 'D1'], 'foundation_of_it' => [68, 'D1']]
    ],
    [
        'roll' => '1178300', 'name' => 'VARUN MANDA', 'gender' => 'M', 'status' => 'PASS',
        'scores' => ['english_comm' => [89, 'A2'], 'hindi_course_b' => [84, 'B2'], 'mathematics' => [76, 'B1'], 'science' => [88, 'A2'], 'social_science' => [95, 'A1'], 'foundation_of_it' => [96, 'A1']]
    ],
    [
        'roll' => '1178297', 'name' => 'YASHIKA KACHHAWAHA', 'gender' => 'F', 'status' => 'PASS',
        'scores' => ['english_comm' => [68, 'C2'], 'hindi_course_b' => [75, 'C2'], 'mathematics' => [48, 'C2'], 'science' => [41, 'D1'], 'social_science' => [62, 'C2'], 'foundation_of_it' => [76, 'C2']]
    ],
    [
        'roll' => '1178299', 'name' => 'YASHVARDHAN GARG', 'gender' => 'M', 'status' => 'PASS',
        'scores' => ['english_comm' => [63, 'D1'], 'hindi_course_b' => [84, 'B2'], 'mathematics' => [56, 'C1'], 'science' => [45, 'C2'], 'social_science' => [49, 'D1'], 'foundation_of_it' => [78, 'C2']]
    ]
];

$successCount = 0;
foreach ($studentsData as $data) {
    $roll = $data['roll'];
    
    $student = CbseStudent::updateOrCreate(
        ['admission_number' => 'CBSE-' . $roll],
        [
            'student_name' => $data['name'],
            'gender' => $data['gender'],
            'qualification_type' => 'CLASS_10',
            'status' => 'active'
        ]
    );

    foreach ($data['scores'] as $subKey => $score) {
        $subject = $subjects[$subKey];
        $totalObtained = (float)$score[0];
        $grade = $score[1];
        
        $totalMax = $subject->theory_marks + $subject->practical_marks;
        $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
        
        // Pass/Fail calculations
        $isPassed = ($grade !== 'E' && $grade !== 'F' && $percentage >= 33.00);
        $isCompartment = ($data['status'] === 'COMP' || !$isPassed) && $percentage >= 25.00;

        CbseResult::updateOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_year_id' => $academicYear->id,
            ],
            [
                'qualification_id' => $qualification->id,
                'exam_year' => 2019,
                'roll_number' => $roll,
                'theory_obtained' => null,
                'practical_obtained' => null,
                'total_obtained' => $totalObtained,
                'total_marks' => $totalMax,
                'percentage' => $percentage,
                'grade' => $grade,
                'is_passed' => $isPassed,
                'is_absent' => false,
                'is_compartment' => $isCompartment
            ]
        );
    }
    $successCount++;
}

echo "Successfully imported {$successCount} students for academic year 2018-2019!\n";
