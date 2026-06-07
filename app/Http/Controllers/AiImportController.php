<?php

namespace App\Http\Controllers;

use App\Services\AiSpreadsheetParser;
use App\Models\Qualification;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use App\Models\UploadLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AiImportController extends Controller
{
    protected AiSpreadsheetParser $parser;

    public function __construct(AiSpreadsheetParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * Show upload form
     */
    public function showUploadForm()
    {
        $schoolId = auth()->user()->school_id;
        $recentUploads = UploadLog::where('school_id', $schoolId)
            ->where('upload_type', 'candidate_data')
            ->with(['series', 'user'])
            ->orderBy('uploaded_at', 'desc')
            ->get()
            ->unique(fn($upload) => $upload->file_name . '_' . $upload->uploaded_at->getTimestamp())
            ->take(3);

        return view('uploads.ai_importer', compact('recentUploads'));
    }

    /**
     * Parse files and present a comparison preview
     */
    public function processUploadPreview(Request $request)
    {
        $request->validate([
            'statement_files' => 'required|array',
            'statement_files.*' => 'file|max:10240' // max 10MB per file
        ]);

        $schoolId = auth()->user()->school_id;
        if (!$schoolId) {
            return redirect()->back()->withErrors('You must be associated with a school to perform uploads.');
        }

        $allFilesData = [];
        $errors = [];

        foreach ($request->file('statement_files') as $file) {
            try {
                if (!$file || !$file->isValid() || empty($file->getRealPath())) {
                    $errorCode = $file ? $file->getError() : 'unknown';
                    $errorMessage = match ($errorCode) {
                        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
                        3 => 'The uploaded file was only partially uploaded.',
                        4 => 'No file was uploaded or the temporary file could not be created/found.',
                        6 => 'Missing a temporary folder for uploads.',
                        7 => 'Failed to write file to disk.',
                        8 => 'A PHP extension stopped the file upload.',
                        default => 'Unknown upload error (code: ' . $errorCode . ') or temporary path is invalid.'
                    };
                    throw new \Exception("File upload failed: " . $errorMessage);
                }

                $tempPath = $file->store('temp', 'local');
                $realPath = storage_path('app/private/' . $tempPath); // In Laravel 11+, stored in 'app/private'

                // Parse sheet
                $parsed = $this->parser->parse($realPath);
                
                // Cleanup temp file
                @unlink($realPath);

                // Find or prepare series details
                $seriesMonth = $parsed['series']['month'];
                $seriesYear = $parsed['series']['year'];
                $seriesCode = strtoupper(substr($seriesMonth, 0, 3)) . '-' . $seriesYear;

                // Find or create series
                $series = ExamSeries::firstOrCreate(
                    ['series_code' => $seriesCode],
                    [
                        'year' => $seriesYear,
                        'month' => $seriesMonth,
                        'series_name' => "{$seriesMonth} {$seriesYear}",
                        'is_active' => true
                    ]
                );

                $qualType = $parsed['qualification'];
                $qualification = Qualification::where('qualification_type', $qualType)->first();
                if (!$qualification) {
                    throw new \Exception("Could not locate qualification record for type: {$qualType}");
                }

                // Analyze database comparison
                $comparison = $this->compareWithDatabase($parsed['candidates'], $series, $qualification, $schoolId);

                $allFilesData[] = [
                    'file_name' => $file->getClientOriginalName(),
                    'series_id' => $series->id,
                    'series_name' => $series->series_name,
                    'qualification_id' => $qualification->id,
                    'qualification_name' => $qualification->qualification_name,
                    'model_name' => $parsed['model_name'],
                    'ai_used' => $parsed['ai_used'],
                    'subjects' => $parsed['subjects_mapped'],
                    'comparison' => $comparison,
                    'ai_audit' => $parsed['ai_audit'] ?? null,
                ];
            } catch (\Exception $e) {
                $errors[] = "Error processing {$file->getClientOriginalName()}: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString();
            }
        }

        if (count($allFilesData) === 0) {
            return redirect()->back()->withErrors($errors);
        }

        // Fetch subjects list for dropdown overrides
        $dbSubjects = Subject::with('qualification')->orderBy('subject_code')->get();

        // Store in session for confirmation step
        $importSessionKey = 'ai_import_' . Str::random(10);
        Session::put($importSessionKey, [
            'school_id' => $schoolId,
            'files_data' => $allFilesData
        ]);

        return view('uploads.ai_preview', [
            'filesData' => $allFilesData,
            'sessionKey' => $importSessionKey,
            'importErrors' => $errors,
            'dbSubjects' => $dbSubjects
        ]);
    }

    /**
     * Perform the actual database writes
     */
    public function confirmImport(Request $request)
    {
        $request->validate([
            'session_key' => 'required|string',
            'mappings' => 'nullable|array'
        ]);

        $sessionKey = $request->session_key;
        if (!Session::has($sessionKey)) {
            return redirect()->route('uploads.ai_importer')->withErrors('Session expired. Please upload your files again.');
        }

        $sessionData = Session::get($sessionKey);
        $schoolId = $sessionData['school_id'];
        $filesData = $sessionData['files_data'];

        $uploaderId = auth()->id();
        $processedCount = 0;
        $failedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($filesData as $fIdx => $fileData) {
                $seriesId = $fileData['series_id'];
                $qualificationId = $fileData['qualification_id'];
                $fileMappings = $request->input("mappings.{$fIdx}", []);

                // Get subject models mapped
                $subjects = [];
                foreach ($fileData['subjects'] as $col => $subData) {
                    if (!is_array($subData)) {
                        continue;
                    }
                    // Respect user-specified manual override code if provided
                    $subCode = $fileMappings[$col] ?? $subData['subject_code'];
                    
                    $subQualType = $subData['qualification'] ?? 'IGCSE';
                    $subQual = Qualification::where('qualification_type', $subQualType)->first();
                    $subQualId = $subQual ? $subQual->id : $qualificationId;

                    $subjects[$subCode] = Subject::where('subject_code', $subCode)
                        ->where('qualification_id', $subQualId)
                        ->first();
                }

                foreach ($fileData['comparison']['candidates'] as $c) {
                    if (!is_array($c)) {
                        continue;
                    }
                    $cNo = $c['candidate_number'] ?? null;
                    $cName = $c['candidate_name'] ?? null;
                    if (!$cNo || !$cName) {
                        continue;
                    }

                    // Use centralized Candidate lookup/creation helper to prevent duplicate profiles
                    $candidate = Candidate::findOrCreateByNameAndNumber($schoolId, $cNo, $cName);

                    foreach ($fileData['subjects'] as $col => $subData) {
                        if (!is_array($subData)) {
                            continue;
                        }
                        $oldSubCode = $subData['subject_code'];
                        $newSubCode = $fileMappings[$col] ?? $oldSubCode;

                        $r = $c['results'][$oldSubCode] ?? null;
                        if (!$r) continue;

                        if (!is_array($r)) {
                            $r = [
                                'grade' => 'U',
                                'pum' => 0.0,
                                'raw_value' => is_scalar($r) ? (string)$r : ''
                            ];
                        }
                        $subject = $subjects[$newSubCode] ?? null;
                        if (!$subject) continue;

                        // 2. Ensure general enrollment exists for the subject's qualification
                        $generalEnrollment = CandidateEnrollment::firstOrCreate(
                            [
                                'candidate_id' => $candidate->id,
                                'series_id' => $seriesId,
                                'qualification_id' => $subject->qualification_id,
                                'subject_id' => null,
                            ],
                            [
                                'enrolled_date' => now()->toDateString(),
                                'enrollment_status' => 'enrolled',
                            ]
                        );

                        // 3. Ensure subject-specific enrollment exists
                        CandidateEnrollment::firstOrCreate(
                            [
                                'candidate_id' => $candidate->id,
                                'series_id' => $seriesId,
                                'qualification_id' => $subject->qualification_id,
                                'subject_id' => $subject->id,
                            ],
                            [
                                'enrolled_date' => now()->toDateString(),
                                'enrollment_status' => 'enrolled',
                            ]
                        );

                        // 4. Create or update SubjectResult
                        SubjectResult::updateOrCreate(
                            [
                                'enrollment_id' => $generalEnrollment->id,
                                'subject_id' => $subject->id,
                                'series_id' => $seriesId,
                            ],
                            [
                                'grade' => $r['grade'],
                                'pum' => $r['pum'],
                                'status' => 'pending_components',
                                'result_uploaded_at' => now(),
                                'uploaded_by' => $uploaderId,
                            ]
                        );

                        $processedCount++;
                    }
                }

                // Create Upload Log
                UploadLog::create([
                    'uploaded_by' => $uploaderId,
                    'school_id' => $schoolId,
                    'series_id' => $seriesId,
                    'subject_id' => null, // multiple subjects
                    'file_name' => $fileData['file_name'],
                    'file_path' => 'ai_imported',
                    'upload_type' => 'candidate_data',
                    'records_processed' => $processedCount,
                    'records_failed' => $failedCount,
                    'status' => 'success',
                    'uploaded_at' => now()
                ]);
            }

            DB::commit();
            Session::forget($sessionKey);

            return redirect()->route('dashboard')->with('success', "AI Importer successfully imported {$processedCount} results.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('uploads.ai_importer')->withErrors('Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Compare parsed candidate entries with database records.
     */
    protected function compareWithDatabase(array $candidates, ExamSeries $series, Qualification $qualification, string $schoolId): array
    {
        $stats = [
            'total_parsed' => 0,
            'new_candidates' => 0,
            'new_results' => 0,
            'updated_results' => 0,
            'no_change_results' => 0
        ];

        $comparedCandidates = [];

        foreach ($candidates as $c) {
            if (!is_array($c)) {
                continue;
            }
            $candNo = $c['candidate_number'] ?? null;
            $candName = $c['candidate_name'] ?? null;
            if (!$candNo || !$candName) {
                continue;
            }

            // Check if Candidate exists in DB using name first, then candidate number
            $dbCandidate = Candidate::where('school_id', $schoolId)
                ->where('candidate_name', 'like', $candName)
                ->first();

            if (!$dbCandidate) {
                $dbCandidate = Candidate::where('school_id', $schoolId)
                    ->where('candidate_number', $candNo)
                    ->first();
            }

            $candStatus = $dbCandidate ? 'exists' : 'new';
            if ($candStatus === 'new') {
                $stats['new_candidates']++;
            }

            $candResults = [];

            foreach ($c['results'] as $subCode => $r) {
                $stats['total_parsed']++;
                
                // Ensure $r is an array to prevent "access array offset on value of type int"
                if (!is_array($r)) {
                    $r = [
                        'grade' => 'U',
                        'pum' => 0.0,
                        'raw_value' => is_scalar($r) ? (string)$r : ''
                    ];
                }

                // Determine qualification type based on subject code
                $subCodeStr = (string)$subCode;
                $subQualType = "IGCSE";
                if (strlen($subCodeStr) == 4 && in_array($subCodeStr[0], ['8', '9'])) {
                    $subQualType = "AS_A_LEVEL";
                }
                
                $subQual = Qualification::where('qualification_type', $subQualType)->first();
                $subQualId = $subQual ? $subQual->id : $qualification->id;

                $subject = Subject::where('subject_code', $subCode)
                    ->where('qualification_id', $subQualId)
                    ->first();

                if (!$subject) {
                    $candResults[$subCode] = array_merge($r, [
                        'status' => 'error',
                        'error_message' => "Subject {$subCode} not found in DB"
                    ]);
                    continue;
                }

                $dbResult = null;
                if ($dbCandidate) {
                    $dbResult = SubjectResult::where('subject_id', $subject->id)
                        ->where('series_id', $series->id)
                        ->whereHas('enrollment', function ($q) use ($dbCandidate) {
                            $q->where('candidate_id', $dbCandidate->id);
                        })
                        ->first();
                }

                if (!$dbResult) {
                    $stats['new_results']++;
                    $candResults[$subCode] = array_merge($r, [
                        'status' => 'new',
                        'db_pum' => null,
                        'db_grade' => null
                    ]);
                } else {
                    $isDifferent = ($dbResult->grade !== $r['grade'] || (float)$dbResult->pum !== (float)$r['pum']);
                    if ($isDifferent) {
                        $stats['updated_results']++;
                        $candResults[$subCode] = array_merge($r, [
                            'status' => 'update',
                            'db_pum' => (float)$dbResult->pum,
                            'db_grade' => $dbResult->grade
                        ]);
                    } else {
                        $stats['no_change_results']++;
                        $candResults[$subCode] = array_merge($r, [
                            'status' => 'no_change',
                            'db_pum' => (float)$dbResult->pum,
                            'db_grade' => $dbResult->grade
                        ]);
                    }
                }
            }

            $comparedCandidates[] = [
                'candidate_number' => $candNo,
                'candidate_name' => $candName,
                'status' => $candStatus,
                'results' => $candResults
            ];
        }

        return [
            'stats' => $stats,
            'candidates' => $comparedCandidates
        ];
    }
}
