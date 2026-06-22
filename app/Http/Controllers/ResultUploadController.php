<?php

namespace App\Http\Controllers;

use App\Models\Qualification;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use App\Http\Requests\UploadResultRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ResultUploadController extends Controller
{
    /**
     * Show upload result page
     */
    public function showUploadResult()
    {
        $qualifications = Qualification::all();

        return view('uploads.result', [
            'qualifications' => $qualifications,
        ]);
    }

    /**
     * Get years for qualification (AJAX) - fixed range 2018-2026
     */
    public function getYears(Request $request)
    {
        $years = range(2030, 2018);

        return response()->json(['years' => $years]);
    }

    /**
     * Get months for qualification and year (AJAX)
     */
    public function getMonths(Request $request)
    {
        $months = ExamSeries::getMonthsForQualificationYear($request->qualification_id, $request->year);

        return response()->json(['months' => $months]);
    }

    /**
     * Get series ID for qualification, year, month
     */
    public function getSeries(Request $request)
    {
        $query = ExamSeries::where('year', $request->year);

        if ($request->filled('month')) {
            $series = $query->where('month', $request->month)->first();
            if (!$series) {
                return response()->json(['error' => 'Series not found'], 404);
            }
            return response()->json([
                'series_id' => $series->id,
                'series_name' => $series->series_name,
            ]);
        }

        // If no month is specified, return all series for that year
        $allSeries = $query->get()->map(fn($s) => [
            'id' => $s->id,
            'month' => $s->month,
            'series_name' => $s->series_name,
        ]);

        return response()->json($allSeries);
    }

    /**
     * Get subjects for qualification (AJAX)
     */
    public function getSubjects(Request $request, $qualification_id = null)
    {
        $qualId = $qualification_id ?: $request->qualification_id;
        $subjects = Subject::where('qualification_id', $qualId)
            ->get(['id', 'subject_code', 'subject_name']);

        return response()->json($subjects);
    }

    /**
     * Upload result for students
     */
    public function storeUploadResult(UploadResultRequest $request)
    {
        try {
            $qualificationId = $request->qualification_id;
            $seriesId = $request->series_id;
            $subjectId = $request->subject_id;
            $schoolId = auth()->user()->school_id;

            $file = $request->file('results_file');
            $fileData = $this->readUploadFile($file);

            $successful = [];
            $failed = [];

            DB::beginTransaction();

            foreach ($fileData as $index => $row) {
                try {
                    $this->validateResultRow($row);

                    $candNo = trim($row[0]);
                    $candName = trim($row[1]);
                    $grade = trim($row[2]);
                    $pum = (float)$row[3];

                    // Use centralized Candidate lookup/creation helper to prevent duplicate profiles
                    $candidate = Candidate::findOrCreateByNameAndNumber($schoolId, $candNo, $candName);

                    // We no longer create general (subject_id = null) enrollments.

                    // Ensure subject-specific enrollment exists
                    $subjectEnrollment = CandidateEnrollment::firstOrCreate(
                        [
                            'candidate_id' => $candidate->id,
                            'series_id' => $seriesId,
                            'qualification_id' => $qualificationId,
                            'subject_id' => $subjectId,
                        ],
                        [
                            'enrolled_date' => now()->toDateString(),
                            'enrollment_status' => 'enrolled',
                        ]
                    );

                    // Create or update subject result under subject-specific enrollment
                    $result = SubjectResult::updateOrCreate(
                        [
                            'enrollment_id' => $subjectEnrollment->id,
                            'subject_id' => $subjectId,
                            'series_id' => $seriesId,
                        ],
                        [
                            'grade' => $grade,
                            'pum' => $pum,
                            'status' => 'pending_components',
                            'result_uploaded_at' => now(),
                            'uploaded_by' => auth()->id(),
                        ]
                    );

                    $successful[] = [
                        'candidate_number' => $candNo,
                        'candidate_name' => $candName,
                        'grade' => $grade,
                        'pum' => $pum,
                        'result_id' => $result->id,
                    ];

                } catch (\Exception $e) {
                    $failed[] = [
                        'row' => $index + 2,
                        'candidate' => $row[0] ?? 'Unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

            // Log upload
            \App\Models\UploadLog::create([
                'uploaded_by' => auth()->id(),
                'school_id' => $schoolId,
                'series_id' => $seriesId,
                'subject_id' => $subjectId,
                'file_name' => $file->getClientOriginalName(),
                'upload_type' => 'candidate_data', // matches enum: candidate_data
                'records_processed' => count($successful),
                'records_failed' => count($failed),
                'status' => count($failed) > 0 ? (count($successful) > 0 ? 'partial' : 'failed') : 'success',
                'error_details' => json_encode($failed),
            ]);

            return response()->json([
                'message' => 'Results uploaded successfully',
                'successful_count' => count($successful),
                'failed_count' => count($failed),
                'data' => [
                    'successful' => $successful,
                    'failed' => $failed,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Upload failed',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Read uploaded file (CSV or Excel)
     */
    private function readUploadFile($file)
    {
        if ($file->getClientOriginalExtension() === 'csv') {
            $lines = file($file->path());
            $data = [];
            foreach ($lines as $line) {
                if (trim($line) !== '') {
                    $data[] = str_getcsv($line);
                }
            }
            return array_slice($data, 1); // Skip header
        } else {
            $data = Excel::toArray(new class {}, $file->path());
            $rows = collect($data[0])->skip(1)->toArray();
            return array_values(array_filter($rows, function($row) {
                return !empty($row) && !empty(array_filter($row));
            }));
        }
    }

    /**
     * Validate result row
     */
    private function validateResultRow(array $row): void
    {
        if (count($row) < 4) {
            throw new \Exception("Row must have 4 columns");
        }

        if (empty($row[0]) || empty($row[1])) {
            throw new \Exception("Candidate number and name are required");
        }

        if (empty($row[2])) {
            throw new \Exception("Grade is required");
        }

        if ($row[3] === null || $row[3] === '') {
            throw new \Exception("PUM is required");
        }

        $pum = (float)$row[3];
        if ($pum < 0 || $pum > 100) {
            throw new \Exception("PUM must be between 0-100");
        }
    }
}
