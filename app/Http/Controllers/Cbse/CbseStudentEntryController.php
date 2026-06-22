<?php

namespace App\Http\Controllers\Cbse;

use App\Http\Controllers\Controller;
use App\Models\Cbse\CbseAcademicYear;
use App\Models\Cbse\CbseQualification;
use App\Models\Cbse\CbseStudent;
use App\Models\Cbse\CbseSubject;
use App\Models\Cbse\CbseResult;
use Illuminate\Http\Request;

class CbseStudentEntryController extends Controller
{
    /**
     * Show the enrollment grid for a specific academic year.
     */
    public function show(CbseAcademicYear $academicYear)
    {
        $class10Qual = CbseQualification::where('qualification_type', 'CLASS_10')->first();
        $class12Qual = CbseQualification::where('qualification_type', 'CLASS_12')->first();

        // Enrolled students for Class 10 in this academic year (or new students with no results at all)
        $class10Students = CbseStudent::where('qualification_type', 'CLASS_10')
            ->where(function($query) use ($academicYear) {
                $query->whereHas('results', function($q) use ($academicYear) {
                    $q->where('academic_year_id', $academicYear->id);
                })->orWhereDoesntHave('results');
            })
            ->get()
            ->sortBy('student_name')
            ->values();

        // Enrolled students for Class 12 in this academic year (or new students with no results at all)
        $class12Students = CbseStudent::where('qualification_type', 'CLASS_12')
            ->where(function($query) use ($academicYear) {
                $query->whereHas('results', function($q) use ($academicYear) {
                    $q->where('academic_year_id', $academicYear->id);
                })->orWhereDoesntHave('results');
            })
            ->get()
            ->sortBy('student_name')
            ->values();

        $class10Subjects = $class10Qual ? CbseSubject::where('qualification_id', $class10Qual->id)->withCount('results')->orderByDesc('results_count')->orderBy('subject_name')->get() : collect();
        $class12Subjects = $class12Qual ? CbseSubject::where('qualification_id', $class12Qual->id)->withCount('results')->orderByDesc('results_count')->orderBy('subject_name')->get() : collect();

        // Map registered subject IDs per student ID and also their roll number
        $studentSubjectsMap = [];
        $studentRollNumbers = [];
        $allResults = CbseResult::where('academic_year_id', $academicYear->id)->get();
        foreach ($allResults as $res) {
            if ($res->subject_id) {
                $studentSubjectsMap[$res->student_id][] = $res->subject_id;
            }
            if ($res->roll_number) {
                $studentRollNumbers[$res->student_id] = $res->roll_number;
            }
        }

        return view('cbse.student-entries.show', compact(
            'academicYear',
            'class10Qual',
            'class12Qual',
            'class10Students',
            'class12Students',
            'class10Subjects',
            'class12Subjects',
            'studentSubjectsMap',
            'studentRollNumbers'
        ));
    }

    /**
     * Create a new student and optionally select their class.
     */
    public function addStudent(Request $request, CbseAcademicYear $academicYear)
    {
        $request->validate([
            'admission_number'   => 'required|unique:cbse_students,admission_number',
            'student_name'       => 'required|string|max:255',
            'qualification_type' => 'required|in:CLASS_10,CLASS_12',
        ]);

        CbseStudent::create([
            'admission_number'   => $request->admission_number,
            'student_name'       => $request->student_name,
            'qualification_type' => $request->qualification_type,
            'status'             => 'active',
        ]);

        return back()->with('success', 'Student added successfully.');
    }

    /**
     * Toggle a subject enrollment for a student.
     */
    public function toggleSubject(Request $request, CbseAcademicYear $academicYear)
    {
        $request->validate([
            'student_id'       => 'required|exists:cbse_students,id',
            'qualification_id' => 'required|exists:cbse_qualifications,id',
            'subject_id'       => 'required|exists:cbse_subjects,id',
            'is_enrolled'      => 'required|boolean',
        ]);

        $studentId = $request->student_id;
        $subjectId = $request->subject_id;
        $qualificationId = $request->qualification_id;

        if ($request->is_enrolled) {
            // Enroll (create result record)
            $examYear = (int) substr($academicYear->name, -4);
            CbseResult::firstOrCreate([
                'academic_year_id' => $academicYear->id,
                'student_id'       => $studentId,
                'qualification_id' => $qualificationId,
                'subject_id'       => $subjectId,
            ], [
                'exam_year'        => $examYear,
            ]);
            return response()->json(['success' => true, 'message' => 'Enrolled in subject.']);
        } else {
            // Unenroll (delete result record)
            CbseResult::where('academic_year_id', $academicYear->id)
                ->where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->delete();
            return response()->json(['success' => true, 'message' => 'Unenrolled from subject.']);
        }
    }

    /**
     * Update the roll number for a student in this academic year.
     */
    public function updateRollNumber(Request $request, CbseAcademicYear $academicYear)
    {
        $request->validate([
            'student_id'  => 'required|exists:cbse_students,id',
            'roll_number' => 'nullable|string|max:255',
        ]);

        CbseResult::where('academic_year_id', $academicYear->id)
            ->where('student_id', $request->student_id)
            ->update(['roll_number' => $request->roll_number]);

        return response()->json(['success' => true, 'message' => 'Roll number updated.']);
    }
    /**
     * Bulk update entries for multiple students and subjects.
     */
    public function updateBulkEntries(Request $request, CbseAcademicYear $academicYear)
    {
        $request->validate([
            'entries' => 'required|array',
            'entries.*.student_id' => 'required|exists:cbse_students,id',
            'entries.*.subject_id' => 'required|exists:cbse_subjects,id',
            'entries.*.registered' => 'required|boolean',
            'qualification_id' => 'required|exists:cbse_qualifications,id',
        ]);

        $examYear = (int) substr($academicYear->name, -4);
        $qualificationId = $request->qualification_id;

        foreach ($request->entries as $entry) {
            $studentId = $entry['student_id'];
            $subjectId = $entry['subject_id'];

            if ($entry['registered']) {
                CbseResult::firstOrCreate([
                    'academic_year_id' => $academicYear->id,
                    'student_id'       => $studentId,
                    'qualification_id' => $qualificationId,
                    'subject_id'       => $subjectId,
                ], [
                    'exam_year'        => $examYear,
                ]);
            } else {
                CbseResult::where('academic_year_id', $academicYear->id)
                    ->where('student_id', $studentId)
                    ->where('subject_id', $subjectId)
                    ->delete();
            }
        }

        return response()->json(['success' => true, 'message' => 'Entries updated successfully.']);
    }
}
