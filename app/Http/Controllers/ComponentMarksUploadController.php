<?php

namespace App\Http\Controllers;

use App\Models\SubjectResult;
use App\Models\Component;
use App\Models\ComponentMarks;
use App\Http\Requests\UploadComponentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ComponentMarksUploadController extends Controller
{
    /**
     * Show component upload page for a result
     */
    public function show(Request $request)
    {
        $seriesId = $request->series_id;
        $subjectId = $request->subject_id;

        // Load qualifications for selector
        $qualifications = \App\Models\Qualification::all();

        $results = collect();
        $components = collect();
        $selectedSubject = null;
        $selectedSeries = null;

        if ($seriesId && $subjectId) {
            $selectedSubject = \App\Models\Subject::find($subjectId);
            $selectedSeries = \App\Models\ExamSeries::find($seriesId);

            // Get all results for this series and subject
            $results = SubjectResult::where('series_id', $seriesId)
                ->where('subject_id', $subjectId)
                ->with(['enrollment.candidate', 'componentMarks'])
                ->get();

            // Get all components for this subject
            $components = Component::where('subject_id', $subjectId)
                ->orderBy('component_code')
                ->get();
        }

        return view('uploads.components', [
            'results' => $results,
            'components' => $components,
            'series_id' => $seriesId,
            'subject_id' => $subjectId,
            'qualifications' => $qualifications,
            'selectedSubject' => $selectedSubject,
            'selectedSeries' => $selectedSeries,
        ]);
    }

    /**
     * Upload component marks for a result
     */
    public function store(UploadComponentRequest $request)
    {
        try {
            $file = $request->file('components_file');
            $fileData = $this->readUploadFile($file);

            $successful = [];
            $failed = [];

            DB::beginTransaction();

            foreach ($fileData as $index => $row) {
                try {
                    if (count($row) < 3) {
                        throw new \Exception("Row must have 3 columns");
                    }

                    $candidateNo = trim($row[0]);
                    $componentCode = trim($row[1]);
                    $obtainedMarks = (float)$row[2];

                    if (empty($candidateNo) || empty($componentCode)) {
                        throw new \Exception("Candidate number and component code are required");
                    }

                    // Find result
                    $result = SubjectResult::where('series_id', $request->series_id)
                        ->where('subject_id', $request->subject_id)
                        ->whereHas('enrollment.candidate', function ($q) use ($candidateNo) {
                            $q->where('candidate_number', $candidateNo);
                        })
                        ->first();

                    if (!$result) {
                        throw new \Exception("Result record not found for candidate {$candidateNo} in this subject and series. Please upload Grade+PUM first.");
                    }

                    // Find component
                    $component = Component::where('subject_id', $result->subject_id)
                        ->where('component_code', $componentCode)
                        ->first();

                    if (!$component) {
                        throw new \Exception("Component {$componentCode} not found for this subject");
                    }

                    // Validate marks
                    if ($obtainedMarks < 0 || $obtainedMarks > $component->total_marks) {
                        throw new \Exception("Obtained marks must be between 0 and {$component->total_marks}");
                    }

                    // Store/update component mark
                    $mark = ComponentMarks::updateOrCreate(
                        [
                            'subject_result_id' => $result->id,
                            'enrollment_id' => $result->enrollment_id,
                            'component_id' => $component->id,
                        ],
                        [
                            'obtained_marks' => $obtainedMarks,
                            'total_marks' => $component->total_marks,
                            'uploaded_by' => auth()->id(),
                            'uploaded_at' => now(),
                        ]
                    );

                    $successful[] = [
                        'candidate' => $candidateNo,
                        'component' => $componentCode,
                        'marks' => $obtainedMarks,
                    ];

                    // Refresh result and check if all components are uploaded
                    $result->load('componentMarks');
                    if ($result->hasAllComponentsUploaded()) {
                        $result->calculateFromComponents();
                    }

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
                'school_id' => auth()->user()->school_id,
                'series_id' => $request->series_id,
                'subject_id' => $request->subject_id,
                'file_name' => $file->getClientOriginalName(),
                'upload_type' => 'component_marks', // matches enum
                'records_processed' => count($successful),
                'records_failed' => count($failed),
                'status' => count($failed) > 0 ? (count($successful) > 0 ? 'partial' : 'failed') : 'success',
                'error_details' => json_encode($failed),
            ]);

            return response()->json([
                'message' => 'Component marks uploaded successfully',
                'successful_count' => count($successful),
                'failed_count' => count($failed),
                'data' => [
                    'successful' => $successful,
                    'failed' => $failed
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
            return array_slice($data, 1);
        } else {
            $data = Excel::toArray(new class {}, $file->path());
            $rows = collect($data[0])->skip(1)->toArray();
            return array_values(array_filter($rows, function($row) {
                return !empty($row) && !empty(array_filter($row));
            }));
        }
    }
}
