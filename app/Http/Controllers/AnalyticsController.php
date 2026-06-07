<?php

namespace App\Http\Controllers;

use App\Models\ExamSeries;
use App\Models\Subject;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Show yearly analytics page.
     */
    public function yearly(Request $request)
    {
        $filters = $request->only(['year', 'series_id', 'subject_id', 'qualification_type', 'grade']);
        
        // Scope filters to school
        if (auth()->user()->school_id) {
            $filters['school_id'] = auth()->user()->school_id;
        }

        $gradeDistribution = $this->analyticsService->getGradeDistribution($filters);
        $passFailStats = $this->analyticsService->getPassFailStats($filters);
        $subjectPerformance = $this->analyticsService->getSubjectPerformance($filters);
        $statisticalSummary = $this->analyticsService->getStatisticalSummary($filters);
        
        $examSeries = ExamSeries::all();
        $subjects = Subject::all();

        $data = compact(
            'gradeDistribution',
            'passFailStats',
            'subjectPerformance',
            'statisticalSummary',
            'examSeries',
            'subjects'
        );

        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('analytics.yearly', $data);
    }

    /**
     * Get YoY comparison data.
     */
    public function yoyComparison(Request $request)
    {
        $subjectId = $request->input('subject_id');
        if (empty($subjectId)) {
            return response()->json([]);
        }
        $comparison = $this->analyticsService->getYearOnYearComparison($subjectId);
        return response()->json($comparison);
    }

    /**
     * Export analytics to PDF/CSV.
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'pdf');
        $filters = $request->only(['year', 'series_id', 'subject_id']);
        
        if (auth()->user()->school_id) {
            $filters['school_id'] = auth()->user()->school_id;
        }

        $gradeDistribution = $this->analyticsService->getGradeDistribution($filters);
        $passFailStats = $this->analyticsService->getPassFailStats($filters);
        $subjectPerformance = $this->analyticsService->getSubjectPerformance($filters);
        $statisticalSummary = $this->analyticsService->getStatisticalSummary($filters);

        $data = compact(
            'gradeDistribution',
            'passFailStats',
            'subjectPerformance',
            'statisticalSummary',
            'filters'
        );

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.analytics', $data);
            return $pdf->download('analytics-report-' . now()->format('YmdHis') . '.pdf');
        } else {
            // Excel/CSV stream export
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=analytics-report-" . now()->format('YmdHis') . ".csv",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];
            
            $callback = function() use ($subjectPerformance) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Subject Performance Analysis']);
                fputcsv($file, ['Subject Code', 'Subject Name', 'Total Students', 'Average %', 'Pass Rate', 'Min %', 'Max %']);
                
                foreach ($subjectPerformance as $row) {
                    fputcsv($file, [
                        $row->subject->subject_code,
                        $row->subject->subject_name,
                        $row->total_students,
                        $row->avg_percentage . '%',
                        $row->pass_rate . '%',
                        $row->min_percentage . '%',
                        $row->max_percentage . '%'
                    ]);
                }
                
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }
    }
}
