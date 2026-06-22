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
            'components.*.label' => 'nullable|string',
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
            $defaultSet = \App\Models\ComponentSet::create([
                'subject_id' => $subject->id,
                'start_year' => null,
                'end_year' => null,
                'label' => 'Default',
                'is_default' => true,
            ]);

            foreach ($validated['components'] as $comp) {
                \App\Models\Component::create([
                    'subject_id' => $subject->id,
                    'component_set_id' => $defaultSet->id,
                    'component_code' => $comp['code'],
                    'component_name' => $comp['name'],
                    'component_label' => $comp['label'] ?? null,
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

    public function editSubject(Subject $subject, Request $request)
    {
        $subject->load(['qualification']);
        
        // Load all component sets sorted chronologically by start_year (oldest → newest)
        $componentSets = \App\Models\ComponentSet::where('subject_id', $subject->id)
            ->with(['components' => function ($q) {
                $q->orderBy('component_code');
            }])
            ->orderBy('start_year', 'asc')
            ->orderBy('end_year', 'asc')
            ->get();

        // If no component sets exist yet, create a default one
        if ($componentSets->isEmpty()) {
            $defaultSet = \App\Models\ComponentSet::create([
                'subject_id' => $subject->id,
                'start_year' => null,
                'end_year' => null,
                'label' => 'Default',
                'is_default' => true,
            ]);
            $componentSets = collect([$defaultSet->load('components')]);
        }

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
            $pumResults = $results->filter(function ($r) {
                return $r->pum !== null && $r->pum > 0;
            });
            $statistics = [
                'pass_rate' => ($passedCount / $totalStudents) * 100,
                'avg_pum' => $pumResults->isNotEmpty() ? $pumResults->avg('pum') : 0,
                'highest' => $pumResults->isNotEmpty() ? $pumResults->max('pum') : 0,
                'lowest' => $pumResults->isNotEmpty() ? $pumResults->min('pum') : 0,
            ];
        }

        $levels = \App\Models\Level::all();

        // Get existing year ranges for overlap validation
        $existingRanges = $componentSets
            ->where('is_default', false)
            ->map(fn($set) => [
                'id' => $set->id,
                'start_year' => $set->start_year,
                'end_year' => $set->end_year,
            ])
            ->values()
            ->toArray();

        return view('qualifications.edit_subject', [
            'subject' => $subject,
            'componentSets' => $componentSets,
            'total_students' => $totalStudents,
            'grade_distribution' => $gradeDistribution,
            'statistics' => $statistics,
            'levels' => $levels,
            'existingRanges' => $existingRanges,
        ]);
    }

    /**
     * Update subject name and code only
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
        ]);

        $subject->update([
            'subject_code' => $validated['subject_code'],
            'subject_name' => $validated['subject_name'],
        ]);

        return redirect()->route('subjects.edit', $subject->id)
            ->with('success', 'Subject details updated successfully.');
    }

    /**
     * Store a new component set with year range (AJAX)
     */
    public function storeComponentSet(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'start_year' => 'required|integer|min:2000|max:2100',
            'end_year' => 'nullable|integer|min:2000|max:2100|gte:start_year',
            'copy_from_set_id' => 'nullable|exists:component_sets,id',
        ]);

        $startYear = $validated['start_year'];
        $endYear = $validated['end_year'] ?? null;

        // Server-side overlap validation
        $existingSets = \App\Models\ComponentSet::where('subject_id', $subject->id)
            ->where('is_default', false)
            ->get();

        foreach ($existingSets as $existing) {
            if ($existing->overlapsWith($startYear, $endYear)) {
                return response()->json([
                    'success' => false,
                    'message' => "Year range overlaps with existing set: {$existing->display_label}",
                ], 422);
            }
        }

        $label = $startYear . ' – ' . ($endYear ?? 'Present');

        $newSet = \App\Models\ComponentSet::create([
            'subject_id' => $subject->id,
            'start_year' => $startYear,
            'end_year' => $endYear,
            'label' => $label,
            'is_default' => false,
        ]);

        // Copy components from source set (or default — prefer default as base template)
        $sourceSetId = $validated['copy_from_set_id'] ?? null;
        if (!$sourceSetId) {
            // First try the default set as the base template
            $sourceSet = \App\Models\ComponentSet::where('subject_id', $subject->id)
                ->where('is_default', true)
                ->first();
            // Fall back to the most recent non-default set if no default
            if (!$sourceSet) {
                $sourceSet = \App\Models\ComponentSet::where('subject_id', $subject->id)
                    ->where('id', '!=', $newSet->id)
                    ->where('is_default', false)
                    ->orderBy('start_year', 'desc')
                    ->first();
            }
            $sourceSetId = $sourceSet?->id;
        }

        if ($sourceSetId) {
            $sourceComponents = \App\Models\Component::where('component_set_id', $sourceSetId)->get();
            foreach ($sourceComponents as $comp) {
                \App\Models\Component::create([
                    'subject_id' => $subject->id,
                    'component_set_id' => $newSet->id,
                    'component_code' => $comp->component_code,
                    'component_name' => $comp->component_name,
                    'component_label' => $comp->component_label,
                    'component_type' => $comp->component_type,
                    'total_marks' => $comp->total_marks,
                    'scaling_factor' => $comp->scaling_factor,
                    'is_mandatory' => $comp->is_mandatory,
                    'level_id' => $comp->level_id,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Component set '{$label}' created successfully.",
            'redirect' => route('subjects.edit', $subject->id),
        ]);
    }

    /**
     * Update components within a specific set (AJAX)
     */
    public function updateComponentSet(Request $request, Subject $subject, \App\Models\ComponentSet $componentSet)
    {
        $validated = $request->validate([
            'start_year' => 'nullable|integer|min:2000|max:2100',
            'end_year'   => 'nullable|integer|min:2000|max:2100|gte:start_year',
            'components' => 'required|array|min:1',
            'components.*.id' => 'nullable|exists:components,id',
            'components.*.code' => 'required|string',
            'components.*.name' => 'required|string',
            'components.*.label' => 'nullable|string',
            'components.*.marks' => 'required|integer|min:1',
            'components.*.level_id' => 'nullable|exists:levels,id',
        ]);

        $componentsData = $validated['components'];

        // Validate unique codes
        $codes = array_column($componentsData, 'code');
        if (count($codes) !== count(array_unique($codes))) {
            return response()->json([
                'success' => false,
                'message' => 'Each component must have a unique code.',
            ], 422);
        }

        // Update year range if provided (non-default sets only)
        if (!$componentSet->is_default && array_key_exists('start_year', $validated)) {
            $startYear = $validated['start_year'];
            $endYear   = $validated['end_year'] ?? null;

            // Overlap check — exclude this set itself
            $existingSets = \App\Models\ComponentSet::where('subject_id', $subject->id)
                ->where('is_default', false)
                ->where('id', '!=', $componentSet->id)
                ->get();

            foreach ($existingSets as $existing) {
                if ($existing->overlapsWith($startYear, $endYear)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Year range overlaps with existing set: {$existing->display_label}",
                    ], 422);
                }
            }

            $newLabel = $startYear . ' – ' . ($endYear ?? 'Present');
            $componentSet->update([
                'start_year' => $startYear,
                'end_year'   => $endYear,
                'label'      => $newLabel,
            ]);
        } elseif ($componentSet->is_default && array_key_exists('start_year', $validated) && $validated['start_year']) {
            // Allow updating year range even for the "default" set
            $startYear = $validated['start_year'];
            $endYear   = $validated['end_year'] ?? null;
            $newLabel  = $startYear . ' – ' . ($endYear ?? 'Present');
            $componentSet->update([
                'start_year' => $startYear,
                'end_year'   => $endYear,
                'label'      => $newLabel,
            ]);
        }

        // Delete components that were removed
        $submittedIds = collect($componentsData)->pluck('id')->filter()->toArray();
        \App\Models\Component::where('component_set_id', $componentSet->id)
            ->whereNotIn('id', $submittedIds)
            ->delete();

        // Temporarily rename to avoid unique constraint conflicts
        $componentsToRename = \App\Models\Component::whereIn('id', $submittedIds)->get();
        foreach ($componentsToRename as $tempComp) {
            $tempComp->update(['component_code' => 'temp_' . uniqid() . '_' . $tempComp->component_code]);
        }

        $totalMarks = 0;
        foreach ($componentsData as $compData) {
            if (!empty($compData['id'])) {
                $component = \App\Models\Component::findOrFail($compData['id']);
                $component->update([
                    'component_code'  => $compData['code'],
                    'component_name'  => $compData['name'],
                    'component_label' => $compData['label'] ?? null,
                    'total_marks'     => $compData['marks'],
                    'level_id'        => $compData['level_id'] ?? null,
                ]);
            } else {
                \App\Models\Component::create([
                    'subject_id'      => $subject->id,
                    'component_set_id'=> $componentSet->id,
                    'component_code'  => $compData['code'],
                    'component_name'  => $compData['name'],
                    'component_label' => $compData['label'] ?? null,
                    'component_type'  => 'paper',
                    'total_marks'     => $compData['marks'],
                    'scaling_factor'  => 1,
                    'is_mandatory'    => true,
                    'level_id'        => $compData['level_id'] ?? null,
                ]);
            }
            $totalMarks += $compData['marks'];
        }

        // Update subject total marks if this is the default set
        if ($componentSet->is_default) {
            $subject->update(['total_marks' => $totalMarks]);
        }

        return response()->json([
            'success' => true,
            'message' => "Components for '{$componentSet->display_label}' updated successfully.",
        ]);
    }

    /**
     * Delete a component set and its components (AJAX)
     */
    public function deleteComponentSet(Request $request, Subject $subject, \App\Models\ComponentSet $componentSet)
    {
        if ($componentSet->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the default component set.',
            ], 422);
        }

        $componentSet->components()->delete();
        $componentSet->delete();

        return response()->json([
            'success' => true,
            'message' => "Component set '{$componentSet->display_label}' deleted.",
            'redirect' => route('subjects.edit', $subject->id),
        ]);
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

        $pumResults = $subject->results->filter(function ($r) {
            return $r->pum !== null && $r->pum > 0;
        });

        $stats = [
            'total_students' => $subject->results->count(),
            'grade_distribution' => $subject->results->groupBy('grade')->map->count(),
            'avg_pum' => $pumResults->isNotEmpty() ? $pumResults->avg('pum') : 0,
            'pass_rate' => $subject->results->count() > 0 ? ($subject->results->where('is_passed', true)->count() / $subject->results->count()) * 100 : 0,
            'highest' => $pumResults->isNotEmpty() ? $pumResults->max('pum') : 0,
            'lowest' => $pumResults->isNotEmpty() ? $pumResults->min('pum') : 0,
        ];

        return response()->json([
            'subject' => $subject,
            'stats' => $stats,
        ]);
    }
}
