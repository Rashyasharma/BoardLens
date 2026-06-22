<?php

namespace App\Http\Controllers;

use App\Models\ExamSeries;
use App\Models\Qualification;
use App\Models\Subject;
use App\Models\Component;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use App\Models\ComponentMarks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManualResultsController extends Controller
{
    /**
     * Selector page: cascade Year → Month → Qualification → Subject.
     */
    public function index(Request $request)
    {
        $qualifications = Qualification::orderBy('qualification_type')->get();

        // Load years that have series
        $years = ExamSeries::distinct()->orderBy('year', 'desc')->pluck('year');

        $selectedYear  = $request->year;
        $selectedMonth = $request->month;
        $selectedQual  = $request->qual;
        $selectedSubject = $request->subject;

        // Available series for selected year
        $seriesOptions = collect();
        if ($selectedYear) {
            $seriesOptions = ExamSeries::where('year', $selectedYear)
                ->when($selectedMonth, fn($q) => $q->where('month', $selectedMonth))
                ->orderByRaw("CASE month WHEN 'November' THEN 1 WHEN 'June' THEN 2 WHEN 'March' THEN 3 END")
                ->get()
                ->map(fn($s) => [
                    'id'    => $s->id,
                    'month' => $s->month,
                    'label' => $this->monthLabel($s->month),
                ]);
        }

        // Available subjects for selected qual
        $subjectOptions = collect();
        if ($selectedQual) {
            $subjectOptions = Subject::where('qualification_id', $selectedQual)
                ->orderBy('subject_name')
                ->get(['id', 'subject_code', 'subject_name']);
        }

        // If all selected, find the matching series
        $selectedSeries = null;
        if ($selectedYear && $selectedMonth) {
            $selectedSeries = ExamSeries::where('year', $selectedYear)
                ->where('month', $selectedMonth)
                ->first();
        }

        return view('manual-results.index', compact(
            'qualifications', 'years', 'seriesOptions', 'subjectOptions',
            'selectedYear', 'selectedMonth', 'selectedQual', 'selectedSubject', 'selectedSeries'
        ));
    }

    /**
     * Show the results entry grid for a specific series + subject.
     */
    public function show(Request $request, ExamSeries $examSeries, Subject $subject)
    {
        $series  = $examSeries;
        $subject = $subject->load(['components', 'qualification']);

        $enrollments = CandidateEnrollment::with('candidate')
            ->where('series_id', $series->id)
            ->where('subject_id', $subject->id)
            ->join('candidates', 'candidate_enrollments.candidate_id', '=', 'candidates.id')
            ->orderBy('candidates.candidate_number')
            ->select('candidate_enrollments.*')
            ->get();

        // Fetch components for this subject based on the series year
        $componentSet = \App\Models\ComponentSet::findForSubjectYear($subject->id, $series->year);
        $components = collect();
        
        if ($componentSet) {
            $components = $componentSet->components()->orderBy('component_code')->get();
        }

        $grades = $subject->qualification->qualification_type === 'AS_A_LEVEL'
            ? ['A*', 'A', 'B', 'C', 'D', 'E', 'a', 'b', 'c', 'd', 'e', 'U']
            : ['A*', 'A*A*', 'A', 'AA', 'B', 'BB', 'C', 'CC', 'D', 'DD', 'E', 'EE', 'F', 'FF', 'G', 'GG', 'U', 'UU'];

        $candidateIds = $enrollments->pluck('candidate_id')->toArray();
        $subjectResults = SubjectResult::with('componentMarks.component')
            ->where('series_id', $series->id)
            ->where('subject_id', $subject->id)
            ->whereHas('enrollment', function($q) use ($candidateIds) {
                $q->whereIn('candidate_id', $candidateIds);
            })
            ->get()
            ->keyBy('enrollment_id');

        $rows = $enrollments->map(function ($enrollment) use ($components, $subjectResults) {
            $result = $subjectResults->get($enrollment->id);

            // Build component mark map: component_id => obtained_marks / is_applicable
            $componentMarkMap = [];
            if ($result) {
                foreach ($result->componentMarks as $cm) {
                    $componentMarkMap[$cm->component_id] = [
                        'obtained' => $cm->obtained_marks,
                        'grade'    => $cm->grade,
                        'applicable' => true,
                    ];
                }
            }

            $componentRows = $components->map(function ($comp) use ($componentMarkMap, $result) {
                $existing = $componentMarkMap[$comp->id] ?? null;
                return [
                    'component_id'   => $comp->id,
                    'component_code' => $comp->component_code,
                    'component_name' => $comp->component_name,
                    'max_marks'      => $comp->total_marks,
                    'obtained'       => $existing ? $existing['obtained'] : null,
                    'component_grade'=> $existing ? ($existing['grade'] ?? null) : null,
                    'applicable'     => $result ? ($existing ? true : false) : true,
                ];
            });

            return [
                'enrollment_id'  => $enrollment->id,
                'candidate_id'   => $enrollment->candidate_id,
                'candidate_no'   => $enrollment->candidate->candidate_number,
                'candidate_name' => $enrollment->candidate->candidate_name,
                'not_opted'      => false,
                'grade'          => $result?->grade ?? '',
                'pum'            => $result?->pum ?? '',
                'result_id'      => $result?->id,
                'components'     => $componentRows,
            ];
        });

        return view('manual-results.show', compact(
            'series', 'subject', 'components', 'rows', 'grades'
        ));
    }

    /**
     * Save a single candidate's result row (called via AJAX).
     * POST /manual-results/{series}/{subject}/save
     */
    public function saveRow(Request $request, ExamSeries $examSeries, Subject $subject)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:candidate_enrollments,id',
            'not_opted'     => 'boolean',
            'grade'         => 'nullable|in:A*,A*A*,A,AA,B,BB,C,CC,D,DD,E,EE,F,FF,G,GG,U,UU,a,b,c,d,e,u',
            'pum'           => 'nullable|numeric|min:0|max:100',
            'components'    => 'nullable|array',
            'components.*.component_id'    => 'exists:components,id',
            'components.*.obtained'        => 'nullable|numeric|min:0',
            'components.*.applicable'      => 'boolean',
            'components.*.component_grade' => 'nullable|string|max:10',
        ]);

        $enrollment = CandidateEnrollment::findOrFail($request->enrollment_id);

        // Check if grade, PUM, or component marks were entered
        $hasGrade = $request->filled('grade');
        $hasPum = $request->filled('pum');
        $hasComponentMarks = false;
        if ($request->filled('components')) {
            foreach ($request->components as $c) {
                if (($c['applicable'] ?? false) && isset($c['obtained']) && $c['obtained'] !== null && $c['obtained'] !== '') {
                    $hasComponentMarks = true;
                    break;
                }
            }
        }

        $hasData = $hasGrade || $hasPum || $hasComponentMarks;

        // Handle "Not Opted" or completely empty row — remove any existing result
        if ($request->boolean('not_opted') || !$hasData) {
            $existingResult = SubjectResult::where('enrollment_id', $enrollment->id)
                ->where('subject_id', $subject->id)
                ->first();
            if ($existingResult) {
                $existingResult->componentMarks()->delete();
                $existingResult->delete();
            }
            return response()->json(['success' => true, 'message' => 'Marked as not opted or cleared.']);
        }

        DB::transaction(function () use ($request, $enrollment, $examSeries, $subject) {
            // Calculate total from applicable components
            $totalObtained = 0;
            $totalMax      = 0;
            $componentData = $request->components ?? [];

            foreach ($componentData as $c) {
                if ($c['applicable'] ?? false) {
                    $totalObtained += (float)($c['obtained'] ?? 0);
                    $comp = \App\Models\Component::find($c['component_id']);
                    if ($comp) $totalMax += $comp->total_marks;
                }
            }

            $pum        = $request->filled('pum') ? $request->pum : null;
            $grade      = $request->filled('grade') ? $request->grade : null;
            $isPassed   = in_array($grade, ['A*', 'A*A*', 'A', 'AA', 'B', 'BB', 'C', 'CC', 'D', 'DD', 'E', 'EE', 'F', 'FF', 'G', 'GG', 'a', 'b', 'c', 'd', 'e']);
            $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : null;

            $result = SubjectResult::updateOrCreate(
                ['enrollment_id' => $enrollment->id],
                [
                    'subject_id'              => $subject->id,
                    'series_id'               => $examSeries->id,
                    'grade'                   => $grade ?? 'U',
                    'pum'                     => $pum ?? 0,
                    'total_obtained_marks'    => $totalObtained ?: null,
                    'total_marks'             => $totalMax ?: null,
                    'overall_percentage'      => $percentage,
                    'is_passed'               => $isPassed,
                    'status'                  => count($componentData) > 0 ? 'component_marks_added' : 'pending_components',
                    'uploaded_by'             => Auth::id() ?? 1,
                    'result_uploaded_at'      => now(),
                ]
            );

            // Save component marks
            foreach ($componentData as $c) {
                $compId     = $c['component_id'];
                $applicable = $c['applicable'] ?? false;
                $obtained   = (float)($c['obtained'] ?? 0);
                $comp       = \App\Models\Component::find($compId);
                $maxMarks   = $comp ? $comp->total_marks : 0;
                $pct        = $maxMarks > 0 ? round(($obtained / $maxMarks) * 100, 2) : 0;

                if ($applicable) {
                    ComponentMarks::updateOrCreate(
                        [
                            'subject_result_id' => $result->id,
                            'component_id'      => $compId,
                        ],
                        [
                            'enrollment_id'  => $enrollment->id,
                            'obtained_marks' => $obtained,
                            'total_marks'    => $maxMarks,
                            'percentage'     => $pct,
                            'grade'          => $c['component_grade'] ?? null,
                            'uploaded_by'    => Auth::id() ?? 1,
                            'uploaded_at'    => now(),
                        ]
                    );
                } else {
                    // Remove component mark if toggled off
                    ComponentMarks::where('subject_result_id', $result->id)
                        ->where('component_id', $compId)
                        ->delete();
                }
            }
        });

        return response()->json(['success' => true, 'message' => 'Result saved.']);
    }

    /**
     * API: Get months with existing series for a given year.
     */
    public function apiMonths(Request $request)
    {
        $months = ExamSeries::where('year', $request->year)
            ->distinct()
            ->pluck('month');

        return response()->json($months);
    }

    /**
     * API: Get subjects.
     */
    public function apiSubjects(ExamSeries $examSeries)
    {
        $qualId = request('qualification_id');
        $subjects = Subject::when($qualId, fn($q) => $q->where('qualification_id', $qualId))
            ->orderBy('subject_name')
            ->get(['id', 'subject_code', 'subject_name']);

        return response()->json($subjects);
    }

    private function monthLabel(string $month): string
    {
        return match($month) {
            'March'    => 'February/March',
            'June'     => 'May/June',
            'November' => 'October/November',
            default    => $month,
        };
    }
}
