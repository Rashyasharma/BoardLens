<?php

namespace App\Http\Controllers;

use App\Models\SubjectResult;
use App\Models\Subject;
use App\Models\ExamSeries;
use App\Models\Qualification;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    /**
     * Yearly PUM trends for all subjects — used by the Yearly Trends panel on subject-wise page.
     * Returns JSON with per-subject, per-series avg PUM data grouped by qualification.
     */
    public function yearlyPumTrends(Request $request)
    {
        $yearFrom = (int) ($request->get('year_from', 2020));
        $yearTo   = (int) ($request->get('year_to', now()->year));

        $monthOrder = ['March' => 1, 'June' => 2, 'November' => 3];

        // Load all series in the year range, sorted chronologically
        $allSeries = ExamSeries::whereBetween('year', [$yearFrom, $yearTo])
            ->get()
            ->sortBy(fn($s) => $s->year * 10 + ($monthOrder[$s->month] ?? 0))
            ->values();

        // Load all subjects with their qualification (eager)
        $subjects = Subject::with('qualification')->get()->keyBy('id');

        // Aggregate avg PUM per (subject_id, series_id) in one query
        $rows = DB::table('subject_results')
            ->select('subject_id', 'series_id', DB::raw('ROUND(AVG(pum),1) as avg_pum'), DB::raw('COUNT(*) as entries'))
            ->whereIn('series_id', $allSeries->pluck('id'))
            ->groupBy('subject_id', 'series_id')
            ->get();

        // Index series by id for quick lookup
        $seriesById = $allSeries->keyBy('id');

        // Build: qual_id -> subject_id -> [series_name => avg_pum]
        $byQualSubjectSeries = [];
        foreach ($rows as $row) {
            $subject = $subjects->get($row->subject_id);
            if (!$subject) continue;
            $series  = $seriesById->get($row->series_id);
            if (!$series) continue;

            $qualId   = $subject->qualification_id;
            $subId    = $subject->id;
            $serName  = $series->series_name;

            $byQualSubjectSeries[$qualId][$subId]['subject_name'] = $subject->subject_name;
            $byQualSubjectSeries[$qualId][$subId]['subject_code'] = $subject->subject_code;
            $byQualSubjectSeries[$qualId][$subId]['series'][$serName] = [
                'avg_pum' => (float)$row->avg_pum,
                'entries' => (int)$row->entries,
            ];
        }

        // Build response payload
        $qualifications = Qualification::all()->keyBy('id');
        $result = [];

        foreach ($byQualSubjectSeries as $qualId => $subjectsData) {
            $qual = $qualifications->get($qualId);
            if (!$qual) continue;

            // Series labels for this qualification (ordered)
            $seriesNames = $allSeries->pluck('series_name')->unique()->values()->toArray();

            // Build subject rows
            $subjectRows = [];
            foreach ($subjectsData as $subId => $data) {
                $pumValues = collect($data['series'] ?? [])->pluck('avg_pum')->filter()->values();
                $overallAvg = $pumValues->isNotEmpty() ? round($pumValues->avg(), 1) : null;
                $subjectRows[] = [
                    'subject_id'   => $subId,
                    'subject_name' => $data['subject_name'],
                    'subject_code' => $data['subject_code'],
                    'overall_avg'  => $overallAvg,
                    'series'       => $data['series'] ?? [],
                ];
            }

            // Sort by overall_avg descending for rankings
            usort($subjectRows, fn($a, $b) => ($b['overall_avg'] ?? -1) <=> ($a['overall_avg'] ?? -1));

            $result[] = [
                'qualification_id'   => $qualId,
                'qualification_name' => $qual->qualification_name,
                'qualification_type' => $qual->qualification_type,
                'series_labels'      => $seriesNames,
                'subjects'           => $subjectRows,
                'highest'            => $subjectRows[0] ?? null,
                'lowest'             => end($subjectRows) ?: null,
            ];
        }

        // Get available year range from the database
        $minYear = ExamSeries::min('year') ?? 2020;
        $maxYear = ExamSeries::max('year') ?? now()->year;

        return response()->json([
            'year_from'    => $yearFrom,
            'year_to'      => $yearTo,
            'min_year'     => $minYear,
            'max_year'     => $maxYear,
            'qualifications' => $result,
        ]);
    }

    /**
     * Subject-wise analysis
     */

    public function subjectWise(Request $request)
    {
        $selectedQualId = $request->get('qualification_id');
        $selectedSubjectId = $request->get('subject_id');

        $results = collect();
        $stats = [];
        $gradeDistribution = [];
        $seriesProgress = [];
        $componentAnalysis = [];
        $bestComponent = null;
        $worstComponent = null;

        if ($selectedSubjectId) {
            $results = SubjectResult::where('subject_id', $selectedSubjectId)
                ->with(['subject.qualification', 'series', 'enrollment.candidate', 'componentMarks.component'])
                ->get();

            if ($results->isNotEmpty()) {
                // Overall stats
                $stats = $this->calculateSubjectStats($results);
                $gradeDistribution = $results->groupBy('grade')->map->count();

                // Ensure all standard grades have keys in gradeDistribution
                $standardGrades = ['A*', 'A*A*', 'A', 'AA', 'a', 'B', 'BB', 'b', 'C', 'CC', 'c', 'D', 'DD', 'd', 'E', 'EE', 'e', 'F', 'FF', 'G', 'GG', 'U', 'UU'];
                foreach ($standardGrades as $g) {
                    if (!isset($gradeDistribution[$g])) {
                        $gradeDistribution[$g] = 0;
                    }
                }

                // Group chronologically by Series
                $monthOrder = ['March' => 1, 'June' => 2, 'November' => 3];
                $groupedResults = $results->groupBy('series_id')->sortBy(function ($group) use ($monthOrder) {
                    $s = $group->first()->series;
                    return $s->year * 10 + ($monthOrder[$s->month] ?? 0);
                });

                foreach ($groupedResults as $seriesId => $seriesResults) {
                    $series = $seriesResults->first()->series;
                    
                    // Fetch total enrollments for this subject in this series
                    $totalEnrollments = \App\Models\CandidateEnrollment::where('subject_id', $selectedSubjectId)
                        ->where('series_id', $seriesId)
                        ->count();

                    // Filter out Q, X, and pending grades for standard pass/fail stats
                    $academicResults = $seriesResults->filter(function ($r) {
                        $g = strtoupper($r->grade ?? '');
                        return !empty($g) && !in_array($g, ['Q', 'X', 'PENDING']);
                    });

                    $passes = $academicResults->where('is_passed', true)->count();
                    $fails = $academicResults->where('is_passed', false)->count();
                    
                    $pendingQ = $seriesResults->filter(fn($r) => strtoupper($r->grade ?? '') === 'Q')->count() 
                        + max(0, $totalEnrollments - $seriesResults->count());
                    
                    $noResultX = $seriesResults->filter(fn($r) => strtoupper($r->grade ?? '') === 'X')->count();

                    $candidatesList = $seriesResults->map(function ($r) {
                        return [
                            'candidate_number' => $r->enrollment->candidate->candidate_number ?? 'N/A',
                            'candidate_name' => $r->enrollment->candidate->candidate_name ?? 'N/A',
                            'grade' => $r->grade,
                            'pum' => (float)$r->pum,
                        ];
                    })->sortBy('candidate_number')->values()->toArray();

                    $seriesProgress[] = [
                        'series_name' => $series->series_name,
                        'entries' => $seriesResults->count(),
                        'avg_pum' => round($seriesResults->avg('pum'), 1),
                        'best_grade' => $this->getBestGrade($seriesResults),
                        'worst_grade' => $this->getWorstGrade($seriesResults),
                        'best_pum' => $seriesResults->max('pum'),
                        'worst_pum' => $seriesResults->min('pum'),
                        'pass' => $passes,
                        'fail' => $fails,
                        'pending_q' => $pendingQ,
                        'no_result_x' => $noResultX,
                        'candidates' => $candidatesList,
                    ];
                }

                // Component Analysis
                $componentAnalysis = $this->analyzeComponents($results);
                if (!empty($componentAnalysis)) {
                    $sortedComponents = collect($componentAnalysis)->sortByDesc('avg_percentage');
                    $bestComponent = $sortedComponents->first();
                    $worstComponent = $sortedComponents->last();
                }
            }
        }

        return view('analysis.subject-wise', [
            'results' => $results,
            'stats' => $stats,
            'gradeDistribution' => $gradeDistribution,
            'subjects' => Subject::with('qualification')
                ->withCount(['results as total_candidates'])
                ->withAvg('results as avg_pum', 'pum')
                ->orderBy('subject_name', 'asc')
                ->get(),
            'qualifications' => Qualification::all(),
            'selectedQualId' => $selectedQualId ?? '',
            'selectedSubjectId' => $selectedSubjectId ?? '',
            'seriesProgress' => $seriesProgress,
            'componentAnalysis' => $componentAnalysis,
            'bestComponent' => $bestComponent,
            'worstComponent' => $worstComponent,
        ]);
    }

    /**
     * Component marks analysis
     */
    public function componentMarks(Request $request)
    {
        $filters = $request->only(['subject_id', 'year', 'month', 'series_id', 'series_from', 'series_to']);

        $query = SubjectResult::forAnalysis(
            $filters['subject_id'] ?? null,
            $filters['year'] ?? null,
            $filters['month'] ?? null,
            $filters['series_id'] ?? null
        );

        // Series range filter: get all series IDs chronologically between from and to
        $selectedSeriesFrom = $filters['series_from'] ?? '';
        $selectedSeriesTo = $filters['series_to'] ?? '';
        $rangeSeriesIds = [];

        if ($selectedSeriesFrom && $selectedSeriesTo) {
            $monthOrder = ['March' => 1, 'June' => 2, 'November' => 3];
            $allSeries = ExamSeries::all()->sortBy(function ($s) use ($monthOrder) {
                return $s->year * 10 + ($monthOrder[$s->month] ?? 0);
            })->values();

            $fromIndex = $allSeries->search(fn($s) => $s->id == $selectedSeriesFrom);
            $toIndex = $allSeries->search(fn($s) => $s->id == $selectedSeriesTo);

            if ($fromIndex !== false && $toIndex !== false) {
                if ($fromIndex > $toIndex) {
                    [$fromIndex, $toIndex] = [$toIndex, $fromIndex];
                }
                $rangeSeriesIds = $allSeries->slice($fromIndex, $toIndex - $fromIndex + 1)->pluck('id')->toArray();
                $query->whereIn('series_id', $rangeSeriesIds);
            }
        }

        // Calculate statistics based on a lightweight aggregation query instead of loading all models
        $statsQuery = clone $query;

        // Optimize: load only required relations and fields
        // Paginate results to prevent memory exhaustion when loading large dataset
        $results = $query->select(['id', 'subject_id', 'series_id', 'enrollment_id', 'grade', 'pum'])
            ->with([
                'componentMarks:id,subject_result_id,obtained_marks,percentage,component_id',
                'componentMarks.component:id,component_code,component_name,total_marks',
                'series:id,year,month,series_code',
                'subject:id,subject_code,subject_name',
                'enrollment.candidate:id,candidate_name,candidate_number'
            ])
            ->paginate(25)
            ->withQueryString();

        $statsResults = $statsQuery->select(['id', 'subject_id', 'series_id', 'enrollment_id'])
            ->with([
                'componentMarks:id,subject_result_id,obtained_marks,percentage,component_id',
                'componentMarks.component:id,component_code,component_name,total_marks,subject_id',
                'series:id,year,month,series_code',
                'enrollment.candidate:id,candidate_name'
            ])
            ->get();

        $componentAnalysis = $this->analyzeComponents($statsResults);

        // Compute component trends: avg marks per series for each component (optimized)
        $componentTrends = [];
        $monthOrder = ['March' => 1, 'June' => 2, 'November' => 3];

        // Load Qualifications and subjects grouped by qualification, including components
        $qualifications = Qualification::with('subjects.components')->get();

        // Compute component trends, max/min scores, and candidate names for each series (optimized)
        $componentTrends = [];
        $monthOrder = ['March' => 1, 'June' => 2, 'November' => 3];

        // Group results by series, sorted chronologically
        $resultsBySeries = $statsResults->groupBy('series_id')->sortBy(function ($group) use ($monthOrder) {
            $s = $group->first()->series;
            return ($s->year ?? 0) * 10 + ($monthOrder[$s->month ?? ''] ?? 0);
        });

        foreach ($resultsBySeries as $seriesId => $seriesResults) {
            $firstResult = $seriesResults->first();
            if (!$firstResult || !$firstResult->series) continue;
            $seriesName = $firstResult->series->series_name;

            foreach ($seriesResults as $result) {
                $candidateName = $result->enrollment->candidate->candidate_name ?? 'N/A';
                foreach ($result->componentMarks as $mark) {
                    if (!$mark->component) continue;
                    $componentName = $result->subject_id . '_' . $mark->component->component_code . ' - ' . $mark->component->component_name;
                    if (!isset($componentTrends[$componentName])) {
                        $componentTrends[$componentName] = [
                            'yearly' => [],
                            'highest_score' => 0,
                            'highest_candidate' => 'N/A',
                            'lowest_score' => 100,
                            'lowest_candidate' => 'N/A'
                        ];
                    }

                    // Log overall min/max marks
                    $pct = $mark->percentage;
                    if ($pct > $componentTrends[$componentName]['highest_score']) {
                        $componentTrends[$componentName]['highest_score'] = $pct;
                        $componentTrends[$componentName]['highest_candidate'] = $candidateName;
                    }
                    if ($pct < $componentTrends[$componentName]['lowest_score']) {
                        $componentTrends[$componentName]['lowest_score'] = $pct;
                        $componentTrends[$componentName]['lowest_candidate'] = $candidateName;
                    }

                    $componentTrends[$componentName]['yearly'][$seriesName]['percentages'][] = $pct;
                    $componentTrends[$componentName]['yearly'][$seriesName]['raw_marks'][] = $mark->obtained_marks;
                    $componentTrends[$componentName]['yearly'][$seriesName]['candidates'][] = [
                        'name' => $candidateName,
                        'mark' => $mark->obtained_marks
                    ];
                }
            }
        }

        // Format trends data per series for Chart and Detailed analysis
        foreach ($componentTrends as $compName => &$data) {
            $seriesList = [];
            foreach ($data['yearly'] as $sName => $seriesData) {
                $raw = $seriesData['raw_marks'];
                $pcts = $seriesData['percentages'];
                
                // Find index of max and min
                $maxVal = max($raw);
                $minVal = min($raw);
                
                $maxCandName = 'N/A';
                $minCandName = 'N/A';
                
                foreach ($seriesData['candidates'] as $cand) {
                    if ($cand['mark'] == $maxVal) $maxCandName = $cand['name'];
                    if ($cand['mark'] == $minVal) $minCandName = $cand['name'];
                }

                $seriesList[] = [
                    'series' => $sName,
                    'candidate_count' => count($pcts),
                    'avg_pct' => round(array_sum($pcts) / count($pcts), 1),
                    'max_score' => $maxVal,
                    'max_candidate' => $maxCandName,
                    'min_score' => $minVal,
                    'min_candidate' => $minCandName,
                ];
            }
            $data['series_trends'] = $seriesList;
        }
        unset($data);

        return view('analysis.component-marks', [
            'componentAnalysis' => $componentAnalysis,
            'componentTrends' => $componentTrends,
            'results' => $results,
            'qualifications' => $qualifications,
            'subjects' => Subject::all(),
            'series' => ExamSeries::all(),
            'selectedSubjectId' => $filters['subject_id'] ?? '',
            'selectedYear' => $filters['year'] ?? '',
            'selectedSeriesId' => $filters['series_id'] ?? '',
            'selectedSeriesFrom' => $selectedSeriesFrom,
            'selectedSeriesTo' => $selectedSeriesTo,
        ]);
    }

    /**
     * Grade threshold analysis
     */
    public function gradeThreshold(Request $request)
    {
        $subjectId = $request->get('subject_id');
        $year = $request->get('year');

        $series = ExamSeries::when($year, function ($q) use ($year) {
                $q->where('year', $year);
            })
            ->with(['gradeThresholds' => function ($q) use ($subjectId) {
                if ($subjectId) $q->where('subject_id', $subjectId);
            }])
            ->get();

        $thresholdComparison = [];
        foreach ($series as $s) {
            $results = SubjectResult::where('series_id', $s->id)
                ->when($subjectId, function ($q) use ($subjectId) {
                    $q->where('subject_id', $subjectId);
                })
                ->get()
                ->groupBy('grade')
                ->map->count();

            $thresholdComparison[$s->series_name] = $results;
        }

        return view('analysis.grade-threshold', [
            'thresholdComparison' => $thresholdComparison,
            'subjects' => Subject::all(),
            'selectedSubjectId' => $subjectId ?? '',
            'selectedYear' => $year ?? '',
            'series' => $series,
        ]);
    }

    /**
     * Trends analysis
     */
    public function trends(Request $request)
    {
        $subjectId = $request->get('subject_id');
        $yearRange = $request->get('year_range') ? explode('-', $request->get('year_range')) : [2022, 2026];

        $trends = $this->calculateTrends($subjectId, $yearRange);

        return view('analysis.trends', [
            'trends' => $trends,
            'subjects' => Subject::all(),
            'selectedSubjectId' => $subjectId ?? '',
            'selectedYearRange' => implode('-', $yearRange),
        ]);
    }

    /**
     * Student journey
     */
    /**
     * Student journey
     */
    public function studentJourney(Request $request)
    {
        $selectedName = $request->get('candidate_name');
        $candidateId = $request->get('candidate_id');
        $candidateNumber = $request->get('candidate_number');
        
        $student = null;
        $journey = [];
        $insights = [];
        $allCandidateNumbers = [];

        // If candidate_id is passed, get its name first to find all matching name records
        if ($candidateId) {
            $c = Candidate::find($candidateId);
            if ($c) {
                $selectedName = $c->candidate_name;
            }
        } elseif ($candidateNumber) {
            $c = Candidate::where('candidate_number', $candidateNumber)->first();
            if ($c) {
                $selectedName = $c->candidate_name;
            }
        }

        // Fetch unique names for the dropdown
        $candidates = Candidate::select('candidate_name')
            ->distinct()
            ->orderBy('candidate_name')
            ->get();

        if ($selectedName) {
            // Find all candidate records with this name
            $students = Candidate::where('candidate_name', $selectedName)->with('school')->get();
            if ($students->isNotEmpty()) {
                $student = $students->first();
                $allCandidateNumbers = $students->pluck('candidate_number')->unique()->toArray();
                $candidateIds = $students->pluck('id')->toArray();

                // Fetch results for all these candidate IDs
                $results = SubjectResult::whereIn('enrollment_id', function ($query) use ($candidateIds) {
                    $query->select('id')
                        ->from('candidate_enrollments')
                        ->whereIn('candidate_id', $candidateIds);
                })
                ->with(['subject.qualification', 'series', 'componentMarks.component'])
                ->get();

                // Sort results chronologically by Series
                $monthOrder = ['March' => 1, 'June' => 2, 'November' => 3];
                $sortedResults = $results->sort(function ($a, $b) use ($monthOrder) {
                    if ($a->series->year !== $b->series->year) {
                        return $a->series->year <=> $b->series->year;
                    }
                    $aMonth = $monthOrder[$a->series->month] ?? 99;
                    $bMonth = $monthOrder[$b->series->month] ?? 99;
                    return $aMonth <=> $bMonth;
                });

                // Group chronologically by Series
                $groupedResults = $sortedResults->groupBy(function ($res) {
                    return $res->series->id;
                });

                foreach ($groupedResults as $seriesId => $seriesResults) {
                    $series = $seriesResults->first()->series;
                    $avgPum = $seriesResults->avg('pum');
                    $journey[] = [
                        'series_id' => $series->id,
                        'series_name' => $series->series_name,
                        'year' => $series->year,
                        'month' => $series->month,
                        'avg_pum' => round($avgPum, 1),
                        'results' => $seriesResults,
                        'total_subjects' => $seriesResults->count(),
                        'best_grade' => $this->getBestGrade($seriesResults),
                        'worst_grade' => $this->getWorstGrade($seriesResults),
                        'qualifications' => $seriesResults->map(fn($r) => $r->subject->qualification->qualification_name)->unique()->values()->toArray(),
                        'pum_delta' => null,
                    ];
                }

                // Compute PUM delta (change from previous series)
                for ($i = 1; $i < count($journey); $i++) {
                    $journey[$i]['pum_delta'] = round($journey[$i]['avg_pum'] - $journey[$i-1]['avg_pum'], 1);
                }

                // Generate Insights
                $insights = $this->generateJourneyInsights($results, $journey);

                // Calculate overall stats for the combined student profiles
                $totalResultsCount = $results->count();
                $avgPumOverall = $totalResultsCount > 0 ? round($results->avg('pum'), 1) : 0;
                $bestGrade = 'N/A';
                if ($totalResultsCount > 0) {
                    $gradeOrder = ['A*' => 1, 'A' => 2, 'B' => 3, 'C' => 4, 'D' => 5, 'E' => 6, 'U' => 7];
                    $bestGrade = $results->sortBy(fn($r) => $gradeOrder[$r->grade] ?? 99)->first()->grade;
                }
                $passCountOverall = $results->where('is_passed', true)->count();
                $passRateOverall = $totalResultsCount > 0 ? round(($passCountOverall / $totalResultsCount) * 100, 0) : 0;
            }
        }

        return view('analysis.student-journey', [
            'candidates' => $candidates,
            'student' => $student,
            'journey' => $journey,
            'insights' => $insights,
            'selected_candidate_name' => $selectedName ?? '',
            'all_candidate_numbers' => $allCandidateNumbers,
            'candidate_number' => $candidateNumber ?? '',
            'total_results_count' => $totalResultsCount ?? 0,
            'avg_pum_overall' => $avgPumOverall ?? 0,
            'best_grade' => $bestGrade ?? 'N/A',
            'pass_rate_overall' => $passRateOverall ?? 0,
        ]);
    }

    /**
     * Generate analytical insights for a candidate's journey
     */
    private function generateJourneyInsights($results, $journey)
    {
        $insights = [];

        if ($results->isEmpty() || empty($journey)) {
            return $insights;
        }

        // 1. Overall Trajectory Insight
        if (count($journey) > 1) {
            $firstSeries = $journey[0];
            $latestSeries = end($journey);
            $diff = $latestSeries['avg_pum'] - $firstSeries['avg_pum'];

            if ($diff > 3) {
                $insights[] = [
                    'type' => 'improvement',
                    'title' => 'Positive Growth Trajectory',
                    'description' => "Average PUM improved by " . round($diff, 1) . "% (from {$firstSeries['avg_pum']}% in {$firstSeries['series_name']} to {$latestSeries['avg_pum']}% in {$latestSeries['series_name']}). This indicates strong progression and learning adaptation.",
                    'class' => 'bg-emerald-50 border-emerald-150 text-emerald-800'
                ];
            } elseif ($diff < -3) {
                $insights[] = [
                    'type' => 'decline',
                    'title' => 'Performance Trend Warning',
                    'description' => "Average PUM decreased by " . round(abs($diff), 1) . "% (from {$firstSeries['avg_pum']}% in {$firstSeries['series_name']} to {$latestSeries['avg_pum']}% in {$latestSeries['series_name']}). Focused support or review of study habits in advanced subjects is recommended.",
                    'class' => 'bg-rose-50 border-rose-150 text-rose-800'
                ];
            } else {
                $insights[] = [
                    'type' => 'stable',
                    'title' => 'Consistent Academic Standing',
                    'description' => "Maintained steady academic performance with consistent average PUM (approx {$latestSeries['avg_pum']}% in the latest {$latestSeries['series_name']} series compared to {$firstSeries['avg_pum']}% in {$firstSeries['series_name']}).",
                    'class' => 'bg-blue-50 border-blue-150 text-blue-800'
                ];
            }
        } else {
            $insights[] = [
                'type' => 'baseline',
                'title' => 'Baseline Performance Established',
                'description' => "Initial academic benchmark established in " . $journey[0]['series_name'] . " with an average PUM of " . $journey[0]['avg_pum'] . "%. Future series will map progression against this baseline.",
                'class' => 'bg-slate-50 border-slate-150 text-slate-800'
            ];
        }

        // 2. Strengths Insight
        $highestResult = $results->sortByDesc('pum')->first();
        if ($highestResult) {
            $insights[] = [
                'type' => 'strength',
                'title' => 'Core Subject Strength',
                'description' => "Demonstrates exceptional proficiency in **{$highestResult->subject->subject_name}** with a top PUM score of **{$highestResult->pum}%** (Grade {$highestResult->grade}) in {$highestResult->series->series_name}.",
                'class' => 'bg-indigo-50 border-indigo-150 text-indigo-800'
            ];
        }

        // 3. Subject-level Progression (e.g. from IGCSE to AS/A Level)
        $subjectGroups = [];
        foreach ($results as $res) {
            $normalizedName = trim(preg_replace('/\s*\([^)]*\)/', '', $res->subject->subject_name));
            $subjectGroups[$normalizedName][] = $res;
        }

        foreach ($subjectGroups as $subName => $subResults) {
            if (count($subResults) > 1) {
                $subResults = collect($subResults)->sortBy(function($r) {
                    $monthOrder = ['March' => 1, 'June' => 2, 'November' => 3];
                    return $r->series->year * 10 + ($monthOrder[$r->series->month] ?? 0);
                });

                $first = $subResults->first();
                $last = $subResults->last();

                if ($first->subject->qualification->qualification_type !== $last->subject->qualification->qualification_type) {
                    $insights[] = [
                        'type' => 'progression',
                        'title' => "Subject Progression: {$subName}",
                        'description' => "Successfully transitioned {$subName} from {$first->subject->qualification->qualification_type} (Grade: {$first->grade}, PUM: {$first->pum}%) to {$last->subject->qualification->qualification_type} (Grade: {$last->grade}, PUM: {$last->pum}%).",
                        'class' => 'bg-amber-50 border-amber-150 text-amber-800'
                    ];
                }
            }
        }

        return $insights;
    }


    private function calculateSubjectStats($results)
    {
        if ($results->isEmpty()) {
            return [
                'total_students' => 0,
                'avg_pum' => 0,
                'highest' => 0,
                'lowest' => 0,
                'pass_rate' => 0,
                'grade_distribution' => [],
            ];
        }

        return [
            'total_students' => $results->count(),
            'avg_pum' => $results->avg('pum'),
            'highest' => $results->max('pum'),
            'lowest' => $results->min('pum'),
            'pass_rate' => ($results->where('is_passed', true)->count() / $results->count()) * 100,
            'grade_distribution' => $results->groupBy('grade')->map->count(),
        ];
    }

    private function analyzeComponents($results)
    {
        $componentAnalysis = [];

        foreach ($results as $result) {
            foreach ($result->componentMarks as $mark) {
                $componentName = $mark->component->component_name;
                $componentCode = $mark->component->component_code;
                $uniqueName = "{$result->subject_id}_{$componentCode} - {$componentName}";
                
                if (!isset($componentAnalysis[$uniqueName])) {
                    $componentAnalysis[$uniqueName] = [
                        'code' => $componentCode,
                        'name' => $componentName,
                        'total_marks' => $mark->component->total_marks,
                        'subject_id' => $mark->component->subject_id,
                        'marks' => [],
                    ];
                }

                $componentAnalysis[$uniqueName]['marks'][] = [
                    'obtained' => $mark->obtained_marks,
                    'percentage' => $mark->percentage,
                ];
            }
        }

        // Calculate statistics for each component
        foreach ($componentAnalysis as &$comp) {
            $count = count($comp['marks']);
            $comp['candidate_count'] = $count;

            if ($count > 0) {
                $obtained = array_column($comp['marks'], 'obtained');
                $percentages = array_column($comp['marks'], 'percentage');

                $comp['avg_marks'] = array_sum($obtained) / $count;
                $comp['avg_percentage'] = array_sum($percentages) / $count;
                $comp['highest'] = max($obtained);
                $comp['lowest'] = min($obtained);

                // Median
                $sorted = $obtained;
                sort($sorted);
                $mid = intdiv($count, 2);
                $comp['median'] = ($count % 2 === 0)
                    ? ($sorted[$mid - 1] + $sorted[$mid]) / 2
                    : $sorted[$mid];

                // Standard deviation
                $mean = $comp['avg_marks'];
                $sumSquaredDiffs = array_sum(array_map(fn($v) => pow($v - $mean, 2), $obtained));
                $comp['std_dev'] = round(sqrt($sumSquaredDiffs / $count), 1);

                // Distribution: 5 percentage buckets
                $buckets = [0, 0, 0, 0, 0]; // 0-20, 20-40, 40-60, 60-80, 80-100
                foreach ($percentages as $pct) {
                    if ($pct >= 80) {
                        $buckets[4]++;
                    } elseif ($pct >= 60) {
                        $buckets[3]++;
                    } elseif ($pct >= 40) {
                        $buckets[2]++;
                    } elseif ($pct >= 20) {
                        $buckets[1]++;
                    } else {
                        $buckets[0]++;
                    }
                }
                $comp['distribution'] = $buckets;
            } else {
                $comp['avg_marks'] = 0;
                $comp['avg_percentage'] = 0;
                $comp['highest'] = 0;
                $comp['lowest'] = 0;
                $comp['median'] = 0;
                $comp['std_dev'] = 0;
                $comp['distribution'] = [0, 0, 0, 0, 0];
            }
        }

        return $componentAnalysis;
    }

    private function calculateTrends($subjectId, $yearRange)
    {
        $query = SubjectResult::whereHas('series', function ($q) use ($yearRange) {
            $q->whereBetween('year', $yearRange);
        });

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        return $query->with('series')
            ->get()
            ->groupBy('series.year')
            ->map(function ($yearResults) {
                return [
                    'avg_pum' => $yearResults->avg('pum'),
                    'pass_rate' => $yearResults->count() > 0 ? ($yearResults->where('is_passed', true)->count() / $yearResults->count()) * 100 : 0,
                    'total_students' => $yearResults->count(),
                ];
            })
            ->toArray();
    }

    private function buildStudentJourney($student)
    {
        $journey = [];

        $enrollments = $student->enrollments()
            ->with(['results.subject.qualification', 'results.series'])
            ->get();

        foreach ($enrollments->groupBy('qualification_id') as $qualId => $enrollmentsForQual) {
            $qualification = $enrollmentsForQual->first()->qualification;
            $results = $enrollmentsForQual->flatMap->results->sortBy('series.year');

            $journey[$qualification->qualification_name] = [
                'qualification' => $qualification,
                'results' => $results,
                'status' => $this->determineStatus($results),
                'trend' => $this->calculateTrendForJourney($results),
            ];
        }

        return $journey;
    }

    private function determineStatus($results)
    {
        if ($results->isEmpty()) {
            return 'NOT_STARTED';
        }

        $passCount = $results->where('is_passed', true)->count();
        if ($passCount === $results->count()) {
            return 'PASSED';
        } elseif ($passCount > 0) {
            return 'IN_PROGRESS';
        } else {
            return 'FAILED';
        }
    }

    private function calculateTrendForJourney($results)
    {
        return $results->pluck('pum')->toArray();
    }

    private function getBestGrade($results)
    {
        $gradeOrder = [
            'A*' => 1, 'A*A*' => 1,
            'A' => 2, 'AA' => 2, 'a' => 2,
            'B' => 3, 'BB' => 3, 'b' => 3,
            'C' => 4, 'CC' => 4, 'c' => 4,
            'D' => 5, 'DD' => 5, 'd' => 5,
            'E' => 6, 'EE' => 6, 'e' => 6,
            'F' => 7, 'FF' => 7,
            'G' => 8, 'GG' => 8,
            'U' => 9, 'UU' => 9,
            'X' => 10, 'Q' => 11
        ];
        return $results->sortBy(fn($r) => $gradeOrder[$r->grade] ?? 99)->first()->grade ?? 'N/A';
    }

    private function getWorstGrade($results)
    {
        $gradeOrder = [
            'A*' => 1, 'A*A*' => 1,
            'A' => 2, 'AA' => 2, 'a' => 2,
            'B' => 3, 'BB' => 3, 'b' => 3,
            'C' => 4, 'CC' => 4, 'c' => 4,
            'D' => 5, 'DD' => 5, 'd' => 5,
            'E' => 6, 'EE' => 6, 'e' => 6,
            'F' => 7, 'FF' => 7,
            'G' => 8, 'GG' => 8,
            'U' => 9, 'UU' => 9,
            'X' => 10, 'Q' => 11
        ];
        return $results->sortByDesc(fn($r) => $gradeOrder[$r->grade] ?? 99)->first()->grade ?? 'N/A';
    }
}
