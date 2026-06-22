<?php

namespace App\Http\Controllers\Cbse;

use App\Http\Controllers\Controller;
use App\Models\Cbse\CbseStudent;
use Illuminate\Http\Request;

class CbseStudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $qualification = $request->input('qualification_type');
        $query = CbseStudent::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('admission_number', 'like', "%{$search}%");
            });
        }

        if ($qualification) {
            $query->where('qualification_type', $qualification);
        }

        $students = $query->latest()->paginate(25);

        return view('cbse.students.index', compact('students', 'search', 'qualification'));
    }

    public function create()
    {
        return view('cbse.students.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'admission_number'   => 'required|string|unique:cbse_students,admission_number',
            'student_name'       => 'required|string',
            'father_name'        => 'nullable|string',
            'mother_name'        => 'nullable|string',
            'date_of_birth'      => 'nullable|date',
            'gender'             => 'required|in:M,F,O',
            'qualification_type' => 'required|in:CLASS_10,CLASS_12',
            'admission_year'     => 'required|integer|min:2000|max:2050',
            'status'             => 'required|in:active,passed,failed,transferred',
        ]);

        CbseStudent::create($validated);

        return redirect()->route('cbse.students.index')->with('success', 'Student created successfully.');
    }

    public function show(CbseStudent $student)
    {
        $student->load(['results' => function ($q) {
            $q->join('cbse_academic_years', 'cbse_results.academic_year_id', '=', 'cbse_academic_years.id')
              ->select('cbse_results.*')
              ->with(['subject', 'qualification', 'academicYear'])
              ->orderBy('cbse_academic_years.name', 'desc');
        }]);

        return view('cbse.students.show', compact('student'));
    }

    public function edit(CbseStudent $student)
    {
        return view('cbse.students.edit', compact('student'));
    }

    public function update(Request $request, CbseStudent $student)
    {
        $validated = $request->validate([
            'admission_number'   => 'required|string|unique:cbse_students,admission_number,' . $student->id,
            'student_name'       => 'required|string',
            'father_name'        => 'nullable|string',
            'mother_name'        => 'nullable|string',
            'date_of_birth'      => 'nullable|date',
            'gender'             => 'required|in:M,F,O',
            'qualification_type' => 'required|in:CLASS_10,CLASS_12',
            'admission_year'     => 'required|integer|min:2000|max:2050',
            'status'             => 'required|in:active,passed,failed,transferred',
        ]);

        $student->update($validated);

        return redirect()->route('cbse.students.index')->with('success', 'Student updated successfully.');
    }

    public function destroy(CbseStudent $student)
    {
        $student->delete();
        return redirect()->route('cbse.students.index')->with('success', 'Student deleted successfully.');
    }
}
