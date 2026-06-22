<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\SubjectResult;
use App\Models\UploadLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display dashboard.
     */
    public function index()
    {
        $schoolId = auth()->user()?->school_id ?? null;
        $school   = auth()->user()?->school ?? null;

        // Base queries, scoped to school if applicable
        $studentQuery = Candidate::query();
        $resultQuery = SubjectResult::query();
        $uploadQuery = UploadLog::query();

        if ($schoolId) {
            $studentQuery->where('school_id', $schoolId);
            $resultQuery->whereHas('enrollment.candidate', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
            $uploadQuery->where('school_id', $schoolId);
        }

        // 1. Core Database Metrics
        $totalStudents = $studentQuery->count();

        $activeSeriesQuery = ExamSeries::query();
        if ($schoolId) {
            $activeSeriesQuery->whereHas('enrollments.candidate', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        } else {
            $activeSeriesQuery->whereHas('enrollments');
        }
        $activeSeries = $activeSeriesQuery->count();
        $totalSubjects = Subject::count();
        $schoolName = $school ? $school->school_name : 'IN016 Lucky International School';

        // 2. Academic Results Analytics
        $totalResults = $resultQuery->count(); // Total subject entries
        
        // Average Pass Rate
        $passedResults = (clone $resultQuery)->where('is_passed', true)->count();
        $avgPassRate = $totalResults > 0 ? round(($passedResults / $totalResults) * 100, 1) : 0;
        
        // Top Grades percentage (A* and A)
        $topGradesCount = (clone $resultQuery)->whereIn('grade', ['A*', 'A*A*', 'A', 'AA', 'a'])->count();
        $topGradesPercent = $totalResults > 0 ? round(($topGradesCount / $totalResults) * 100, 1) : 0;
        
        // Fail count (U grade)
        $failCount = (clone $resultQuery)->whereIn('grade', ['U', 'UU'])->count();

        // 3. Components & Marks
        $componentQuery = \App\Models\ComponentMarks::query();
        if ($schoolId) {
            $componentQuery->whereHas('enrollment.candidate', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }
        $totalComponentMarks = $componentQuery->count();
        
        $completedPortfolios = (clone $resultQuery)->whereIn('status', ['complete', 'component_marks_added'])->count();
        $incompletePortfolios = (clone $resultQuery)->where('status', 'pending_components')->count();

        // 4. Platform Activity & Audits
        $aiUploads = (clone $uploadQuery)->where('file_path', 'ai_imported')->count();
        $excelUploads = (clone $uploadQuery)->where('file_path', '!=', 'ai_imported')->count();
        $flaggedResults = (clone $resultQuery)->whereIn('grade', ['Q', 'X'])->count();

        $recentUploads = $uploadQuery->with(['series', 'subject', 'user'])
            ->latest('uploaded_at')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalStudents',
            'activeSeries',
            'totalSubjects',
            'schoolName',
            'avgPassRate',
            'totalResults',
            'topGradesPercent',
            'failCount',
            'totalComponentMarks',
            'completedPortfolios',
            'incompletePortfolios',
            'aiUploads',
            'excelUploads',
            'flaggedResults',
            'recentUploads'
        ));
    }
}
