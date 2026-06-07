<?php

namespace App\Http\Controllers;

use App\Models\UploadLog;
use App\Models\Qualification;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Http\Requests\StoreMarksRequest;
use App\Http\Requests\StoreThresholdRequest;
use App\Http\Requests\StoreCandidateRequest;
use App\Services\ExcelImportService;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    protected ExcelImportService $importService;

    public function __construct(ExcelImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Show marks upload page.
     */
    public function showMarksUpload()
    {
        $schoolId = auth()->user()->school_id;
        $query = UploadLog::where('upload_type', 'component_marks');
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        return view('uploads.marks', [
            'qualifications' => Qualification::all(),
            'examSeries' => ExamSeries::where('is_active', true)->get(),
            'subjects' => Subject::all(),
            'uploadHistory' => $query->with(['series', 'subject', 'user'])->latest('uploaded_at')->paginate(10)
        ]);
    }

    /**
     * Handle marks upload.
     */
    public function storeMarksUpload(StoreMarksRequest $request)
    {
        try {
            // Save upload file
            $file = $request->file('marks_file');
            $filePath = $file->store('uploads/marks', 'local');

            $schoolId = auth()->user()->school_id;

            // Import component marks
            $results = $this->importService->importComponentMarks(
                storage_path('app/private/' . $filePath), // In Laravel 11+, files are stored in 'app/private' folder by default
                $request->series_id,
                $schoolId,
                auth()->id()
            );

            // Log upload
            UploadLog::create([
                'uploaded_by' => auth()->id(),
                'school_id' => $schoolId,
                'series_id' => $request->series_id,
                'subject_id' => $request->subject_id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'upload_type' => 'component_marks',
                'records_processed' => $results['summary']['processed'],
                'records_failed' => $results['summary']['failed_count'],
                'status' => $results['summary']['failed_count'] > 0 ? ($results['summary']['processed'] > 0 ? 'partial' : 'failed') : 'success',
                'error_details' => $results['failed'] ?? null,
            ]);

            if ($results['summary']['failed_count'] > 0) {
                return redirect()->route('uploads.marks')->withErrors('Some rows failed to import. Check the Upload History log for details.');
            }

            return redirect()->route('uploads.marks')->with('success', 'Component marks uploaded and processed successfully! ' . $results['summary']['processed'] . ' records loaded.');

        } catch (\Exception $e) {
            return redirect()->route('uploads.marks')->withErrors('Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Show grade thresholds upload page.
     */
    public function showThresholdsUpload()
    {
        $schoolId = auth()->user()->school_id;
        $query = UploadLog::where('upload_type', 'grade_thresholds');
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        return view('uploads.thresholds', [
            'examSeries' => ExamSeries::all(),
            'uploadHistory' => $query->with(['series', 'user'])->latest('uploaded_at')->paginate(10)
        ]);
    }

    /**
     * Handle thresholds upload.
     */
    public function storeThresholdsUpload(StoreThresholdRequest $request)
    {
        try {
            $file = $request->file('thresholds_file');
            $filePath = $file->store('uploads/thresholds', 'local');

            $schoolId = auth()->user()->school_id;

            $results = $this->importService->importGradeThresholds(
                storage_path('app/private/' . $filePath),
                $request->series_id,
                auth()->id()
            );

            UploadLog::create([
                'uploaded_by' => auth()->id(),
                'school_id' => $schoolId,
                'series_id' => $request->series_id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'upload_type' => 'grade_thresholds',
                'records_processed' => $results['summary']['processed'],
                'records_failed' => $results['summary']['failed_count'],
                'status' => $results['summary']['failed_count'] > 0 ? ($results['summary']['processed'] > 0 ? 'partial' : 'failed') : 'success',
                'error_details' => $results['failed'] ?? null,
            ]);

            if ($results['summary']['failed_count'] > 0) {
                return redirect()->route('uploads.thresholds')->withErrors('Some rows failed to import. See details in the log.');
            }

            return redirect()->route('uploads.thresholds')->with('success', 'Grade thresholds imported successfully! ' . $results['summary']['processed'] . ' records loaded.');

        } catch (\Exception $e) {
            return redirect()->route('uploads.thresholds')->withErrors('Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Show candidates upload page.
     */
    public function showCandidatesUpload()
    {
        $schoolId = auth()->user()->school_id;
        $query = UploadLog::where('upload_type', 'candidate_data');
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        return view('uploads.candidates', [
            'uploadHistory' => $query->with(['user'])->latest('uploaded_at')->paginate(10)
        ]);
    }

    /**
     * Handle candidates upload.
     */
    public function storeCandidatesUpload(StoreCandidateRequest $request)
    {
        try {
            $file = $request->file('candidate_file');
            $filePath = $file->store('uploads/candidates', 'local');

            $schoolId = auth()->user()->school_id;

            $results = $this->importService->importCandidates(
                storage_path('app/private/' . $filePath),
                $schoolId
            );

            // Fetch an active series to associate the log with, or fallback to null
            $activeSeries = ExamSeries::where('is_active', true)->first();

            UploadLog::create([
                'uploaded_by' => auth()->id(),
                'school_id' => $schoolId,
                'series_id' => $activeSeries ? $activeSeries->id : ExamSeries::first()->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'upload_type' => 'candidate_data',
                'records_processed' => $results['summary']['processed'],
                'records_failed' => $results['summary']['failed_count'],
                'status' => $results['summary']['failed_count'] > 0 ? ($results['summary']['processed'] > 0 ? 'partial' : 'failed') : 'success',
                'error_details' => $results['failed'] ?? null,
            ]);

            if ($results['summary']['failed_count'] > 0) {
                return redirect()->route('uploads.candidates')->withErrors('Some rows failed to import.');
            }

            return redirect()->route('uploads.candidates')->with('success', 'Candidate profiles imported successfully! ' . $results['summary']['processed'] . ' records loaded.');

        } catch (\Exception $e) {
            return redirect()->route('uploads.candidates')->withErrors('Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Get upload history.
     */
    public function uploadHistory()
    {
        $schoolId = auth()->user()->school_id;
        $query = UploadLog::query();
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $uploads = $query->with(['series', 'subject', 'user', 'school'])
            ->latest('uploaded_at')
            ->paginate(15);

        return view('uploads.history', compact('uploads'));
    }
}
