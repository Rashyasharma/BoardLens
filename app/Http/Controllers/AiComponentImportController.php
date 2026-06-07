<?php

namespace App\Http\Controllers;

use App\Models\Qualification;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use App\Models\Component;
use App\Models\ComponentMarks;
use App\Models\UploadLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AiComponentImportController extends Controller
{
    public function showUploadForm()
    {
        $series = ExamSeries::orderBy('year', 'desc')
            ->orderByRaw("CASE WHEN month = 'March' THEN 1 WHEN month = 'June' THEN 2 WHEN month = 'November' THEN 3 ELSE 4 END")
            ->get();
        $qualifications = Qualification::orderBy('qualification_name')->get();

        $schoolId = auth()->user()->school_id;
        $recentUploads = UploadLog::where('school_id', $schoolId)
            ->where('upload_type', 'component_marks')
            ->with(['series', 'subject', 'user'])
            ->orderBy('uploaded_at', 'desc')
            ->get()
            ->unique(fn($upload) => $upload->file_name . '_' . $upload->uploaded_at->getTimestamp())
            ->take(3);

        return view('uploads.ai_components', compact('series', 'qualifications', 'recentUploads'));
    }

    public function processUploadPreview(Request $request)
    {
        $request->validate([
            'series_id' => 'required|exists:exam_series,id',
            'qualification_id' => 'required|exists:qualifications,id',
            'components_file' => 'required|file|max:15360'
        ]);

        $schoolId = auth()->user()->school_id;
        if (!$schoolId) {
            return redirect()->back()->withErrors('You must be associated with a school to perform uploads.');
        }

        $series = ExamSeries::find($request->series_id);
        $qualification = Qualification::find($request->qualification_id);
        $file = $request->file('components_file');

        try {
            $tempPath = $file->store('temp', 'local');
            $realPath = storage_path('app/private/' . $tempPath);

            $spreadsheet = IOFactory::load($realPath);
            $sheetNames = $spreadsheet->getSheetNames();

            $parsedData = [];
            $sheetsOrder = [];

            // All subjects for mapping dropdowns
            $dbSubjects = Subject::where('qualification_id', $qualification->id)
                ->with('components')
                ->orderBy('subject_code')
                ->get();

            foreach ($sheetNames as $sheetName) {
                $cleanName = trim($sheetName);
                if (!preg_match('/^\d{4}$/', $cleanName)) {
                    continue;
                }

                $sheet = $spreadsheet->getSheetByName($sheetName);
                $rows = $sheet->toArray(null, true, true, true);

                if (count($rows) < 4) {
                    continue;
                }

                $subjectCode = $cleanName;
                
                // Direct match attempt
                $subject = Subject::where('subject_code', $subjectCode)
                    ->where('qualification_id', $qualification->id)
                    ->first();

                if (!$subject) {
                    $subject = Subject::where('subject_code', $subjectCode)->first();
                }

                $subjectName = $subject ? $subject->subject_name : "Syllabus {$subjectCode}";
                $subjectId = $subject ? $subject->id : null;

                // Identify column headers dynamically from Row 4
                $row3 = $rows[3] ?? [];
                $row4 = $rows[4] ?? [];

                $candNoCol = 'D';
                $candNameCol = 'E';
                $optionCol = null;

                foreach ($row4 as $col => $headerVal) {
                    if (!$headerVal) continue;
                    $headerLower = strtolower(trim($headerVal));
                    if (in_array($headerLower, ['candidate number', 'candidate no', 'cand. no', 'no.'])) {
                        $candNoCol = $col;
                    } elseif (in_array($headerLower, ['candidate name', 'candidate', 'name'])) {
                        $candNameCol = $col;
                    } elseif (in_array($headerLower, ['option code', 'option'])) {
                        $optionCol = $col;
                    }
                }

                $componentCols = [];
                foreach ($row3 as $col => $r3Val) {
                    if ($r3Val && preg_match('/^Component\s+(\d+)/i', trim($r3Val), $matches)) {
                        $compCode = trim($matches[1]);
                        $firstDigit = substr($compCode, 0, 1);
                        $digit = ($firstDigit === '0' && strlen($compCode) > 1) ? substr($compCode, 1, 1) : $firstDigit;
                        $componentCols[$col] = [
                            'code' => $compCode,
                            'name' => trim($r3Val),
                            'paper_code' => 'Paper ' . $digit
                        ];
                    }
                }

                if (empty($componentCols)) {
                    continue;
                }

                $candidatesData = [];

                for ($i = 5; $i <= count($rows); $i++) {
                    $row = $rows[$i];
                    
                    $syllabusCell = isset($row['A']) ? trim($row['A']) : '';
                    $candNo = isset($row[$candNoCol]) ? trim($row[$candNoCol]) : '';
                    $candName = isset($row[$candNameCol]) ? trim($row[$candNameCol]) : '';
                    $optCode = $optionCol && isset($row[$optionCol]) ? trim($row[$optionCol]) : '—';

                    // Clean up non-breaking spaces and other whitespaces
                    $cleanCandName = trim(preg_replace('/\s+/u', ' ', $candName));
                    $cleanCandNo = trim(preg_replace('/\s+/u', '', $candNo));

                    // Break early if we hit summary rows or footers (check name, candidate number, or syllabus cell)
                    $upperName = strtoupper($cleanCandName);
                    $upperSyllabus = strtoupper(trim($syllabusCell));
                    if (in_array($upperName, ['MAX', 'MIN', 'AVERAGE']) || 
                        str_contains($upperName, 'REPORT GENERATED') || 
                        str_contains($upperSyllabus, 'REPORT GENERATED') ||
                        str_contains($upperName, 'CAMBRIDGEINTERNATIONAL.ORG')) {
                        break;
                    }

                    // Skip empty rows
                    if ($cleanCandNo === '' || $cleanCandName === '') {
                        continue;
                    }

                    if (is_numeric($cleanCandNo)) {
                        $cleanCandNo = str_pad($cleanCandNo, 4, '0', STR_PAD_LEFT);
                    }

                    // Look up candidate in DB using name first, then candidate number
                    $dbCandidate = Candidate::where('school_id', $schoolId)
                        ->where('candidate_name', 'like', $cleanCandName)
                        ->first();

                    if (!$dbCandidate) {
                        $dbCandidate = Candidate::where('school_id', $schoolId)
                            ->where('candidate_number', $cleanCandNo)
                            ->first();
                    }

                    // Update local variables to cleaned values
                    $candNo = $cleanCandNo;
                    $candName = $cleanCandName;

                    $candStatus = $dbCandidate ? 'exists' : 'new';
                    $subjectResult = null;
                    $status = 'Ready to import';
                    $errorMessage = null;

                    if ($dbCandidate) {
                        if ($subjectId) {
                            $subjectResult = SubjectResult::where('subject_id', $subjectId)
                                ->where('series_id', $series->id)
                                ->whereHas('enrollment', function ($q) use ($dbCandidate) {
                                    $q->where('candidate_id', $dbCandidate->id);
                                })
                                ->first();

                            if (!$subjectResult) {
                                $status = 'No Grade Uploaded';
                                $errorMessage = 'Syllabus result record missing. Please upload Grade+PUM first.';
                            }
                        }
                    } else {
                        $status = 'New Candidate';
                        $errorMessage = 'Candidate record not found in system.';
                    }

                    $marksBreakdown = [];
                    foreach ($componentCols as $col => $compInfo) {
                        $rawMark = isset($row[$col]) ? trim($row[$col]) : null;
                        
                        $marksBreakdown[$compInfo['code']] = [
                            'component_name' => $compInfo['name'],
                            'paper_code' => $compInfo['paper_code'],
                            'obtained_marks' => $rawMark !== '' && $rawMark !== null ? (float)$rawMark : null,
                        ];
                    }

                    $candidatesData[] = [
                        'candidate_number' => $candNo,
                        'candidate_name' => $candName,
                        'option_code' => $optCode,
                        'cand_status' => $candStatus,
                        'status' => $status,
                        'error_message' => $errorMessage,
                        'marks' => $marksBreakdown,
                        'db_candidate_id' => $dbCandidate ? $dbCandidate->id : null,
                    ];
                }

                $parsedData[$subjectCode] = [
                    'subject_code' => $subjectCode,
                    'subject_name' => $subjectName,
                    'subject_id' => $subjectId,
                    'components' => array_values($componentCols),
                    'candidates' => $candidatesData
                ];
                $sheetsOrder[] = $subjectCode;
            }

            @unlink($realPath);

            if (empty($parsedData)) {
                return redirect()->back()->withErrors('No valid syllabus sheets found in the Excel report.');
            }

            $sessionKey = 'ai_comp_import_' . Str::random(10);
            Session::put($sessionKey, [
                'school_id' => $schoolId,
                'series_id' => $series->id,
                'qualification_id' => $qualification->id,
                'parsed_data' => $parsedData,
                'sheets_order' => $sheetsOrder,
                'file_name' => $file->getClientOriginalName()
            ]);

            return view('uploads.ai_components_preview', [
                'parsedData' => $parsedData,
                'sheetsOrder' => $sheetsOrder,
                'series' => $series,
                'qualification' => $qualification,
                'sessionKey' => $sessionKey,
                'dbSubjects' => $dbSubjects
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Error reading components sheet: ' . $e->getMessage());
        }
    }

    public function confirmImport(Request $request)
    {
        $request->validate([
            'session_key' => 'required|string',
            'subject_mappings' => 'required|array', // e.g. [ '8021' => 'subject_id' ]
            'component_mappings' => 'required|array', // e.g. [ '8021' => [ '12' => 'component_id' ] ]
        ]);

        $sessionKey = $request->session_key;
        if (!Session::has($sessionKey)) {
            return redirect()->route('uploads.ai_components')->withErrors('Session expired. Please upload your file again.');
        }

        $sessionData = Session::get($sessionKey);
        $schoolId = $sessionData['school_id'];
        $seriesId = $sessionData['series_id'];
        $qualificationId = $sessionData['qualification_id'];
        $parsedData = $sessionData['parsed_data'];

        $uploaderId = auth()->id();
        $processedCount = 0;
        $failedCount = 0;
        $failedDetails = [];

        DB::beginTransaction();

        try {
            foreach ($parsedData as $subCode => $subData) {
                // Get mapped subject from user submission
                $subjectId = $request->input("subject_mappings.{$subCode}");
                if (!$subjectId) {
                    continue;
                }

                $subject = Subject::find($subjectId);
                if (!$subject) {
                    continue;
                }

                // Get mapped components for this subject
                $compMappings = $request->input("component_mappings.{$subCode}", []);

                foreach ($subData['candidates'] as $c) {
                    $candId = $c['db_candidate_id'];
                    if (!$candId) {
                        $failedCount++;
                        $failedDetails[] = [
                            'candidate' => $c['candidate_name'] . ' (' . $c['candidate_number'] . ')',
                            'subject' => $subject->subject_code,
                            'error' => 'Candidate record not found in system.'
                        ];
                        continue;
                    }

                    // Look up syllabus result record matching this mapped subject
                    $subjectResult = SubjectResult::where('subject_id', $subject->id)
                        ->where('series_id', $seriesId)
                        ->whereHas('enrollment', function ($q) use ($candId) {
                            $q->where('candidate_id', $candId);
                        })
                        ->first();

                    if (!$subjectResult) {
                        $failedCount++;
                        $failedDetails[] = [
                            'candidate' => $c['candidate_name'] . ' (' . $c['candidate_number'] . ')',
                            'subject' => $subject->subject_code,
                            'error' => 'Syllabus result record missing. Please upload Grade+PUM first.'
                        ];
                        continue;
                    }

                    foreach ($c['marks'] as $rawCompCode => $m) {
                        // Locate mapped component id from user's submission
                        $compId = $compMappings[$rawCompCode] ?? null;
                        if (!$compId || $m['obtained_marks'] === null) {
                            continue;
                        }

                        $dbComponent = Component::find($compId);
                        if (!$dbComponent) {
                            continue;
                        }

                        // Store or update component marks
                        ComponentMarks::updateOrCreate(
                            [
                                'subject_result_id' => $subjectResult->id,
                                'enrollment_id' => $subjectResult->enrollment_id,
                                'component_id' => $dbComponent->id,
                            ],
                            [
                                'obtained_marks' => $m['obtained_marks'],
                                'total_marks' => $dbComponent->total_marks,
                                'uploaded_by' => $uploaderId,
                                'uploaded_at' => now(),
                            ]
                        );

                        $processedCount++;
                    }

                    // Recalculate if all component marks are populated
                    $subjectResult->load('componentMarks');
                    if ($subjectResult->hasAllComponentsUploaded()) {
                        $subjectResult->calculateFromComponents();
                    }
                }
            }

            // Log the upload activity once for the entire sheet
            $fileName = $sessionData['file_name'] ?? 'AI_Provisional_Component_Marks_March_2026.xlsx';
            UploadLog::create([
                'uploaded_by' => $uploaderId,
                'school_id' => $schoolId,
                'series_id' => $seriesId,
                'subject_id' => null, // multiple subjects
                'file_name' => $fileName,
                'file_path' => 'ai_imported',
                'upload_type' => 'component_marks',
                'records_processed' => $processedCount,
                'records_failed' => $failedCount,
                'status' => $failedCount > 0 ? ($processedCount > 0 ? 'partial' : 'failed') : 'success',
                'error_details' => !empty($failedDetails) ? json_encode($failedDetails) : null,
                'uploaded_at' => now()
            ]);

            DB::commit();
            Session::forget($sessionKey);

            return redirect()->route('uploads.components')->with('success', "AI Importer successfully imported {$processedCount} component marks.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('uploads.ai_components')->withErrors('Import failed: ' . $e->getMessage());
        }
    }
}
