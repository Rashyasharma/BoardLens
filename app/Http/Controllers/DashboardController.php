<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\SubjectResult;
use App\Models\UploadLog;
use App\Models\Qualification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // 5. Best & Worst Performing Subjects (Last 3 years)
        $currentYear = (int) now()->format('Y');
        $startYear = $currentYear - 2; // Last 3 years (e.g., 2026, 2025, 2024)

        // Helper closure to calculate stats based on PUM
        $getSubjectPerformance = function ($qualificationTypes) use ($schoolId, $startYear) {
            $qualIds = Qualification::whereIn('qualification_type', $qualificationTypes)->pluck('id');
            
            $query = SubjectResult::select(
                    'subject_id',
                    DB::raw('COUNT(*) as total_results'),
                    DB::raw('AVG(pum) as avg_pum')
                )
                ->whereHas('series', function ($q) use ($startYear) {
                    $q->where('year', '>=', $startYear);
                })
                ->whereHas('subject', function ($q) use ($qualIds) {
                    $q->whereIn('qualification_id', $qualIds);
                })
                ->whereNotNull('pum')
                ->where('pum', '>', 0);
                
            if ($schoolId) {
                $query->whereHas('enrollment.candidate', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            }

            return $query->groupBy('subject_id')
                ->havingRaw('COUNT(*) > 0') // Ensure there are results
                ->with('subject')
                ->get()
                ->map(function ($result) {
                    $result->avg_pum = round($result->avg_pum, 1);
                    return $result;
                })
                ->filter(function ($result) {
                    return $result->total_results >= 5; // Meaningful sample size
                });
        };

        // GCE AS & A Level
        $gcePerformance = $getSubjectPerformance(['AS_A_LEVEL', 'AS_LEVEL', 'A_LEVEL']);
        $bestGceSubjects = $gcePerformance->sortByDesc('avg_pum')->take(5);
        $worstGceSubjects = $gcePerformance->sortBy('avg_pum')->take(5);

        // IGCSE
        $igcsePerformance = $getSubjectPerformance(['IGCSE']);
        $bestIgcseSubjects = $igcsePerformance->sortByDesc('avg_pum')->take(5);
        $worstIgcseSubjects = $igcsePerformance->sortBy('avg_pum')->take(5);

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
            'recentUploads',
            'bestGceSubjects',
            'worstGceSubjects',
            'bestIgcseSubjects',
            'worstIgcseSubjects'
        ));
    }
}
