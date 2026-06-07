<?php

namespace App\Http\Controllers;

use App\Models\SubjectResult;
use App\Models\Qualification;
use App\Models\Subject;
use App\Models\ExamSeries;
use Illuminate\Http\Request;

class ResultsController extends Controller
{
    /**
     * Show both upload and view result tabs
     */
    /**
     * Show both upload and view result tabs
     */
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $searchSeriesId = $request->input('series_id');

        $resultsQuery = SubjectResult::query()
            ->selectRaw('subject_id, series_id, COUNT(*) as candidate_count, AVG(pum) as average_pum, SUM(CASE WHEN is_passed = 1 THEN 1 ELSE 0 END) as passed_count')
            ->groupBy('subject_id', 'series_id')
            ->with(['subject.qualification', 'series']);

        if ($schoolId) {
            $resultsQuery->whereHas('enrollment.candidate', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }

        if ($searchSeriesId) {
            $resultsQuery->where('series_id', $searchSeriesId);
        }

        $allResults = $resultsQuery->get();

        // Group by series_id
        $seriesGroups = $allResults->groupBy('series_id')->map(function ($group) {
            $first = $group->first();
            $series = $first->series;
            if (!$series) return null;
            
            // Group by qualification within this series
            $qualifications = $group->groupBy(fn($r) => $r->subject->qualification_id)->map(function ($qualGroup) {
                $firstQual = $qualGroup->first();
                $qualification = $firstQual->subject->qualification;
                if (!$qualification) return null;
                
                // Aggregate stats
                $subjects = $qualGroup->map(function ($r) {
                    return [
                        'subject_id' => $r->subject_id,
                        'subject_name' => $r->subject->subject_name,
                        'subject_code' => $r->subject->subject_code,
                        'candidate_count' => $r->candidate_count,
                        'passed_count' => $r->passed_count,
                        'failed_count' => $r->candidate_count - $r->passed_count,
                        'average_pum' => round($r->average_pum, 1)
                    ];
                });

                // Calculate average PUM for qualification
                $totalCand = $qualGroup->sum('candidate_count');
                $avgPum = $qualGroup->avg('average_pum');

                return [
                    'qualification_id' => $qualification->id,
                    'qualification_name' => $qualification->type_display,
                    'subject_count' => $qualGroup->unique('subject_id')->count(),
                    'total_candidates' => $totalCand,
                    'average_pum' => $avgPum ? round($avgPum, 1) : null,
                    'subjects' => $subjects
                ];
            })->filter()->values();

            return [
                'series_id' => $series->id,
                'series_name' => $series->series_name,
                'year' => $series->year,
                'month' => $series->month,
                'qualifications' => $qualifications
            ];
        })->filter()->values();

        // Sort series groups chronologically
        $seriesGroups = $seriesGroups->sortByDesc(function ($item) {
            $monthOrder = match($item['month']) {
                'November' => 1,
                'June' => 2,
                'March' => 3,
                default => 4
            };
            return $item['year'] . '_' . $monthOrder;
        })->values();

        $qualifications = Qualification::all();
        $series = ExamSeries::all();

        return view('results.index', [
            'seriesGroups' => $seriesGroups,
            'qualifications' => $qualifications,
            'series' => $series,
            'selectedSeriesId' => $searchSeriesId,
        ]);
    }

