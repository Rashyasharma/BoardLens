<?php

namespace App\Http\Controllers;

use App\Models\SubjectResult;
use App\Models\ExamSeries;
use App\Models\Subject;
use App\Models\Component;
use App\Models\ComponentMarks;
use Illuminate\Http\Request;

class ViewResultsController extends Controller
{
    /**
     * Show view results page
     */
    public function index(Request $request)
    {
        $schoolId = auth()->user()?->school_id;
        
        $query = SubjectResult::query()
            ->with(['enrollment.candidate', 'subject', 'series', 'componentMarks.component']);

        // Scope to school
        if ($schoolId) {
            $query->whereHas('enrollment.candidate', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }

        // Apply filters
        if ($request->filled('year')) {
            $query->filterByYear($request->year);
        }

        if ($request->filled('month')) {
            $query->filterByMonth($request->month);
        }

        if ($request->filled('subject_id')) {
            $query->filterBySubject($request->subject_id);
        }

        if ($request->filled('series_id')) {
            $query->filterBySeries($request->series_id);
        }

        if ($request->filled('grade')) {
            $g = $request->grade;
            if ($g === 'QX') {
                $query->whereIn('grade', ['Q', 'X']);
            } elseif ($g === 'U') {
                $query->whereIn('grade', ['U', 'UU']);
            } else {
                $query->where('grade', $g);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $results = $query->paginate(20)->withQueryString();

        // Get filter options
        $years = collect(range(2026, 2018));
        $subjects = Subject::all();

        return view('results.view', [
            'results' => $results,
            'years' => $years,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Show single result with component breakdown
     */
    public function show(SubjectResult $result)
    {
        // Scope check
        $schoolId = auth()->user()?->school_id;
        $candidate = $result->enrollment->candidate;
        if ($schoolId && $candidate->school_id !== $schoolId) {
            abort(403, 'Unauthorized access to result record.');
        }

        $componentBreakdown = $result->componentMarks()
            ->with('component')
            ->get()
            ->map(function ($mark) {
                return [
                    'component_code' => $mark->component->component_code,
                    'component_name' => $mark->component->component_name,
                    'obtained_marks' => $mark->obtained_marks,
                    'total_marks' => $mark->total_marks,
                    'percentage' => round($mark->percentage, 2),
                ];
            });

        return view('results.show', [
            'result' => $result,
            'componentBreakdown' => $componentBreakdown,
        ]);
    }

    /**
     * Add/Edit component marks for a result (form)
     */
    public function editComponents(SubjectResult $result)
    {
        // Scope check
        $schoolId = auth()->user()?->school_id;
        $candidate = $result->enrollment->candidate;
        if ($schoolId && $candidate->school_id !== $schoolId) {
            abort(403, 'Unauthorized access to result record.');
        }

        $componentSet = \App\Models\ComponentSet::findForSubjectYear($result->subject_id, $result->series->year);
        $components = $componentSet ? $componentSet->components()->orderBy('component_code')->get() : collect();
        $existingMarks = $result->componentMarks()
            ->with('component')
            ->get()
            ->keyBy('component_id')
            ->toArray();

        return view('results.edit-components', [
            'result' => $result,
            'components' => $components,
            'existingMarks' => $existingMarks,
        ]);
    }

    /**
     * Store component marks (individual)
     */
    public function storeComponent(SubjectResult $result, Request $request)
    {
        // Scope check
        $schoolId = auth()->user()?->school_id;
        $candidate = $result->enrollment->candidate;
        if ($schoolId && $candidate->school_id !== $schoolId) {
            abort(403, 'Unauthorized access to result record.');
        }

        $request->validate([
            'component_id' => 'required|exists:components,id',
            'obtained_marks' => 'required|numeric|min:0',
        ]);

        try {
            $componentId = $request->component_id;
            $obtainedMarks = (float)$request->obtained_marks;

            $component = $result->subject->components()->findOrFail($componentId);

            if ($obtainedMarks > $component->total_marks) {
                return response()->json(['error' => "Marks exceed component total of {$component->total_marks}"], 422);
            }

            ComponentMarks::updateOrCreate(
                [
                    'subject_result_id' => $result->id,
                    'enrollment_id' => $result->enrollment_id,
                    'component_id' => $componentId,
                ],
                [
                    'obtained_marks' => $obtainedMarks,
                    'total_marks' => $component->total_marks,
                    'uploaded_by' => auth()->id(),
                ]
            );

            // Check if all components uploaded
            $result->load('componentMarks');
            if ($result->hasAllComponentsUploaded()) {
                $result->calculateFromComponents();
            }

            return response()->json(['message' => 'Component marks updated successfully']);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
