<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Component;
use App\Models\Qualification;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Load Qualifications for quick lookup
        $qualifications = [
            'IGCSE' => Qualification::where('qualification_type', 'IGCSE')->first(),
            'AS_A_LEVEL' => Qualification::where('qualification_type', 'AS_A_LEVEL')->first(),
        ];

        // 2. Parse IGCSE file
        $this->parseIgcseFile(base_path('Cambridge_IGCSE_2026.xlsx'), $qualifications['IGCSE']);

        // 3. Parse AS & A Level file
        $this->parseAsALevelFile(base_path('Updated_Cambridge_AS_A_Level_2026.xlsx'), $qualifications['AS_A_LEVEL']);
    }

    private function parseIgcseFile(string $path, ?Qualification $qualification): void
    {
        if (!$qualification || !file_exists($path)) {
            return;
        }

        $spreadsheet = IOFactory::load($path);
        $sheetNames = $spreadsheet->getSheetNames();

        foreach ($sheetNames as $index => $sheetName) {
            if ($sheetName === 'Verification' || $sheetName === 'Sheet1') {
                continue;
            }

            // Extract subject name and code: e.g., "Mathematics (0580)"
            if (preg_match('/^(.*?)\s*\((\d+)\)$/', $sheetName, $matches)) {
                $subjectName = trim($matches[1]);
                $subjectCode = trim($matches[2]);

                $sheet = $spreadsheet->getSheet($index);
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                $rows = $sheet->rangeToArray("A1:{$highestCol}{$highestRow}", null, true, true, false);

                // Find total possible marks by summing component marks
                $totalSubjectMarks = 0;
                $componentsData = [];

                foreach ($rows as $rIndex => $row) {
                    if ($rIndex === 0) continue; // Skip header
                    if (empty($row[0])) continue;

                    $paperName = trim($row[0]);
                    $componentName = trim($row[1]);
                    $maxMarks = (int)$row[2];

                    $totalSubjectMarks += $maxMarks;
                    $componentsData[] = [
                        'paper_name' => $paperName,
                        'component_name' => $componentName,
                        'total_marks' => $maxMarks,
                    ];
                }

                // Create or update Subject
                $subject = Subject::updateOrCreate(
                    [
                        'qualification_id' => $qualification->id,
                        'subject_code' => $subjectCode
                    ],
                    [
                        'subject_name' => $subjectName,
                        'total_marks' => $totalSubjectMarks,
                        'passing_percentage' => 40.00,
                        'description' => "Cambridge IGCSE {$subjectName}"
                    ]
                );

                // Create or update Components
                foreach ($componentsData as $comp) {
                    $componentCode = $this->parseComponentCode($comp['paper_name']);
                    
                    Component::updateOrCreate(
                        [
                            'subject_id' => $subject->id,
                            'component_code' => $componentCode
                        ],
                        [
                            'component_name' => $comp['component_name'],
                            'component_type' => $this->inferComponentType($comp['component_name']),
                            'total_marks' => $comp['total_marks'],
                            'scaling_factor' => 1,
                            'is_mandatory' => true
                        ]
                    );
                }
            }
        }
    }

    private function parseAsALevelFile(string $path, ?Qualification $qualification): void
    {
        if (!$qualification || !file_exists($path)) {
            return;
        }

        $spreadsheet = IOFactory::load($path);
        $sheetNames = $spreadsheet->getSheetNames();

        foreach ($sheetNames as $index => $sheetName) {
            if ($sheetName === 'Verification' || $sheetName === 'Sheet1') {
                continue;
            }

            // Extract subject name and code: e.g., "Mathematics (9709)"
            if (preg_match('/^(.*?)\s*\((\d+)\)$/', $sheetName, $matches)) {
                $subjectName = trim($matches[1]);
                $subjectCode = trim($matches[2]);

                $sheet = $spreadsheet->getSheet($index);
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                $rows = $sheet->rangeToArray("A1:{$highestCol}{$highestRow}", null, true, true, false);

                // Group components
                $components = [];

                foreach ($rows as $rIndex => $row) {
                    if ($rIndex === 0) continue; // Skip header
                    if (empty($row[0])) continue;

                    $qualTypeStr = trim($row[0]);
                    $paperName = trim($row[1]);
                    $componentName = trim($row[2]);
                    $maxMarks = (int)$row[3];

                    $components[] = [
                        'paper_name' => $paperName,
                        'component_name' => $componentName,
                        'total_marks' => $maxMarks,
                    ];
                }

                if (empty($components)) continue;

                // Sum marks
                $totalSubjectMarks = array_sum(array_column($components, 'total_marks'));

                // Create or update Subject
                $subject = Subject::updateOrCreate(
                    [
                        'qualification_id' => $qualification->id,
                        'subject_code' => $subjectCode
                    ],
                    [
                        'subject_name' => $subjectName,
                        'total_marks' => $totalSubjectMarks,
                        'passing_percentage' => 40.00,
                        'description' => "Cambridge GCE AS & A Level {$subjectName}"
                    ]
                );

                // Create or update Components
                foreach ($components as $comp) {
                    $componentCode = $this->parseComponentCode($comp['paper_name']);
                    
                    Component::updateOrCreate(
                        [
                            'subject_id' => $subject->id,
                            'component_code' => $componentCode
                        ],
                        [
                            'component_name' => $comp['component_name'],
                            'component_type' => $this->inferComponentType($comp['component_name']),
                            'total_marks' => $comp['total_marks'],
                            'scaling_factor' => 1,
                            'is_mandatory' => true
                        ]
                    );
                }
            }
        }
    }

    private function parseComponentCode(string $paperName): string
    {
        // e.g. "Paper 2" -> "P2"
        preg_match('/\d+/', $paperName, $matches);
        return isset($matches[0]) ? 'P' . $matches[0] : $paperName;
    }

    private function inferComponentType(string $name): string
    {
        $nameLower = strtolower($name);
        if (str_contains($nameLower, 'practical') || str_contains($nameLower, 'laboratory')) {
            return 'practical';
        }
        if (str_contains($nameLower, 'project')) {
            return 'project';
        }
        if (str_contains($nameLower, 'coursework')) {
            return 'coursework';
        }
        return 'paper';
    }
}
