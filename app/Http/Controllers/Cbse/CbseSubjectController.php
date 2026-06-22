<?php

namespace App\Http\Controllers\Cbse;

use App\Http\Controllers\Controller;
use App\Models\Cbse\CbseQualification;
use App\Models\Cbse\CbseSubject;
use Illuminate\Http\Request;

class CbseSubjectController extends Controller
{
    public function index(Request $request)
    {
        $qualificationId = $request->input('qualification_id');
        $query = CbseSubject::with('qualification')
            ->withCount(['results as total_candidates'])
            ->withAvg('results as avg_percentage', 'percentage');

        if ($qualificationId) {
            $query->where('qualification_id', $qualificationId);
        }

        $subjects = $query->orderBy('subject_name')->get();
        $qualifications = CbseQualification::all();

        return view('cbse.subjects.index', compact('subjects', 'qualifications', 'qualificationId'));
    }

    public function create()
    {
        $qualifications = CbseQualification::all();
        return view('cbse.subjects.create', compact('qualifications'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'qualification_id' => 'required|exists:cbse_qualifications,id',
            'subject_code'      => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('cbse_subjects')->where(function ($query) use ($request) {
                    return $query->where('qualification_id', $request->qualification_id);
                })
            ],
            'subject_name'      => 'required|string',
            'theory_marks'      => 'required|integer|min:0|max:100',
            'practical_marks'  => 'required|integer|min:0|max:100',
            'practical_type'   => 'required|string',
            'description'      => 'nullable|string',
        ], [
            'subject_code.unique' => 'This subject code is already registered for the selected qualification.',
        ]);

        $validated['theory_passing_marks'] = round($validated['theory_marks'] * 0.33, 2);
        $validated['passing_percentage'] = 33.00;

        CbseSubject::create($validated);

        return redirect()->route('cbse.subjects.index')->with('success', 'Subject created successfully.');
    }

    public function edit(CbseSubject $subject)
    {
        $qualifications = CbseQualification::all();
        return view('cbse.subjects.edit', compact('subject', 'qualifications'));
    }

    public function update(Request $request, CbseSubject $subject)
    {
        $validated = $request->validate([
            'qualification_id' => 'required|exists:cbse_qualifications,id',
            'subject_code'      => [
                'required',
                'string',
                \Illuminate\Validation\Rule::unique('cbse_subjects')->where(function ($query) use ($request) {
                    return $query->where('qualification_id', $request->qualification_id);
                })->ignore($subject->id)
            ],
            'subject_name'      => 'required|string',
            'theory_marks'      => 'required|integer|min:0|max:100',
            'practical_marks'  => 'required|integer|min:0|max:100',
            'practical_type'   => 'required|string',
            'description'      => 'nullable|string',
        ], [
            'subject_code.unique' => 'This subject code is already registered for the selected qualification.',
        ]);

        $validated['theory_passing_marks'] = round($validated['theory_marks'] * 0.33, 2);

        $subject->update($validated);

        return redirect()->route('cbse.subjects.index')->with('success', 'Subject updated successfully.');
    }

    public function destroy(CbseSubject $subject)
    {
        $subject->delete();
        return redirect()->route('cbse.subjects.index')->with('success', 'Subject deleted successfully.');
    }
}
