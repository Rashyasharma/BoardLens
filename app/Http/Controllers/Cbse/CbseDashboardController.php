<?php

namespace App\Http\Controllers\Cbse;

use App\Http\Controllers\Controller;
use App\Models\Cbse\CbseQualification;
use App\Models\Cbse\CbseSubject;
use App\Models\Cbse\CbseStudent;
use App\Models\Cbse\CbseResult;
use Illuminate\Http\Request;

class CbseDashboardController extends Controller
{
    public function index()
    {
        // 1. Core Database Metrics
        $totalStudents = CbseStudent::count();
        $totalQualifications = CbseQualification::count();
        $totalSubjects = CbseSubject::count();

        // 2. Academic Results Analytics
        $resultQuery = CbseResult::query();
        $totalResults = $resultQuery->count();

        // Average Pass Rate
        $passedResults = (clone $resultQuery)->where('is_passed', true)->count();
        $avgPassRate = $totalResults > 0 ? round(($passedResults / $totalResults) * 100, 1) : 0;

        // Top Grades (A1 and A2)
        $topGradesCount = (clone $resultQuery)->whereIn('grade', ['A1', 'A2'])->count();
        $topGradesPercent = $totalResults > 0 ? round(($topGradesCount / $totalResults) * 100, 1) : 0;

        // Fail count
        $failCount = (clone $resultQuery)->whereIn('grade', ['E1', 'E2'])->count();
        $absentCount = (clone $resultQuery)->where('is_absent', true)->count();
        $compartmentCount = (clone $resultQuery)->where('is_compartment', true)->count();

        // 3. Year-wise Distribution
        $yearlyStats = CbseResult::selectRaw('cbse_academic_years.name, cbse_results.academic_year_id, COUNT(cbse_results.id) as total_entries, AVG(cbse_results.percentage) as avg_percent, SUM(CASE WHEN cbse_results.is_passed = 1 THEN 1 ELSE 0 END) as passed_entries')
            ->join('cbse_academic_years', 'cbse_results.academic_year_id', '=', 'cbse_academic_years.id')
            ->groupBy('cbse_academic_years.name', 'cbse_results.academic_year_id')
            ->orderBy('cbse_academic_years.name', 'desc')
            ->get()
            ->map(function ($row) {
                $row->pass_rate = $row->total_entries > 0 ? round(($row->passed_entries / $row->total_entries) * 100, 1) : 0;
                $row->avg_percent = round($row->avg_percent, 1);
                return $row;
            });

        return view('cbse.dashboard.index', compact(
            'totalStudents',
            'totalQualifications',
            'totalSubjects',
            'totalResults',
            'avgPassRate',
            'topGradesPercent',
            'failCount',
            'absentCount',
            'compartmentCount',
            'yearlyStats'
        ));
    }
}
