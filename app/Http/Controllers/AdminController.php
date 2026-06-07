<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subject;
use App\Models\Component;
use App\Models\School;
use App\Models\Qualification;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of admin resource.
     */
    public function index(Request $request)
    {
        $subjects = Subject::with('qualification')->orderBy('subject_name')->get();
        $components = Component::with('subject.qualification')->orderBy('component_code')->get();
        $qualifications = Qualification::orderBy('qualification_name')->get();

        return view('admin.index', compact('subjects', 'components', 'qualifications'));
    }

    /**
     * Qualifications Management
     */
    public function storeQualification(Request $request)
    {
        $data = $request->validate([
            'qualification_type' => 'required|in:IGCSE,AS_A_LEVEL|unique:qualifications,qualification_type',
            'qualification_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Qualification::create($data);

        return redirect()->back()->with('success', 'Qualification created successfully!');
    }

    public function updateQualification(Request $request, Qualification $qualification)
    {
        $data = $request->validate([
            'qualification_type' => 'required|in:IGCSE,AS_A_LEVEL|unique:qualifications,qualification_type,' . $qualification->id,
            'qualification_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $qualification->update($data);

        return redirect()->back()->with('success', 'Qualification updated successfully!');
    }

    public function destroyQualification(Qualification $qualification)
    {
        $qualification->delete();
        return redirect()->back()->with('success', 'Qualification deleted successfully!');
    }

    /**
     * Subjects Management
     */
    public function storeSubject(Request $request)
    {
        $data = $request->validate([
            'subject_code' => 'required|string|max:255',
            'subject_name' => 'required|string|max:255',
            'qualification_id' => 'required|exists:qualifications,id',
        ]);

        $data['total_marks'] = 0;
        $data['passing_percentage'] = 40.00;

        Subject::create($data);

        return redirect()->back()->with('success', 'Subject created successfully!');
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'subject_code' => 'required|string|max:255',
            'subject_name' => 'required|string|max:255',
            'qualification_id' => 'required|exists:qualifications,id',
        ]);

        $subject->update($data);

        return redirect()->back()->with('success', 'Subject updated successfully!');
    }

    public function destroySubject(Subject $subject)
    {
        $subject->delete();
        return redirect()->back()->with('success', 'Subject deleted successfully!');
    }

    /**
     * Components Management
     */
    public function storeComponent(Request $request)
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'component_code' => 'required|string|max:255',
            'component_name' => 'required|string|max:255',
            'component_type' => 'required|in:paper,practical,project,coursework,other',
            'total_marks' => 'required|integer|min:1',
            'scaling_factor' => 'required|integer|min:0|max:10',
        ]);

        $comp = Component::create($data);

        // Update subject total marks
        $this->recalculateSubjectMarks($comp->subject_id);

        return redirect()->back()->with('success', 'Component created successfully!');
    }

    public function updateComponent(Request $request, Component $component)
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'component_code' => 'required|string|max:255',
            'component_name' => 'required|string|max:255',
            'component_type' => 'required|in:paper,practical,project,coursework,other',
            'total_marks' => 'required|integer|min:1',
            'scaling_factor' => 'required|integer|min:0|max:10',
        ]);

        $oldSubjectId = $component->subject_id;
        $component->update($data);

        $this->recalculateSubjectMarks($oldSubjectId);
        if ($oldSubjectId !== $component->subject_id) {
            $this->recalculateSubjectMarks($component->subject_id);
        }

        return redirect()->back()->with('success', 'Component updated successfully!');
    }

    public function destroyComponent(Component $component)
    {
        $subjectId = $component->subject_id;
        $component->delete();

        $this->recalculateSubjectMarks($subjectId);

        return redirect()->back()->with('success', 'Component deleted successfully!');
    }

    private function recalculateSubjectMarks($subjectId): void
    {
        $subject = Subject::find($subjectId);
        if ($subject) {
            $totalMarks = Component::where('subject_id', $subjectId)->sum('total_marks');
            $subject->update(['total_marks' => $totalMarks]);
        }
    }
}
