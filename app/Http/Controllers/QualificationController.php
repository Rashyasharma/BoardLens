<?php

namespace App\Http\Controllers;

use App\Models\Qualification;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QualificationController extends Controller
{
    /**
     * Show all qualifications with subjects
     */
    public function index()
    {
        $qualifications = Qualification::with(['subjects.components', 'subjects.results'])
            ->get()
            ->map(function ($qual) {
                return [
                    'id' => $qual->id,
                    'name' => $qual->qualification_name,
                    'type' => $qual->type_display,
                    'qualification_type' => $qual->qualification_type,
                    'subjects_with_stats' => $qual->subjectsWithStats(),
                ];
            });

        return view('qualifications.index', [
            'qualifications' => $qualifications,
        ]);
    }

    /**
     * Show single qualification with tabs
     */
    public function show(Qualification $qualification)
    {
        $subjects_with_stats = collect($qualification->subjectsWithStats())
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->toArray();

        return view('qualifications.show', [
            'qualification' => $qualification,
            'subjects_with_stats' => $subjects_with_stats,
        ]);
    }

    /**
     * Create new qualification
     */
    public function create()
    {
        return view('qualifications.create');
    }

    /**
     * Store qualification
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'qualification_type' => 'required|unique:qualifications|in:IGCSE,AS_A_LEVEL',
            'qualification_name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        Qualification::create($validated);

        return redirect()->route('qualifications.index')
            ->with('success', 'Qualification created successfully');
    }

    /**
     * Edit qualification
     */
    public function edit(Qualification $qualification)
    {
        return view('qualifications.edit', ['qualification' => $qualification]);
    }

    /**
     * Update qualification
     */
    public function update(Request $request, Qualification $qualification)
    {
        $validated = $request->validate([
            'qualification_name' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $qualification->update($validated);

        return redirect()->route('qualifications.show', $qualification)
            ->with('success', 'Qualification updated successfully');
    }

    /**
     * Delete qualification
     */
    public function destroy(Qualification $qualification)
    {
        $qualification->delete();

        return redirect()->route('qualifications.index')
            ->with('success', 'Qualification deleted successfully');
    }

    /**
     * Show all subjects with filter
     */
    public function subjectsIndex()
    {
        $qualifications = Qualification::with(['subjects.components', 'subjects.results'])
            ->get()
            ->map(function ($qual) {
                return [
                    'id' => $qual->id,
                    'name' => $qual->qualification_name,
                    'type' => $qual->type_display,
                    'qualification_type' => $qual->qualification_type,
                    'subjects_with_stats' => $qual->subjectsWithStats(),
                ];
            });

        return view('subjects.index', [
            'qualifications' => $qualifications,
        ]);
    }

    /**
     * Show create subject form
     */
    public function createSubject()
    {
        $qualifications = Qualification::all();
        $levels = \App\Models\Level::all();
        return view('qualifications.create_subject', compact('qualifications', 'levels'));
    }

    /**
     * Store new subject and components
     */
    public function storeSubjectAndComponents(Request $request)
    {
        $validated = $request->validate([
            'qualification_id' => 'required|exists:qualifications,id',
            'subject_code' => [
                'required',
                'string',
                Rule::unique('subjects')->where(function ($query) use ($request) {
                    return $query->where('qualification_id', $request->qualification_id);
                })
            ],
            'subject_name' => 'required|string',
            'components' => 'nullable|array',
            'components.*.code' => 'required|string',
            'components.*.name' => 'required|string',
            'components.*.marks' => 'required|integer|min:1',
            'components.*.level_id' => 'nullable|exists:levels,id',
        ]);

        $subject = Subject::create([
            'qualification_id' => $validated['qualification_id'],
            'subject_code' => $validated['subject_code'],
            'subject_name' => $validated['subject_name'],
            'total_marks' => 0,
            'passing_percentage' => 40.00,
        ]);

        $totalMarks = 0;
        if (!empty($validated['components'])) {
            foreach ($validated['components'] as $comp) {
                \App\Models\Component::create([
                    'subject_id' => $subject->id,
                    'component_code' => $comp['code'],
                    'component_name' => $comp['name'],
                    'component_type' => 'paper',
                    'total_marks' => $comp['marks'],
                    'scaling_factor' => 1,
                    'is_mandatory' => true,
                    'level_id' => $comp['level_id'] ?? null,
                ]);
                $totalMarks += $comp['marks'];
            }
        }
        $subject->update(['total_marks' => $totalMarks]);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject and components created successfully');
    }

    public function editSubject(Subject $subject)
    {
        $subject->load(['components', 'qualification']);
        
        $results = $subject->results()->latest()->get();
        $totalStudents = $results->count();
        
        $gradeDistribution = $subject->results()
            ->groupBy('grade')
            ->selectRaw('grade, COUNT(*) as count')
            ->pluck('count', 'grade')
            ->toArray();
            
        $statistics = null;
        if ($results->isNotEmpty()) {
            $passedCount = $results->where('is_passed', true)->count();
            $statistics = [
                'pass_rate' => ($passedCount / $totalStudents) * 100,
                'avg_pum' => $results->avg('pum'),
                'highest' => $results->max('pum'),
                'lowest' => $results->min('pum'),
            ];
        }

        $levels = \App\Models\Level::all();

        return view('qualifications.edit_subject', [
            'subject' => $subject,
            'total_students' => $totalStudents,
            'grade_distribution' => $gradeDistribution,
            'statistics' => $statistics,
            'levels' => $levels,
        ]);
    }

    /**
     * Update subject and components
     */
    public function updateSubjectAndComponents(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'subject_code' => [
                'required',
                'string',
                Rule::unique('subjects')->where(function ($query) use ($subject) {
                    return $query->where('qualification_id', $subject->qualification_id);
                })->ignore($subject->id)
            ],
            'subject_name' => 'required|string',
            'components' => 'required|array|min:1',
            'components.*.id' => 'nullable|exists:components,id',
            'components.*.code' => 'required|string',
            'components.*.name' => 'required|string',
            'components.*.marks' => 'required|integer|min:1',
            'components.*.level_id' => 'nullable|exists:levels,id',
        ]);

        $subjectCode = $validated['subject_code'];
        $subjectName = $validated['subject_name'];
        $componentsData = $validated['components'];

        // Validate unique codes in the request array
        $codes = array_column($componentsData, 'code');
        if (count($codes) !== count(array_unique($codes))) {
            return back()->withErrors(['components' => 'Each component must have a unique code.'])->withInput();
        }

        $subject->update([
            'subject_code' => $subjectCode,
            'subject_name' => $subjectName,
        ]);

        // Delete components that were removed in the frontend
        $submittedIds = collect($componentsData)->pluck('id')->filter()->toArray();
        $subject->components()->whereNotIn('id', $submittedIds)->delete();

        // Temporarily rename remaining components' codes to avoid unique constraint conflicts when swapping codes
        foreach ($subject->components()->whereIn('id', $submittedIds)->get() as $tempComp) {
            $tempComp->update(['component_code' => 'temp_' . uniqid() . '_' . $tempComp->component_code]);
        }

        $totalMarks = 0;
        foreach ($componentsData as $compData) {
            if (!empty($compData['id'])) {
                // Update existing component
                $component = \App\Models\Component::findOrFail($compData['id']);
                $component->update([
                    'component_code' => $compData['code'],
                    'component_name' => $compData['name'],
                    'total_marks' => $compData['marks'],
                    'level_id' => $compData['level_id'] ?? null,
                ]);
            } else {
                // Create new component
                \App\Models\Component::create([
                    'subject_id' => $subject->id,
                    'component_code' => $compData['code'],
                    'component_name' => $compData['name'],
                    'component_type' => 'paper',
                    'total_marks' => $compData['marks'],
                    'scaling_factor' => 1,
                    'is_mandatory' => true,
                    'level_id' => $compData['level_id'] ?? null,
                ]);
            }
            $totalMarks += $compData['marks'];
        }

        // Update subject's total marks cache
        $subject->update(['total_marks' => $totalMarks]);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject and components updated successfully');
    }

    /**
     * Delete subject
     */
    public function destroySubject(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully');
    }

    /**
     * Get subject details (AJAX)
     */
    public function getSubjectDetails(Request $request)
    {
        $subject = Subject::with('components', 'results')->findOrFail($request->subject_id);

        $stats = [
            'total_students' => $subject->results->count(),
            'grade_distribution' => $subject->results->groupBy('grade')->map->count(),
            'avg_pum' => $subject->results->avg('pum'),
            'pass_rate' => $subject->results->count() > 0 ? ($subject->results->where('is_passed', true)->count() / $subject->results->count()) * 100 : 0,
            'highest' => $subject->results->max('pum'),
            'lowest' => $subject->results->min('pum'),
        ];

        return response()->json([
            'subject' => $subject,
            'stats' => $stats,
        ]);
    }
}
