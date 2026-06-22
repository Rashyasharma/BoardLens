<?php

namespace App\Http\Controllers\Cbse;

use App\Http\Controllers\Controller;
use App\Models\Cbse\CbseAcademicYear;
use Illuminate\Http\Request;

class CbseAcademicYearController extends Controller
{
    /**
     * List all academic years.
     */
    public function index()
    {
        $academicYears = CbseAcademicYear::orderBy('name', 'desc')->get()->map(function ($ay) {
            $studentCount = \App\Models\Cbse\CbseResult::where('academic_year_id', $ay->id)
                ->distinct('student_id')
                ->count('student_id');

            $class10Count = \App\Models\Cbse\CbseResult::where('academic_year_id', $ay->id)
                ->whereHas('qualification', function($q) {
                    $q->where('qualification_type', 'CLASS_10');
                })
                ->distinct('student_id')
                ->count('student_id');

            $class12Count = \App\Models\Cbse\CbseResult::where('academic_year_id', $ay->id)
                ->whereHas('qualification', function($q) {
                    $q->where('qualification_type', 'CLASS_12');
                })
                ->distinct('student_id')
                ->count('student_id');

            $totalResults = \App\Models\Cbse\CbseResult::where('academic_year_id', $ay->id)->count();
            $passedResults = \App\Models\Cbse\CbseResult::where('academic_year_id', $ay->id)->where('is_passed', true)->count();
            $passRate = $totalResults > 0 ? round(($passedResults / $totalResults) * 100, 1) : null;

            return [
                'id'         => $ay->id,
                'name'       => $ay->name,
                'start_date' => $ay->start_date,
                'end_date'   => $ay->end_date,
                'is_active'  => $ay->is_active,
                'students'   => $studentCount,
                'class_10'   => $class10Count,
                'class_12'   => $class12Count,
                'pass_rate'  => $passRate !== null ? "{$passRate}%" : 'N/A',
            ];
        });

        return view('cbse.academic-years.index', compact('academicYears'));
    }

    /**
     * Store new academic year.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255|unique:cbse_academic_years,name',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);

        CbseAcademicYear::create([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('cbse.academic-years.index')
            ->with('success', "Academic Year '{$request->name}' created successfully.");
    }

    /**
     * Update existing academic year.
     */
    public function update(Request $request, CbseAcademicYear $academicYear)
    {
        $request->validate([
            'name'       => 'required|string|max:255|unique:cbse_academic_years,name,' . $academicYear->id,
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'is_active'  => 'boolean',
        ]);

        $academicYear->update([
            'name'       => $request->name,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'is_active'  => $request->boolean('is_active', true),
        ]);

        return redirect()->route('cbse.academic-years.index')
            ->with('success', 'Academic Year updated successfully.');
    }

    /**
     * Delete an academic year.
     */
    public function destroy(CbseAcademicYear $academicYear)
    {
        $academicYear->delete();
        return redirect()->route('cbse.academic-years.index')
            ->with('success', 'Academic Year deleted.');
    }
}
