<?php

namespace App\Http\Controllers\Cbse;

use App\Http\Controllers\Controller;
use App\Models\Cbse\CbseQualification;
use App\Models\Cbse\CbseAcademicYear;
use App\Models\Cbse\CbseSubject;
use App\Models\Cbse\CbseStudent;
use App\Models\Cbse\CbseResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CbseAnalysisController extends Controller
{
    public function broadsheet(Request $request)
    {
        $academicYearId   = $request->input('academic_year_id');
        $qualificationId  = $request->input('qualification_id');

        $academicYears   = CbseAcademicYear::orderByDesc('name')->get();
        $qualifications  = CbseQualification::orderBy('qualification_type')->get();

        $candidates  = collect();
        $subjects    = collect();
        $subjectStats = [];
        $gradeStats   = [];
        $selectedYear = null;
        $selectedQualification = null;
        $historicalStats = [
            'last1' => [],
            'last3' => [],
            'last5' => []
        ];
        $dashboardStats = [];

        if ($academicYearId && $qualificationId) {
            $selectedYear = CbseAcademicYear::find($academicYearId);
            $selectedQualification = CbseQualification::find($qualificationId);

            // Get subjects that have results for this year+qualification
            $subjectIds = CbseResult::where('academic_year_id', $academicYearId)
                ->where('qualification_id', $qualificationId)
                ->distinct()
                ->pluck('subject_id');

            $subjects = CbseSubject::whereIn('id', $subjectIds)
                ->orderBy('subject_name')
                ->get();

            // Get all results for this year+qualification eagerly
            $results = CbseResult::where('academic_year_id', $academicYearId)
                ->where('qualification_id', $qualificationId)
                ->with(['student', 'subject'])
                ->get();

            // Group results by student
            $grouped = $results->groupBy('student_id');

            // Build candidate rows
            $candidates = $grouped->map(function ($studentResults) use ($subjects) {
                $student = $studentResults->first()->student;
                $row = [
                    'roll_number'    => $student->admission_number,
                    'student_name'   => $student->student_name,
                    'marks'          => [],
                    'marks_max'      => [],
                    'grades'         => [],
                    'total_obtained' => 0,
                    'total_max'      => 0,
                ];

                foreach ($studentResults as $result) {
                    $row['marks'][$result->subject_id]     = round((float) $result->total_obtained);
                    $row['marks_max'][$result->subject_id] = (int) $result->total_marks;
                    $row['grades'][$result->subject_id]    = $result->grade;
                    $row['total_obtained'] += (float) $result->total_obtained;
                    $row['total_max']      += (int)   $result->total_marks;
                }

                $row['total_obtained'] = round($row['total_obtained']);

                $row['percentage'] = $row['total_max'] > 0
                    ? round(($row['total_obtained'] / $row['total_max']) * 100, 1)
                    : 0;

                // Top 5 subjects: pick 5 best-scored subjects by marks
                $scored = collect($row['marks'])->map(function($m, $subId) use ($row) {
                    $max = $row['marks_max'][$subId] ?? 0;
                    return ['obtained' => $m, 'max' => $max];
                })->sortByDesc('obtained')->take(5);
                $row['top5_obtained'] = $scored->sum('obtained');
                $row['top5_max']      = $scored->sum('max');
                $row['top5_pct']      = $row['top5_max'] > 0
                    ? round(($row['top5_obtained'] / $row['top5_max']) * 100, 1)
                    : 0;

                return $row;
            })->sortBy('student_name')->values();

            // Compute per-subject stats
            foreach ($subjects as $sub) {
                $marksForSubject = $results->where('subject_id', $sub->id)
                    ->pluck('total_obtained')
                    ->map(fn($v) => (float)$v)
                    ->filter(fn($v) => $v >= 0);

                $subjectStats[$sub->id] = [
                    'max'     => $marksForSubject->isNotEmpty() ? $marksForSubject->max() : 0,
                    'min'     => $marksForSubject->isNotEmpty() ? $marksForSubject->min() : 0,
                    'avg'     => $marksForSubject->isNotEmpty() ? round($marksForSubject->avg(), 1) : 0,
                    'total'   => $marksForSubject->count(),
                    'passed'  => $results->where('subject_id', $sub->id)->where('is_passed', true)->count(),
                ];

                // Grade distribution
                $gradeStats[$sub->id] = $results->where('subject_id', $sub->id)
                    ->groupBy('grade')
                    ->map->count()
                    ->sortKeys()
                    ->toArray();
            }

            // Historical Averages
            $yearIndex = $academicYears->search(fn($y) => $y->id == $academicYearId);
            if ($yearIndex !== false) {
                $last1YearIds = $academicYears->slice($yearIndex + 1, 1)->pluck('id')->toArray();
                $last3YearIds = $academicYears->slice($yearIndex + 1, 3)->pluck('id')->toArray();
                $last5YearIds = $academicYears->slice($yearIndex + 1, 5)->pluck('id')->toArray();

                foreach (['last1' => $last1YearIds, 'last3' => $last3YearIds, 'last5' => $last5YearIds] as $key => $yIds) {
                    if (empty($yIds)) continue;
                    $stats = CbseResult::whereIn('academic_year_id', $yIds)
                        ->where('qualification_id', $qualificationId)
                        ->whereIn('subject_id', $subjectIds)
                        ->selectRaw('subject_id, AVG(total_obtained) as avg_marks')
                        ->groupBy('subject_id')
                        ->get();
                    foreach ($stats as $s) {
                        $historicalStats[$key][$s->subject_id] = round((float)$s->avg_marks, 1);
                    }
                }
            }
        } else {
            // Dashboard Stats Calculation using SQL for efficiency
            $rawResults = \Illuminate\Support\Facades\DB::table('cbse_results')
                ->select(
                    'academic_year_id',
                    'qualification_id',
                    'student_id',
                    \Illuminate\Support\Facades\DB::raw('SUM(total_obtained) as sum_obtained'),
                    \Illuminate\Support\Facades\DB::raw('SUM(total_marks) as sum_max')
                )
                ->groupBy('academic_year_id', 'qualification_id', 'student_id')
                ->get();
                
            $cohorts = [];
            foreach ($rawResults as $row) {
                $key = $row->academic_year_id . '|' . $row->qualification_id;
                if (!isset($cohorts[$key])) {
                    $cohorts[$key] = [];
                }
                $pct = $row->sum_max > 0 ? ($row->sum_obtained / $row->sum_max) * 100 : 0;
                $cohorts[$key][] = $pct;
            }
            
            $yearModels = $academicYears->keyBy('id');
            $qualModels = $qualifications->keyBy('id');

            foreach ($cohorts as $key => $percentages) {
                [$yId, $qId] = explode('|', $key);
                $yearName = $yearModels->has($yId) ? $yearModels->get($yId)->name : 'Unknown';
                $qualName = $qualModels->has($qId) ? $qualModels->get($qId)->qualification_name : 'Unknown';
                
                $collection = collect($percentages);
                $dashboardStats[] = [
                    'year_id' => $yId,
                    'qual_id' => $qId,
                    'year_name' => $yearName,
                    'qual_name' => $qualName,
                    'student_count' => $collection->count(),
                    'max_percentage' => round($collection->max(), 1),
                    'min_percentage' => round($collection->min(), 1),
                    'avg_percentage' => round($collection->avg(), 1)
                ];
            }
            
            usort($dashboardStats, function($a, $b) {
                return strcmp($b['year_name'], $a['year_name']);
            });
        }

        return view('cbse.analysis.broadsheet', compact(
            'academicYears', 'qualifications', 'academicYearId', 'qualificationId',
            'candidates', 'subjects', 'subjectStats', 'gradeStats',
            'selectedYear', 'selectedQualification', 'historicalStats', 'dashboardStats'
        ));
    }

    public function subjectWise(Request $request)
    {
        $subjectId = $request->input('subject_id');
        $subjects = CbseSubject::with('qualification')
            ->withCount(['results as total_candidates'])
            ->withAvg('results as avg_percentage', 'percentage')
            ->orderBy('subject_name', 'asc')
            ->get();

        $yearlyStats = [];
        $selectedSubject = null;
        $gradeDistribution = [];

        if ($subjectId) {
            $selectedSubject = CbseSubject::findOrFail($subjectId);

            $yearlyStats = CbseResult::where('subject_id', $subjectId)
                ->join('cbse_academic_years', 'cbse_results.academic_year_id', '=', 'cbse_academic_years.id')
                ->selectRaw('cbse_academic_years.name as exam_year, COUNT(cbse_results.id) as total, AVG(cbse_results.percentage) as avg_percentage, SUM(CASE WHEN cbse_results.is_passed = 1 THEN 1 ELSE 0 END) as passed')
                ->groupBy('cbse_academic_years.name')
                ->orderBy('cbse_academic_years.name')
                ->get()
                ->map(function ($row) {
                    $row->pass_rate = $row->total > 0 ? round(($row->passed / $row->total) * 100, 1) : 0;
                    $row->avg_percentage = round($row->avg_percentage, 1);
                    return $row;
                });

            // Grade breakdown for selected subject in recent years
            $rawGrades = CbseResult::where('subject_id', $subjectId)
                ->join('cbse_academic_years', 'cbse_results.academic_year_id', '=', 'cbse_academic_years.id')
                ->selectRaw('cbse_academic_years.name as exam_year, cbse_results.grade, COUNT(cbse_results.id) as count')
                ->groupBy('cbse_academic_years.name', 'cbse_results.grade')
                ->orderBy('cbse_academic_years.name')
                ->get();

            foreach ($rawGrades as $rg) {
                $gradeDistribution[$rg->exam_year][$rg->grade] = $rg->count;
            }
        }

        // --- Calculate Subjects of Concern (Downward Trend) ---
        $subjectsOfConcern = collect();
        if (!$subjectId) {
            $allYearlyStats = CbseResult::join('cbse_academic_years', 'cbse_results.academic_year_id', '=', 'cbse_academic_years.id')
                ->selectRaw('subject_id, cbse_academic_years.name as exam_year, AVG(cbse_results.percentage) as avg_percentage')
                ->groupBy('subject_id', 'cbse_academic_years.name')
                ->orderBy('cbse_academic_years.name')
                ->get();

            $subjectTrends = [];
            foreach ($allYearlyStats->groupBy('subject_id') as $subId => $stats) {
                $stats = $stats->sortBy('exam_year')->values();
                if ($stats->count() >= 2) {
                    $latest = $stats->last();
                    $previous = $stats->get($stats->count() - 2);
                    $diff = $latest->avg_percentage - $previous->avg_percentage;
                    // Any drop indicates a downward trend
                    if ($diff < 0) {
                        $subjectTrends[$subId] = [
                            'latest_year' => $latest->exam_year,
                            'latest_avg' => round($latest->avg_percentage, 1),
                            'previous_year' => $previous->exam_year,
                            'previous_avg' => round($previous->avg_percentage, 1),
                            'drop' => round(abs($diff), 1)
                        ];
                    }
                }
            }

            foreach ($subjects as $sub) {
                if (isset($subjectTrends[$sub->id])) {
                    $sub->trend = $subjectTrends[$sub->id];
                    $subjectsOfConcern->push($sub);
                }
            }
            $subjectsOfConcern = $subjectsOfConcern->sortByDesc(function($sub) {
                return $sub->trend['drop']; // Sort by largest drop first
            })->groupBy(function($sub) {
                return $sub->qualification->qualification_name ?? str_replace('CLASS_', 'Class ', $sub->qualification->qualification_type);
            });
        }

        return view('cbse.analysis.subject-wise', compact('subjects', 'subjectId', 'selectedSubject', 'yearlyStats', 'gradeDistribution', 'subjectsOfConcern'));
    }

    public function studentJourney(Request $request)
    {
        $studentName = $request->input('student_name');

        // Group students by name for the dropdown
        $students = CbseStudent::with(['results.qualification'])
            ->get()
            ->groupBy('student_name')
            ->map(function($group) {
                // Combine qualifications across all records for this name
                $types = $group->flatMap->results->pluck('qualification.qualification_type')->unique()->filter()->toArray();
                $labels = [];
                if (in_array('CLASS_10', $types)) {
                    $labels[] = 'X';
                }
                if (in_array('CLASS_12', $types)) {
                    $labels[] = 'XII';
                }
                if (empty($labels)) {
                    $types = $group->pluck('qualification_type')->unique()->toArray();
                    if (in_array('CLASS_10', $types)) $labels[] = 'X';
                    if (in_array('CLASS_12', $types)) $labels[] = 'XII';
                }
                
                // We'll use the first record for generic details (admission_number might differ, we can just join them or use the latest)
                $firstStudent = $group->sortByDesc('qualification_type')->first();
                $firstStudent->class_bracket = implode(', ', $labels);
                // Also collect all admission numbers
                $firstStudent->all_admission_numbers = $group->pluck('admission_number')->filter()->unique()->implode(', ');
                
                return $firstStudent;
            })
            ->sortBy('student_name')
            ->values();
        
        $selectedStudent = null;
        $studentJourney = [];

        if ($studentName) {
            $selectedStudent = $students->where('student_name', $studentName)->first();
            $studentIds = CbseStudent::where('student_name', $studentName)->pluck('id');
            
            $studentJourney = CbseResult::whereIn('student_id', $studentIds)
                ->with(['subject', 'qualification', 'academicYear'])
                ->get()
                ->sortBy(function($result) {
                    return $result->academicYear ? $result->academicYear->name : '';
                })
                ->groupBy(function($result) {
                    return $result->academicYear ? $result->academicYear->name : 'Unknown';
                });
        }

        return view('cbse.analysis.student-journey', compact('students', 'studentName', 'selectedStudent', 'studentJourney'));
    }
}