    /**
     * Show qualifications and subjects registered for a specific series.
     */
    public function seriesDetails(ExamSeries $examSeries)
    {
        $schoolId = auth()->user()->school_id;

        $resultsQuery = SubjectResult::query()
            ->where('series_id', $examSeries->id)
            ->selectRaw('subject_id, series_id, COUNT(*) as candidate_count, AVG(pum) as average_pum, SUM(CASE WHEN is_passed = 1 THEN 1 ELSE 0 END) as passed_count')
            ->groupBy('subject_id', 'series_id')
            ->with(['subject.qualification', 'series']);

        if ($schoolId) {
            $resultsQuery->whereHas('enrollment.candidate', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }

        $allResults = $resultsQuery->get();

        $qualificationsData = $allResults->groupBy(fn($r) => $r->subject->qualification_id)->map(function ($qualGroup) {
            $firstQual = $qualGroup->first();
            $qualification = $firstQual->subject->qualification;
            if (!$qualification) return null;

            $subjects = $qualGroup->map(function ($r) {
                // Find one result model to check component marks exist
                $hasComponents = \App\Models\ComponentMarks::whereHas('subjectResult', function ($q) use ($r) {
                    $q->where('subject_id', $r->subject_id)->where('series_id', $r->series_id);
                })->exists();

                return [
                    'subject_id' => $r->subject_id,
                    'subject_name' => $r->subject->subject_name,
                    'subject_code' => $r->subject->subject_code,
                    'candidate_count' => $r->candidate_count,
                    'passed_count' => $r->passed_count,
                    'failed_count' => $r->candidate_count - $r->passed_count,
                    'average_pum' => round($r->average_pum, 1),
                    'pum_uploaded' => $r->average_pum !== null,
                    'components_uploaded' => $hasComponents,
                ];
            });

            $totalCand = $qualGroup->sum('candidate_count');
            $avgPum = $qualGroup->avg('average_pum');

            return [
                'qualification_id' => $qualification->id,
                'qualification_name' => $qualification->type_display,
                'subject_count' => $qualGroup->unique('subject_id')->count(),
                'total_candidates' => $totalCand,
                'average_pum' => $avgPum ? round($avgPum, 1) : null,
                'subjects' => $subjects
            ];
        })->filter()->values();

        return view('results.series-details', [
            'series' => $examSeries,
            'qualificationsData' => $qualificationsData,
        ]);
    }

    /**
     * Show candidate-wise results for a specific subject in a series.
     */
    public function subjectResults(ExamSeries $examSeries, Subject $subject)
    {
        $schoolId = auth()->user()->school_id;

        $query = SubjectResult::where('subject_results.series_id', $examSeries->id)
            ->where('subject_results.subject_id', $subject->id)
            ->join('candidate_enrollments', 'subject_results.enrollment_id', '=', 'candidate_enrollments.id')
            ->join('candidates', 'candidate_enrollments.candidate_id', '=', 'candidates.id')
            ->orderBy('candidates.candidate_number')
            ->select('subject_results.*')
            ->with(['enrollment.candidate', 'componentMarks.component']);

        if ($schoolId) {
            $query->whereHas('enrollment.candidate', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }

        $results = $query->paginate(20)->withQueryString();

        return view('results.subject-details', [
            'series'  => $examSeries,
            'subject' => $subject,
            'results' => $results,
        ]);
    }

    /**
     * Legacy / support endpoints
     */
    public function showUpload(Request $request)
    {
        return redirect()->route('manual-results.index');
    }

    public function storeUpload(\App\Http\Requests\UploadResultRequest $request)
    {
        // Delegate to ResultUploadController storeUploadResult method
        return app(ResultUploadController::class)->storeUploadResult($request);
    }

    public function view(Request $request)
    {
        return redirect()->route('results.index');
    }

    public function show(SubjectResult $result)
    {
        // Delegate to ViewResultsController show method
        return app(ViewResultsController::class)->show($result);
    }

    /**
     * Delete the subject result and associated component marks.
     */
    public function destroy(SubjectResult $result)
    {
        $schoolId = auth()->user()->school_id;
        $candidate = $result->enrollment->candidate;
        if ($schoolId && $candidate->school_id !== $schoolId) {
            abort(403, 'Unauthorized access.');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($result) {
            $result->componentMarks()->delete();
            $result->delete();
        });

        // Redirect to results index if the referrer was the show page of the deleted result
        $referrer = request()->headers->get('referer');
        $showUrl = route('results.show', $result);
        
        if ($referrer === $showUrl) {
            return redirect()->route('results.index')->with('success', 'Result record deleted successfully.');
        }

        return back()->with('success', 'Result record deleted successfully.');
    }

    /**
     * Delete all results of a subject in a series.
     */
    public function destroySubjectResults(ExamSeries $examSeries, Subject $subject)
    {
        $schoolId = auth()->user()->school_id;

        \Illuminate\Support\Facades\DB::transaction(function () use ($examSeries, $subject, $schoolId) {
            $query = SubjectResult::where('series_id', $examSeries->id)
                ->where('subject_id', $subject->id);

            if ($schoolId) {
                $query->whereHas('enrollment.candidate', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            }

            $results = $query->get();

            foreach ($results as $res) {
                $res->componentMarks()->delete();
                $res->delete();
            }
        });

        return redirect()->route('results.index')->with('success', 'All results for the subject in this series have been deleted.');
    }

    /**
     * Generate broadsheet view for a qualification and series.
     */
    public function broadsheet(Request $request)
    {
        $qualifications = Qualification::all();
        $years = collect(range(2026, 2018));
        
        $schoolId = auth()->user()->school_id;
        
        $seriesStats = collect();
        
        // Find all combinations of series_id and qualification_id that have enrollments with subject results
        $enrollmentsQuery = \App\Models\CandidateEnrollment::with(['series', 'qualification'])
            ->whereIn('enrollment_status', ['enrolled', 'completed', 'withdrawn'])
            ->whereHas('subjectResult');
            
        if ($schoolId) {
            $enrollmentsQuery->whereHas('candidate', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }
        
        $enrollments = $enrollmentsQuery->get();
        
        // Group by series_id AND qualification_id
        $grouped = $enrollments->groupBy(function($item) {
            return $item->series_id . '_' . $item->qualification_id;
        });
        
        foreach ($grouped as $key => $items) {
            $first = $items->first();
            $series = $first->series;
            $qualification = $first->qualification;
            
            if (!$series || !$qualification) continue;
            
            $qualId = $qualification->id;
            $seriesId = $series->id;
            
            // Count unique candidates
            $candCount = $items->unique('candidate_id')->count();
            
            // Get stats
            $resultsQuery = SubjectResult::where('series_id', $seriesId)
                ->whereHas('enrollment', function($q) use ($qualId, $schoolId) {
                    $q->where('qualification_id', $qualId);
                    if ($schoolId) {
                        $q->whereHas('candidate', function($qc) use ($schoolId) {
                            $qc->where('school_id', $schoolId);
                        });
                    }
                });
                
            $totalResults = $resultsQuery->count();
            $avgPum = $resultsQuery->avg('pum');
            $passCount = $resultsQuery->whereIn('grade', ['A*', 'A', 'B', 'C', 'a', 'b', 'c'])->count();
            $passRate = $totalResults > 0 ? round(($passCount / $totalResults) * 100, 1) : 0;
            
            $seriesStats->push([
                'series_id' => $seriesId,
                'qualification_id' => $qualId,
                'qualification_name' => $qualification->type_display, // e.g. "IGCSE" or "AS and A Level"
                'qualification_type' => $qualification->qualification_type,
                'month' => $series->month,
                'year' => $series->year,
                'series_code' => $series->series_code,
                'series_name' => $series->series_name,
                'candidate_count' => $candCount,
                'average_pum' => $avgPum ? round($avgPum, 1) : null,
                'pass_rate' => $passRate,
            ]);
        }
        
        // Sort seriesStats
        $seriesStats = $seriesStats->sortByDesc(function($item) {
            $monthOrder = match($item['month']) {
                'November' => 1,
                'June' => 2,
                'March' => 3,
                default => 4
            };
            return $item['year'] . '_' . $monthOrder;
        })->values();
        
        return view('results.broadsheet', compact('qualifications', 'years', 'seriesStats'));
    }

    public function broadsheetDetail($seriesId, $qualificationId)
    {
        $series = ExamSeries::findOrFail($seriesId);
        $qualification = Qualification::findOrFail($qualificationId);
        
        $schoolId = auth()->user()->school_id;
        
        // Get all candidates enrolled in this series + qualification
        $enrollments = \App\Models\CandidateEnrollment::with('candidate')
            ->where('series_id', $series->id)
            ->where('qualification_id', $qualification->id)
            ->whereIn('enrollment_status', ['enrolled', 'completed', 'withdrawn'])
            ->join('candidates', 'candidate_enrollments.candidate_id', '=', 'candidates.id')
            ->orderBy('candidates.candidate_number')
            ->select('candidate_enrollments.*')
            ->get()
            ->unique('candidate_id');
            
        if ($schoolId) {
            $enrollments = $enrollments->filter(fn($e) => $e->candidate->school_id == $schoolId);
        }
        
        // Get all subject results for these candidates belonging to this qualification
        $candidateIds = $enrollments->pluck('candidate_id')->toArray();
        $results = SubjectResult::where('series_id', $series->id)
            ->whereHas('subject', function($q) use ($qualification) {
                $q->where('qualification_id', $qualification->id);
            })
            ->whereHas('enrollment', function($q) use ($candidateIds) {
                $q->whereIn('candidate_id', $candidateIds);
            })
            ->with('enrollment')
            ->get();
            
        // Only keep enrollments that have at least one active result for this qualification
        $activeCandidateIds = $results->map(fn($r) => $r->enrollment->candidate_id)->unique()->toArray();
        $enrollments = $enrollments->filter(function($e) use ($activeCandidateIds) {
            return in_array($e->candidate_id, $activeCandidateIds);
        });
            
        // Filter subjects to only those with at least one grade
        $activeSubjectIds = $results->pluck('subject_id')->unique();
        $subjects = Subject::where('qualification_id', $qualification->id)
            ->whereIn('id', $activeSubjectIds)
            ->orderBy('subject_name')
            ->get();
            
        // Build present grades and stats map
        $presentGrades = $results->pluck('grade')
            ->filter()
            ->unique()
            ->sortBy(function($grade) {
                return match($grade) {
                    'A*' => 1, 'A*A*' => 2, 'A' => 3, 'AA' => 4,
                    'B' => 5, 'BB' => 6, 'C' => 7, 'CC' => 8,
                    'D' => 9, 'DD' => 10, 'E' => 11, 'EE' => 12,
                    'F' => 13, 'FF' => 14, 'G' => 15, 'GG' => 16,
                    'a' => 17, 'b' => 18, 'c' => 19, 'd' => 20, 'e' => 21,
                    'U' => 22, 'UU' => 23, 'u' => 24,
                    'Q' => 25, 'X' => 26,
                    default => 27
                };
            })
            ->values();

        $statsMap = [];
        $pumStats = [];
        
        foreach ($subjects as $sub) {
            $statsMap[$sub->id] = [];
            foreach ($presentGrades as $g) {
                $statsMap[$sub->id][$g] = 0;
            }
            $statsMap[$sub->id]['total_sat'] = 0;
            
            $pumStats[$sub->id] = [
                'highest' => 'N/A',
                'lowest' => 'N/A',
                'average' => 'N/A',
                'values' => []
            ];
        }
        
        $gradeMap = [];
        $pumMap = [];
        
        foreach ($results as $res) {
            $candId = $res->enrollment->candidate_id;
            $gradeMap[$candId][$res->subject_id] = $res->grade;
            
            // Grade count stats
            if (isset($statsMap[$res->subject_id])) {
                $statsMap[$res->subject_id][$res->grade]++;
                $statsMap[$res->subject_id]['total_sat']++;
            }
            
            // Determine PUM value
            $gradeLower = strtolower($res->grade ?? '');
            if (in_array($gradeLower, ['u', 'uu', 'x'])) {
                $val = 0.0;
            } else {
                $dbPum = (float)($res->pum ?? 0);
                $val = $dbPum > 0 ? $dbPum : null;
            }
            
            $pumMap[$candId][$res->subject_id] = ($val === null) ? 'N/A' : $val;
            
            if ($val !== null && isset($pumStats[$res->subject_id])) {
                $pumStats[$res->subject_id]['values'][] = $val;
            }
        }
        
        // Calculate PUM statistics max, min, avg
        foreach ($subjects as $sub) {
            $vals = $pumStats[$sub->id]['values'];
            if (count($vals) > 0) {
                $pumStats[$sub->id]['highest'] = max($vals);
                $pumStats[$sub->id]['lowest'] = min($vals);
                $pumStats[$sub->id]['average'] = round(array_sum($vals) / count($vals), 1);
            }
        }
        
        // Build candidate rows
        $candidates = $enrollments->map(function ($enrollment) use ($subjects, $gradeMap, $pumMap) {
            $grades = [];
            $pums = [];
            foreach ($subjects as $sub) {
                $grades[$sub->id] = $gradeMap[$enrollment->candidate_id][$sub->id] ?? null;
                $pums[$sub->id] = $pumMap[$enrollment->candidate_id][$sub->id] ?? null;
            }
            return [
                'candidate_no' => $enrollment->candidate->candidate_number,
                'candidate_name' => $enrollment->candidate->candidate_name,
                'grades' => $grades,
                'pums' => $pums,
            ];
        });
        
        return view('results.broadsheet_detail', compact(
            'qualification', 'series', 'subjects', 'candidates', 'presentGrades', 'statsMap', 'pumStats'
        ));
    }

    public function broadsheetExport($seriesId, $qualificationId)
    {
        $series = ExamSeries::findOrFail($seriesId);
        $qualification = Qualification::findOrFail($qualificationId);
        
        $schoolId = auth()->user()->school_id;
        
        // Get all candidates enrolled in this series + qualification
        $enrollments = \App\Models\CandidateEnrollment::with('candidate')
            ->where('series_id', $series->id)
            ->where('qualification_id', $qualification->id)
            ->whereIn('enrollment_status', ['enrolled', 'completed', 'withdrawn'])
            ->join('candidates', 'candidate_enrollments.candidate_id', '=', 'candidates.id')
            ->orderBy('candidates.candidate_number')
            ->select('candidate_enrollments.*')
            ->get()
            ->unique('candidate_id');
            
        if ($schoolId) {
            $enrollments = $enrollments->filter(fn($e) => $e->candidate->school_id == $schoolId);
        }
        
        // Get all subject results for these candidates belonging to this qualification
        $candidateIds = $enrollments->pluck('candidate_id')->toArray();
        $results = SubjectResult::where('series_id', $series->id)
            ->whereHas('subject', function($q) use ($qualification) {
                $q->where('qualification_id', $qualification->id);
            })
            ->whereHas('enrollment', function($q) use ($candidateIds) {
                $q->whereIn('candidate_id', $candidateIds);
            })
            ->with('enrollment')
            ->get();
            
        // Only keep enrollments that have at least one active result for this qualification
        $activeCandidateIds = $results->map(fn($r) => $r->enrollment->candidate_id)->unique()->toArray();
        $enrollments = $enrollments->filter(function($e) use ($activeCandidateIds) {
            return in_array($e->candidate_id, $activeCandidateIds);
        });
            
        // Filter subjects to only those with at least one grade
        $activeSubjectIds = $results->pluck('subject_id')->unique();
        $subjects = Subject::where('qualification_id', $qualification->id)
            ->whereIn('id', $activeSubjectIds)
            ->orderBy('subject_name')
            ->get();
            
        $gradeMap = [];
        $pumMap = [];
        
        foreach ($results as $res) {
            $candId = $res->enrollment->candidate_id;
            $gradeMap[$candId][$res->subject_id] = $res->grade;
            
            $gradeLower = strtolower($res->grade ?? '');
            if (in_array($gradeLower, ['u', 'uu', 'x'])) {
                $val = 0.0;
            } else {
                $dbPum = (float)($res->pum ?? 0);
                $val = $dbPum > 0 ? $dbPum : null;
            }
            $pumMap[$candId][$res->subject_id] = ($val === null) ? 'N/A' : $val;
        }
        
        $filename = "broadsheet_" . strtolower(str_replace(' ', '_', $qualification->type_display)) . "_" . strtolower($series->month) . "_" . $series->year . ".csv";
        
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];
        
        $callback = function() use ($enrollments, $subjects, $gradeMap, $pumMap) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for proper Excel encoding
            fputs($file, "\xEF\xBB\xBF");
            
            // Write column headers
            $headersRow = ['Candidate No', 'Candidate Name'];
            foreach ($subjects as $sub) {
                $headersRow[] = $sub->subject_name;
            }
            fputcsv($file, $headersRow);
            
            // Write candidate rows
            foreach ($enrollments as $enrollment) {
                $row = [
                    $enrollment->candidate->candidate_number,
                    $enrollment->candidate->candidate_name
                ];
                
                foreach ($subjects as $sub) {
                    $grade = $gradeMap[$enrollment->candidate_id][$sub->id] ?? null;
                    $pum = $pumMap[$enrollment->candidate_id][$sub->id] ?? null;
                    
                    if ($grade !== null) {
                        $displayGrade = in_array($grade, ['a', 'b', 'c', 'd', 'e']) ? $grade . '^' : $grade;
                        $displayPum = ($pum !== null && $pum !== 'N/A') ? "($pum)" : "";
                        $row[] = $displayGrade . $displayPum;
                    } else {
                        $row[] = '';
                    }
                }
                fputcsv($file, $row);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
