<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use App\Models\ComponentMarks;
use App\Models\Candidate;
use App\Models\Component;
use App\Models\CandidateEnrollment;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ExcelImportService
{
    protected MarkCalculationService $calculationService;

    public function __construct(MarkCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Import component marks from Excel file
     * Expected columns: candidate_number, candidate_name, subject_code, 
     *                   component_code, obtained_marks, total_marks
     */
    public function importComponentMarks(
        string $filePath,
        string $seriesId,
        string $schoolId,
        string $userId
    ): array {
        $file = Excel::toArray(new class {}, $filePath);
        $data = collect($file[0])->skip(1); // Skip header row

        $results = [
            'successful' => [],
            'failed' => [],
            'summary' => [
                'total_rows' => count($data),
                'processed' => 0,
                'failed_count' => 0,
            ]
        ];

        foreach ($data as $row) {
            try {
                // Skip completely empty rows
                if (empty($row[0]) && empty($row[2])) {
                    continue;
                }

                // Validate row
                $this->validateMarkRow($row);

                // Find or create enrollment
                $enrollment = $this->findOrCreateEnrollment(
                    (string)$row[0], // candidate_number
                    (string)$row[1], // candidate_name
                    $seriesId,
                    (string)$row[2], // subject_code
                    $schoolId
                );

                // Find component
                $component = Component::whereHas('subject', function ($q) use ($row) {
                    $q->where('subject_code', (string)$row[2]);
                })
                ->where('component_code', (string)$row[3])
                ->first();

                if (!$component) {
                    throw new \Exception("Component with code " . $row[3] . " not found for subject " . $row[2]);
                }

                // Store component marks
                $mark = ComponentMarks::updateOrCreate(
                    [
                        'enrollment_id' => $enrollment->id,
                        'component_id' => $component->id,
                    ],
                    [
                        'obtained_marks' => (float)$row[4],
                        'total_marks' => (int)$row[5],
                        'uploaded_by' => $userId,
                    ]
                );

                // Recalculate subject result
                $this->calculationService->calculateSubjectResult($enrollment);

                $results['successful'][] = [
                    'candidate' => $row[0] . ' - ' . $row[1],
                    'component' => $row[3],
                    'marks' => $row[4] . '/' . $row[5],
                ];
                $results['summary']['processed']++;

            } catch (\Exception $e) {
                $results['failed'][] = [
                    'row' => count($results['successful']) + count($results['failed']) + 2,
                    'candidate' => $row[0] ?? 'Unknown',
                    'error' => $e->getMessage(),
                ];
                $results['summary']['failed_count']++;
            }
        }

        return $results;
    }

    /**
     * Import Grade Thresholds
     * Expected columns: subject_code, grade, qualification_type, minimum_percentage, minimum_marks
     */
    public function importGradeThresholds(
        string $filePath,
        string $seriesId,
        string $userId
    ): array {
        $file = Excel::toArray(new class {}, $filePath);
        $data = collect($file[0])->skip(1); // Skip header row

        $results = [
            'successful' => [],
            'failed' => [],
            'summary' => [
                'total_rows' => count($data),
                'processed' => 0,
                'failed_count' => 0,
            ]
        ];

        foreach ($data as $row) {
            try {
                if (empty($row[0]) || empty($row[1])) {
                    continue;
                }

                $subject = \App\Models\Subject::where('subject_code', (string)$row[0])->first();
                if (!$subject) {
                    throw new \Exception("Subject code " . $row[0] . " not found");
                }

                \App\Models\GradeThreshold::updateOrCreate(
                    [
                        'series_id' => $seriesId,
                        'subject_id' => $subject->id,
                        'grade' => (string)$row[1],
                    ],
                    [
                        'qualification_type' => (string)$row[2],
                        'minimum_percentage' => (float)$row[3],
                        'minimum_marks' => isset($row[4]) ? (int)$row[4] : null,
                        'created_by' => $userId,
                    ]
                );

                $results['successful'][] = [
                    'subject' => $row[0],
                    'grade' => $row[1],
                    'threshold' => $row[3] . '%',
                ];
                $results['summary']['processed']++;

            } catch (\Exception $e) {
                $results['failed'][] = [
                    'row' => count($results['successful']) + count($results['failed']) + 2,
                    'subject' => $row[0] ?? 'Unknown',
                    'error' => $e->getMessage(),
                ];
                $results['summary']['failed_count']++;
            }
        }

        return $results;
    }

    /**
     * Import Candidates
     * Expected columns: candidate_number, candidate_name, date_of_birth, gender
     */
    public function importCandidates(
        string $filePath,
        string $schoolId
    ): array {
        $file = Excel::toArray(new class {}, $filePath);
        $data = collect($file[0])->skip(1); // Skip header row

        $results = [
            'successful' => [],
            'failed' => [],
            'summary' => [
                'total_rows' => count($data),
                'processed' => 0,
                'failed_count' => 0,
            ]
        ];

        foreach ($data as $row) {
            try {
                if (empty($row[0]) || empty($row[1])) {
                    continue;
                }

                $dob = null;
                if (!empty($row[2])) {
                    try {
                        $dob = Carbon::parse($row[2])->toDateString();
                    } catch (\Exception $ex) {
                        $dob = null;
                    }
                }

                $candidate = Candidate::findOrCreateByNameAndNumber(
                    $schoolId,
                    (string)$row[0],
                    (string)$row[1],
                    [
                        'date_of_birth' => $dob,
                        'gender' => !empty($row[3]) ? strtoupper(substr((string)$row[3], 0, 1)) : null,
                    ]
                );

                $results['successful'][] = [
                    'number' => $row[0],
                    'name' => $row[1],
                ];
                $results['summary']['processed']++;

            } catch (\Exception $e) {
                $results['failed'][] = [
                    'row' => count($results['successful']) + count($results['failed']) + 2,
                    'number' => $row[0] ?? 'Unknown',
                    'error' => $e->getMessage(),
                ];
                $results['summary']['failed_count']++;
            }
        }

        return $results;
    }

    /**
     * Validate mark row data
     */
    private function validateMarkRow(array $row): void
    {
        if (count($row) < 6) {
            throw new \Exception("Row does not have required columns (expected 6 columns)");
        }

        if (!is_numeric($row[4]) || !is_numeric($row[5])) {
            throw new \Exception("Marks must be numeric values");
        }

        if ($row[4] > $row[5] || $row[4] < 0) {
            throw new \Exception("Obtained marks cannot exceed total marks or be negative");
        }
    }

    /**
     * Find or create candidate enrollment
     */
    private function findOrCreateEnrollment(
        string $candidateNumber,
        string $candidateName,
        string $seriesId,
        string $subjectCode,
        string $schoolId
    ): CandidateEnrollment {
        $candidate = Candidate::findOrCreateByNameAndNumber($schoolId, $candidateNumber, $candidateName);

        $subject = \App\Models\Subject::where('subject_code', $subjectCode)->first();
        if (!$subject) {
            throw new \Exception("Subject with code " . $subjectCode . " not found");
        }

        return CandidateEnrollment::firstOrCreate(
            [
                'candidate_id' => $candidate->id,
                'series_id' => $seriesId,
                'subject_id' => $subject->id,
            ],
            [
                'qualification_id' => $subject->qualification_id,
                'enrolled_date' => now()->toDateString(),
                'enrollment_status' => 'enrolled',
            ]
        );
    }
}
