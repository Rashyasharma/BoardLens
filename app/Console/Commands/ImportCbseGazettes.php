<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cbse\CbseAcademicYear;
use App\Models\Cbse\CbseQualification;
use App\Models\Cbse\CbseSubject;
use App\Models\Cbse\CbseStudent;
use App\Models\Cbse\CbseResult;
use Illuminate\Support\Str;

class ImportCbseGazettes extends Command
{
    protected $signature = 'cbse:import-gazettes';
    protected $description = 'Import CBSE results from the Downloads folder on the Desktop';

    // Preset mappings for common subjects to make database entry cleaner
    protected array $subjectPresets = [
        'CLASS_10' => [
            '184' => ['name' => 'English Language & Literature', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
            '085' => ['name' => 'Hindi Course B', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
            '041' => ['name' => 'Mathematics (Standard)', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
            '241' => ['name' => 'Mathematics (Basic)', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
            '086' => ['name' => 'Science', 'theory' => 80, 'practical' => 20, 'type' => 'Practical'],
            '087' => ['name' => 'Social Science', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
            '402' => ['name' => 'Information Technology', 'theory' => 50, 'practical' => 50, 'type' => 'Practical'],
            '417' => ['name' => 'Artificial Intelligence', 'theory' => 50, 'practical' => 50, 'type' => 'Practical'],
            '018' => ['name' => 'French', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
            '122' => ['name' => 'Sanskrit', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
        ],
        'CLASS_12' => [
            '301' => ['name' => 'English Core', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
            '302' => ['name' => 'Hindi Core', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
            '001' => ['name' => 'English Elective', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
            '002' => ['name' => 'Hindi Elective', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
            '037' => ['name' => 'Psychology', 'theory' => 70, 'practical' => 30, 'type' => 'Practical'],
            '052' => ['name' => 'Applied Art – Commercial Art', 'theory' => 30, 'practical' => 70, 'type' => 'Practical'],
            '041' => ['name' => 'Mathematics', 'theory' => 80, 'practical' => 20, 'type' => 'Internal Assessment'],
            '042' => ['name' => 'Physics', 'theory' => 70, 'practical' => 30, 'type' => 'Practical'],
            '043' => ['name' => 'Chemistry', 'theory' => 70, 'practical' => 30, 'type' => 'Practical'],
            '044' => ['name' => 'Biology', 'theory' => 70, 'practical' => 30, 'type' => 'Practical'],
            '045' => ['name' => 'Biology', 'theory' => 70, 'practical' => 30, 'type' => 'Practical'],
            '048' => ['name' => 'Physical Education', 'theory' => 70, 'practical' => 30, 'type' => 'Practical'],
            '083' => ['name' => 'Computer Science', 'theory' => 70, 'practical' => 30, 'type' => 'Practical'],
            '065' => ['name' => 'Informatics Practices', 'theory' => 70, 'practical' => 30, 'type' => 'Practical'],
            '030' => ['name' => 'Accountancy', 'theory' => 80, 'practical' => 20, 'type' => 'Project'],
            '054' => ['name' => 'Business Studies', 'theory' => 80, 'practical' => 20, 'type' => 'Project'],
            '055' => ['name' => 'Economics', 'theory' => 80, 'practical' => 20, 'type' => 'Project'],
            '027' => ['name' => 'History', 'theory' => 80, 'practical' => 20, 'type' => 'Project'],
            '028' => ['name' => 'Political Science', 'theory' => 80, 'practical' => 20, 'type' => 'Project'],
            '029' => ['name' => 'Geography', 'theory' => 70, 'practical' => 30, 'type' => 'Practical'],
        ]
    ];

    public function handle()
    {
        $downloadsDir = 'C:\\Users\\HP11\\Desktop\\CBSE Result Downloads';
        if (!is_dir($downloadsDir)) {
            $this->error("Downloads folder not found at {$downloadsDir}");
            return 1;
        }

        // Find all txt files matching 11140*.txt or 11140*.TXT
        $files = array_merge(
            glob($downloadsDir . DIRECTORY_SEPARATOR . '11140*.TXT'),
            glob($downloadsDir . DIRECTORY_SEPARATOR . '11140*.txt')
        );

        if (empty($files)) {
            $this->warn("No CBSE gazette files found matching 11140* in {$downloadsDir}");
            return 0;
        }

        $this->info("Found " . count($files) . " files to process.");

        foreach ($files as $file) {
            $this->processFile($file);
        }

        $this->info("Import completed successfully.");
        return 0;
    }

    protected function processFile(string $path)
    {
        $filename = basename($path);
        $this->info("Processing: {$filename}");

        $content = file_get_contents($path);
        if ($content === false) {
            $this->error("Failed to read: {$filename}");
            return;
        }

        // Extract year and exam name
        // C.B.S.E. - SECONDARY SCHOOL EXAMINATION (MAIN)-2020
        // C.B.S.E. - SENIOR SCHOOL CERTIFICATE EXAMINATION (MAIN)-2026
        preg_match('/EXAMINATION\s*(?:\([^)]*\))?-(\d{4})/', $content, $yearMatch);
        preg_match('/C\.B\.S\.E\.\s*-\s*([^-\n\r]+)/', $content, $examMatch);

        $examYear = isset($yearMatch[1]) ? (int)$yearMatch[1] : null;
        $examName = isset($examMatch[1]) ? trim($examMatch[1]) : '';

        if (!$examYear) {
            $this->warn("Could not determine exam year for {$filename}. Skipping.");
            return;
        }

        $qualType = (str_contains(strtoupper($examName), 'SECONDARY')) ? 'CLASS_10' : 'CLASS_12';

        $this->comment("  Detected Year: {$examYear} | Qualification: {$qualType}");

        // Academic Year Y maps to (Y-1)-Y
        $ayName = ($examYear - 1) . '-' . $examYear;
        $academicYear = CbseAcademicYear::firstOrCreate(
            ['name' => $ayName],
            [
                'start_date' => ($examYear - 1) . '-06-01',
                'end_date' => $examYear . '-05-31',
                'is_active' => true
            ]
        );

        $qualification = CbseQualification::firstOrCreate(
            ['qualification_type' => $qualType],
            [
                'qualification_name' => $qualType === 'CLASS_10' ? 'Secondary (Class 10)' : 'Senior Secondary (Class 12)',
                'board_code' => $qualType === 'CLASS_10' ? '241' : '301',
                'description' => $qualType === 'CLASS_10' ? 'CBSE Secondary School Certificate' : 'CBSE Senior Secondary Certificate',
                'is_active' => true
            ]
        );

        // Process lines
        $lines = explode("\n", $content);
        $studentCount = 0;

        for ($i = 0; $i < count($lines); $i++) {
            $line = $lines[$i];
            
            // Check if line starts with 8-digit roll number (with optional candidate category letter like 'C')
            if (preg_match('/^\s*(\d{8})\s+(?:([A-Z])\s+)?([MFO])\s+(.+)$/', $line, $matches)) {
                $roll = $matches[1];
                $category = $matches[2] ?? null;
                $gender = $matches[3];
                $rest = $matches[4];

                $parts = preg_split('/\s{2,}/', trim($rest));
                if (count($parts) < 1) continue;

                $name = $parts[0];

                // Subject codes are 3-digit numbers in the rest of the parts
                $subjects = [];
                $resultStatus = 'PASS';
                for ($p = 1; $p < count($parts); $p++) {
                    $token = trim($parts[$p]);
                    if (preg_match('/^\d{3}$/', $token)) {
                        $subjects[] = $token;
                    } elseif (in_array($token, ['PASS', 'COMP', 'ABST', 'FAIL', 'COMPTT.', 'ER'])) {
                        $resultStatus = $token;
                    }
                }

                // Look for marks line
                $marksLine = '';
                $j = $i + 1;
                while ($j < count($lines)) {
                    $nextLine = $lines[$j];
                    if (trim($nextLine) === '') {
                        $j++;
                        continue;
                    }
                    if (preg_match('/^\s*(\d{8})\s+/', $nextLine)) {
                        break;
                    }
                    if (str_contains($nextLine, 'TOTAL') || str_contains($nextLine, 'SCHOOL')) {
                        break;
                    }
                    $marksLine = $nextLine;
                    break;
                }

                $subjectResults = [];
                if ($marksLine && $resultStatus !== 'ABST') {
                    $mTokens = preg_split('/\s+/', trim($marksLine));
                    $mTokens = array_filter($mTokens, fn($t) => trim($t) !== '');
                    $mTokens = array_values($mTokens);

                    // Check if marks line has grades or only marks
                    if (count($mTokens) >= count($subjects) * 2) {
                        // Pairs of marks and grades
                        for ($s = 0; $s < count($subjects); $s++) {
                            $mVal = $mTokens[$s * 2] ?? null;
                            $gVal = $mTokens[$s * 2 + 1] ?? null;
                            $subjectResults[] = [
                                'code' => $subjects[$s],
                                'marks' => $mVal,
                                'grade' => $gVal
                            ];
                        }
                    } else {
                        // Marks only
                        for ($s = 0; $s < count($subjects); $s++) {
                            $mVal = $mTokens[$s] ?? null;
                            $subjectResults[] = [
                                'code' => $subjects[$s],
                                'marks' => $mVal,
                                'grade' => null
                            ];
                        }
                    }
                } else {
                    // Absent or no marks line
                    for ($s = 0; $s < count($subjects); $s++) {
                        $subjectResults[] = [
                            'code' => $subjects[$s],
                            'marks' => 'ABST',
                            'grade' => 'F'
                        ];
                    }
                }

                // Create or update Student
                $student = CbseStudent::updateOrCreate(
                    ['admission_number' => 'CBSE-' . $roll],
                    [
                        'student_name' => $name,
                        'gender' => $gender,
                        'qualification_type' => $qualType,
                        'status' => 'active'
                    ]
                );

                // Save results
                foreach ($subjectResults as $resData) {
                    $subCode = $resData['code'];
                    $rawMarks = $resData['marks'];
                    $rawGrade = $resData['grade'];

                    // Retrieve or create Subject
                    $subject = CbseSubject::where('qualification_id', $qualification->id)
                        ->where('subject_code', $subCode)
                        ->first();

                    if (!$subject) {
                        $preset = $this->subjectPresets[$qualType][$subCode] ?? null;
                        $subject = CbseSubject::create([
                            'qualification_id' => $qualification->id,
                            'subject_code' => $subCode,
                            'subject_name' => $preset ? $preset['name'] : 'Subject ' . $subCode,
                            'theory_marks' => $preset ? $preset['theory'] : 80,
                            'practical_marks' => $preset ? $preset['practical'] : 20,
                            'practical_type' => $preset ? $preset['type'] : 'Internal Assessment',
                            'passing_percentage' => 33.00,
                            'theory_passing_marks' => $preset ? round($preset['theory'] * 0.33, 2) : 26.40,
                            'is_active' => true
                        ]);
                    }

                    $totalMax = $subject->theory_marks + $subject->practical_marks;
                    $isAbsent = ($rawMarks === 'ABST' || $resultStatus === 'ABST');

                    if ($isAbsent) {
                        $theoryObtained = null;
                        $practicalObtained = null;
                        $totalObtained = null;
                        $percentage = null;
                        $grade = 'F';
                        $isPassed = false;
                        $isCompartment = false;
                    } else {
                        $totalObtained = (float)$rawMarks;
                        $theoryObtained = null; // Stored out of 100 for now, bifurcation uploaded later
                        $practicalObtained = null;
                        
                        $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
                        $grade = $rawGrade ?? CbseResult::computeGrade($percentage);
                        $isPassed = $percentage >= 33.00;
                        $isCompartment = !$isPassed && $percentage >= 25.00;
                    }

                    CbseResult::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'academic_year_id' => $academicYear->id,
                        ],
                        [
                            'qualification_id' => $qualification->id,
                            'exam_year' => $examYear,
                            'roll_number' => $roll,
                            'theory_obtained' => $theoryObtained,
                            'practical_obtained' => $practicalObtained,
                            'total_obtained' => $totalObtained,
                            'total_marks' => $totalMax,
                            'percentage' => $percentage,
                            'grade' => $grade,
                            'is_passed' => $isPassed,
                            'is_absent' => $isAbsent,
                            'is_compartment' => $isCompartment
                        ]
                    );
                }

                $studentCount++;
                $i = $j - 1;
            }
        }

        $this->info("  Imported {$studentCount} student results.");
    }
}
