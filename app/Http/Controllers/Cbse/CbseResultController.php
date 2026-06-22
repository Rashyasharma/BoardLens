<?php

namespace App\Http\Controllers\Cbse;

use App\Http\Controllers\Controller;
use App\Models\Cbse\CbseQualification;
use App\Models\Cbse\CbseSubject;
use App\Models\Cbse\CbseStudent;
use App\Models\Cbse\CbseResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CbseResultController extends Controller
{
    public function index(Request $request)
    {
        $searchYearId = $request->input('academic_year_id');

        $resultsQuery = CbseResult::query()
            ->selectRaw('subject_id, academic_year_id, COUNT(*) as candidate_count, AVG(percentage) as average_percentage, SUM(CASE WHEN is_passed = 1 THEN 1 ELSE 0 END) as passed_count')
            ->groupBy('subject_id', 'academic_year_id')
            ->with(['subject.qualification', 'academicYear']);

        if ($searchYearId) {
            $resultsQuery->where('academic_year_id', $searchYearId);
        }

        $allResults = $resultsQuery->get();

        // Group by academic_year_id
        $yearGroups = $allResults->groupBy('academic_year_id')->map(function ($group) {
            $first = $group->first();
            $year = $first->academicYear;
            if (!$year) return null;

            // Group by qualification within this year
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
                        'average_percentage' => round($r->average_percentage, 1)
                    ];
                });

                $totalCand = $qualGroup->sum('candidate_count');
                $avgPercentage = $qualGroup->avg('average_percentage');

                return [
                    'qualification_id' => $qualification->id,
                    'qualification_name' => $qualification->qualification_name,
                    'subject_count' => $qualGroup->unique('subject_id')->count(),
                    'total_candidates' => $totalCand,
                    'average_percentage' => $avgPercentage ? round($avgPercentage, 1) : null,
                    'subjects' => $subjects
                ];
            })->filter()->values();

            return [
                'academic_year_id' => $year->id,
                'year_name' => $year->name,
                'qualifications' => $qualifications
            ];
        })->filter()->values();

        // Sort groups chronologically desc
        $yearGroups = $yearGroups->sortByDesc('year_name')->values();

        $qualifications = CbseQualification::all();
        $academicYears = \App\Models\Cbse\CbseAcademicYear::orderBy('name', 'desc')->get();

        return view('cbse.results.index', [
            'yearGroups' => $yearGroups,
            'qualifications' => $qualifications,
            'academicYears' => $academicYears,
            'selectedYearId' => $searchYearId,
        ]);
    }

    public function academicYearDetails(\App\Models\Cbse\CbseAcademicYear $academicYear)
    {
        $allResults = CbseResult::where('academic_year_id', $academicYear->id)
            ->selectRaw('subject_id, academic_year_id, COUNT(*) as candidate_count, AVG(percentage) as average_percentage, SUM(CASE WHEN is_passed = 1 THEN 1 ELSE 0 END) as passed_count')
            ->groupBy('subject_id', 'academic_year_id')
            ->with(['subject.qualification', 'academicYear'])
            ->get();

        $qualificationsData = $allResults->groupBy(fn($r) => $r->subject->qualification_id)->map(function ($qualGroup) {
            $firstQual = $qualGroup->first();
            $qualification = $firstQual->subject->qualification;
            if (!$qualification) return null;

            $subjects = $qualGroup->map(function ($r) {
                return [
                    'subject_id' => $r->subject_id,
                    'subject_name' => $r->subject->subject_name,
                    'subject_code' => $r->subject->subject_code,
                    'candidate_count' => $r->candidate_count,
                    'passed_count' => $r->passed_count,
                    'failed_count' => $r->candidate_count - $r->passed_count,
                    'average_percentage' => round($r->average_percentage, 1),
                    'marks_entered' => CbseResult::where('academic_year_id', $r->academic_year_id)
                        ->where('subject_id', $r->subject_id)
                        ->where(fn($q) => $q->whereNotNull('total_obtained')->orWhere('is_absent', true))
                        ->exists()
                ];
            });

            $totalCand = $qualGroup->sum('candidate_count');
            $avgPercentage = $qualGroup->avg('average_percentage');

            return [
                'qualification_id' => $qualification->id,
                'qualification_name' => $qualification->qualification_name,
                'subject_count' => $qualGroup->unique('subject_id')->count(),
                'total_candidates' => $totalCand,
                'average_percentage' => $avgPercentage ? round($avgPercentage, 1) : null,
                'subjects' => $subjects
            ];
        })->filter()->values();

        $topScores = CbseResult::where('academic_year_id', $academicYear->id)
            ->where('percentage', '>=', 90)
            ->with(['student', 'subject'])
            ->get()
            ->sort(function ($a, $b) {
                if ($a->student->qualification_type === $b->student->qualification_type) {
                    return $a->subject->subject_name <=> $b->subject->subject_name;
                }
                return $a->student->qualification_type <=> $b->student->qualification_type;
            })->values();

        $lowScores = CbseResult::where('academic_year_id', $academicYear->id)
            ->where('percentage', '<', 33)
            ->with(['student', 'subject'])
            ->get()
            ->sort(function ($a, $b) {
                if ($a->student->qualification_type === $b->student->qualification_type) {
                    return $a->subject->subject_name <=> $b->subject->subject_name;
                }
                return $a->student->qualification_type <=> $b->student->qualification_type;
            })->values();

        $toppersClass10 = DB::table('cbse_results')
            ->join('cbse_students', 'cbse_results.student_id', '=', 'cbse_students.id')
            ->where('cbse_results.academic_year_id', $academicYear->id)
            ->where('cbse_students.qualification_type', 'CLASS_10')
            ->select('cbse_students.id', 'cbse_students.student_name', DB::raw('SUM(cbse_results.total_obtained) as aggregate_obtained'), DB::raw('SUM(cbse_results.total_marks) as aggregate_marks'))
            ->groupBy('cbse_students.id', 'cbse_students.student_name')
            ->havingRaw('SUM(cbse_results.total_marks) > 0')
            ->orderByRaw('(SUM(cbse_results.total_obtained) * 1.0 / SUM(cbse_results.total_marks)) DESC')
            ->limit(3)
            ->get()
            ->map(function ($row) {
                $row->percentage = round(($row->aggregate_obtained / $row->aggregate_marks) * 100, 2);
                return $row;
            });

        $toppersClass12 = DB::table('cbse_results')
            ->join('cbse_students', 'cbse_results.student_id', '=', 'cbse_students.id')
            ->where('cbse_results.academic_year_id', $academicYear->id)
            ->where('cbse_students.qualification_type', 'CLASS_12')
            ->select('cbse_students.id', 'cbse_students.student_name', DB::raw('SUM(cbse_results.total_obtained) as aggregate_obtained'), DB::raw('SUM(cbse_results.total_marks) as aggregate_marks'))
            ->groupBy('cbse_students.id', 'cbse_students.student_name')
            ->havingRaw('SUM(cbse_results.total_marks) > 0')
            ->orderByRaw('(SUM(cbse_results.total_obtained) * 1.0 / SUM(cbse_results.total_marks)) DESC')
            ->limit(3)
            ->get()
            ->map(function ($row) {
                $row->percentage = round(($row->aggregate_obtained / $row->aggregate_marks) * 100, 2);
                return $row;
            });

        $qual10 = \App\Models\Cbse\CbseQualification::where('qualification_type', 'CLASS_10')->first();
        $qual12 = \App\Models\Cbse\CbseQualification::where('qualification_type', 'CLASS_12')->first();

        return view('cbse.results.academic-year-details', [
            'academicYear' => $academicYear,
            'qualificationsData' => $qualificationsData,
            'topScores' => $topScores,
            'lowScores' => $lowScores,
            'toppersClass10' => $toppersClass10,
            'toppersClass12' => $toppersClass12,
            'qual10' => $qual10,
            'qual12' => $qual12,
        ]);
    }

    public function subjectDetails(\App\Models\Cbse\CbseAcademicYear $academicYear, CbseSubject $subject)
    {
        $sortBy = request('sort_by', 'roll_number');
        $sortOrder = request('sort_order', 'asc');

        $query = CbseResult::where('academic_year_id', $academicYear->id)
            ->where('subject_id', $subject->id)
            ->with(['student', 'qualification'])
            ->leftJoin('cbse_students', 'cbse_results.student_id', '=', 'cbse_students.id')
            ->select('cbse_results.*');

        if ($sortBy === 'name') {
            $query->orderBy('cbse_students.student_name', $sortOrder);
        } elseif ($sortBy === 'marks') {
            $query->orderBy('cbse_results.total_obtained', $sortOrder);
        } else {
            // Default or roll_number
            $query->orderBy('cbse_results.roll_number', $sortOrder);
        }

        $results = $query->paginate(25)->withQueryString();

        // Calculate Stats
        $allResultsForStats = CbseResult::where('academic_year_id', $academicYear->id)
            ->where('subject_id', $subject->id)
            ->whereNotNull('total_obtained')
            ->with('student')
            ->get();

        $averagePercentage = $allResultsForStats->avg('percentage');
        $highestResult = $allResultsForStats->sortByDesc('total_obtained')->first();
        $lowestResult = $allResultsForStats->where('is_absent', false)->sortBy('total_obtained')->first();

        // Last year average
        $lastYear = \App\Models\Cbse\CbseAcademicYear::where('start_date', '<', $academicYear->start_date)
            ->orderBy('start_date', 'desc')
            ->first();
            
        $lastYearAverage = null;
        if ($lastYear) {
            $lastYearAverage = CbseResult::where('academic_year_id', $lastYear->id)
                ->where('subject_id', $subject->id)
                ->avg('percentage');
        }

        return view('cbse.results.subject-details', [
            'academicYear' => $academicYear,
            'subject' => $subject,
            'results' => $results,
            'averagePercentage' => $averagePercentage,
            'highestResult' => $highestResult,
            'lowestResult' => $lowestResult,
            'lastYearAverage' => $lastYearAverage,
        ]);
    }

    public function destroySubjectResults(\App\Models\Cbse\CbseAcademicYear $academicYear, CbseSubject $subject)
    {
        CbseResult::where('academic_year_id', $academicYear->id)
            ->where('subject_id', $subject->id)
            ->update([
                'roll_number'         => null,
                'theory_obtained'    => 0,
                'practical_obtained' => 0,
                'total_obtained'     => null,
                'percentage'         => null,
                'grade'              => null,
                'is_passed'          => false,
                'is_absent'          => false,
                'is_compartment'     => false,
            ]);

        return redirect()->route('cbse.results.index')->with('success', 'All marks cleared for the subject in this academic year.');
    }

    public function create(Request $request)
    {
        $academicYears = \App\Models\Cbse\CbseAcademicYear::orderBy('name', 'desc')->get();
        $qualifications = CbseQualification::all();
        
        $selectedYearId = $request->input('academic_year_id');
        $selectedQualId = $request->input('qualification_id');
        $selectedSubjectId = $request->input('subject_id');

        $subjects = collect();
        if ($selectedQualId) {
            $subjects = CbseSubject::where('qualification_id', $selectedQualId)->orderBy('subject_name')->get();
        } elseif ($selectedSubjectId) {
            $sub = CbseSubject::find($selectedSubjectId);
            if ($sub) {
                $selectedQualId = $sub->qualification_id;
                $subjects = CbseSubject::where('qualification_id', $selectedQualId)->orderBy('subject_name')->get();
            }
        }

        $enrolledResults = collect();
        $selectedSubject = null;

        if ($selectedYearId && $selectedSubjectId) {
            $selectedSubject = CbseSubject::find($selectedSubjectId);
            $enrolledResults = CbseResult::where('academic_year_id', $selectedYearId)
                ->where('subject_id', $selectedSubjectId)
                ->with('student')
                ->get()
                ->sortBy(fn($r) => $r->student->student_name)
                ->values();
        }

        return view('cbse.results.create', compact(
            'academicYears', 'qualifications', 'subjects', 'enrolledResults', 'selectedSubject',
            'selectedYearId', 'selectedQualId', 'selectedSubjectId'
        ));
    }

    public function saveRow(Request $request)
    {
        $request->validate([
            'result_id'          => 'required|exists:cbse_results,id',
            'roll_number'         => 'nullable|string',
            'theory_obtained'    => 'nullable|numeric|min:0',
            'practical_obtained' => 'nullable|numeric|min:0',
            'is_absent'           => 'boolean',
        ]);

        $result = CbseResult::findOrFail($request->input('result_id'));
        $subject = $result->subject;

        $isAbsent = $request->boolean('is_absent');
        $theory = $isAbsent ? 0 : (float)$request->input('theory_obtained', 0);
        $practical = $isAbsent ? 0 : (float)$request->input('practical_obtained', 0);

        if (!$isAbsent) {
            if ($theory > $subject->theory_marks) {
                return response()->json(['success' => false, 'error' => 'Theory marks cannot exceed ' . $subject->theory_marks]);
            }
            if ($practical > $subject->practical_marks) {
                return response()->json(['success' => false, 'error' => 'Practical/IA marks cannot exceed ' . $subject->practical_marks]);
            }
        }

        $totalObtained = $theory + $practical;
        $totalMax = $subject->theory_marks + $subject->practical_marks;
        $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;

        $grade = $isAbsent ? 'F' : CbseResult::computeGrade($percentage);
        $isPassed = !$isAbsent && ($percentage >= 33.0) && ($theory >= $subject->theory_passing_marks);
        $isCompartment = !$isAbsent && !$isPassed && ($percentage >= 25.0);

        $result->update([
            'roll_number'         => $request->input('roll_number'),
            'theory_obtained'    => $theory,
            'practical_obtained' => $practical,
            'total_obtained'     => $totalObtained,
            'total_marks'        => $totalMax,
            'percentage'         => $percentage,
            'grade'              => $grade,
            'is_passed'          => $isPassed,
            'is_absent'          => $isAbsent,
            'is_compartment'     => $isCompartment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Result updated.',
            'total_obtained' => $totalObtained,
            'percentage' => round($percentage, 2),
            'grade' => $grade,
            'status' => $isPassed ? 'PASS' : ($isCompartment ? 'COMPARTMENT' : 'FAIL'),
            'status_color' => $isPassed ? 'emerald' : ($isCompartment ? 'amber' : 'rose'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id'          => 'required|exists:cbse_students,id',
            'subject_id'          => 'required|exists:cbse_subjects,id',
            'academic_year_id'    => 'required|exists:cbse_academic_years,id',
            'roll_number'         => 'nullable|string',
            'theory_obtained'    => 'nullable|numeric|min:0',
            'practical_obtained' => 'nullable|numeric|min:0',
            'is_absent'           => 'boolean',
        ]);

        $subject = CbseSubject::findOrFail($request->input('subject_id'));
        $student = CbseStudent::findOrFail($request->input('student_id'));

        $isAbsent = $request->boolean('is_absent');

        $theory = $isAbsent ? 0 : (float)$request->input('theory_obtained', 0);
        $practical = $isAbsent ? 0 : (float)$request->input('practical_obtained', 0);

        if (!$isAbsent) {
            if ($theory > $subject->theory_marks) {
                return back()->withErrors(['theory_obtained' => 'Theory marks cannot exceed subject maximum (' . $subject->theory_marks . ')']);
            }
            if ($practical > $subject->practical_marks) {
                return back()->withErrors(['practical_obtained' => 'Practical/IA marks cannot exceed subject maximum (' . $subject->practical_marks . ')']);
            }
        }

        $totalObtained = $theory + $practical;
        $totalMax = $subject->theory_marks + $subject->practical_marks;
        $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;

        $grade = $isAbsent ? 'F' : CbseResult::computeGrade($percentage);
        $isPassed = !$isAbsent && ($percentage >= 33.0) && ($theory >= $subject->theory_passing_marks);
        $isCompartment = !$isAbsent && !$isPassed && ($percentage >= 25.0); // Simple business rule for compartment

        $academicYear = \App\Models\Cbse\CbseAcademicYear::find($validated['academic_year_id']);
        $examYear = $academicYear ? (int) substr($academicYear->name, -4) : date('Y');

        CbseResult::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'subject_id' => $validated['subject_id'],
                'academic_year_id' => $validated['academic_year_id'],
            ],
            [
                'qualification_id'   => $subject->qualification_id,
                'exam_year'          => $examYear,
                'roll_number'         => $validated['roll_number'],
                'theory_obtained'    => $theory,
                'practical_obtained' => $practical,
                'total_obtained'     => $totalObtained,
                'total_marks'        => $totalMax,
                'percentage'         => $percentage,
                'grade'              => $grade,
                'is_passed'          => $isPassed,
                'is_absent'          => $isAbsent,
                'is_compartment'     => $isCompartment,
            ]
        );

        return redirect()->route('cbse.results.index')->with('success', 'Result saved successfully.');
    }

    public function edit(CbseResult $result)
    {
        $students = CbseStudent::all();
        $subjects = CbseSubject::all();
        return view('cbse.results.edit', compact('result', 'students', 'subjects'));
    }

    public function update(Request $request, CbseResult $result)
    {
        $validated = $request->validate([
            'theory_obtained'    => 'nullable|numeric|min:0',
            'practical_obtained' => 'nullable|numeric|min:0',
            'is_absent'           => 'boolean',
        ]);

        $subject = $result->subject;
        $isAbsent = $request->boolean('is_absent');

        $theory = $isAbsent ? 0 : (float)$request->input('theory_obtained', 0);
        $practical = $isAbsent ? 0 : (float)$request->input('practical_obtained', 0);

        if (!$isAbsent) {
            if ($theory > $subject->theory_marks) {
                return back()->withErrors(['theory_obtained' => 'Theory marks cannot exceed subject maximum (' . $subject->theory_marks . ')']);
            }
            if ($practical > $subject->practical_marks) {
                return back()->withErrors(['practical_obtained' => 'Practical/IA marks cannot exceed subject maximum (' . $subject->practical_marks . ')']);
            }
        }

        $totalObtained = $theory + $practical;
        $totalMax = $subject->theory_marks + $subject->practical_marks;
        $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;

        $grade = $isAbsent ? 'F' : CbseResult::computeGrade($percentage);
        $isPassed = !$isAbsent && ($percentage >= 33.0) && ($theory >= $subject->theory_passing_marks);
        $isCompartment = !$isAbsent && !$isPassed && ($percentage >= 25.0);

        $result->update([
            'theory_obtained'    => $theory,
            'practical_obtained' => $practical,
            'total_obtained'     => $totalObtained,
            'total_marks'        => $totalMax,
            'percentage'         => $percentage,
            'grade'              => $grade,
            'is_passed'          => $isPassed,
            'is_absent'          => $isAbsent,
            'is_compartment'     => $isCompartment,
        ]);

        return redirect()->route('cbse.results.index')->with('success', 'Result updated successfully.');
    }

    public function destroy(CbseResult $result)
    {
        $result->delete();
        return redirect()->route('cbse.results.index')->with('success', 'Result deleted successfully.');
    }

    public function showUpload()
    {
        $qualifications = CbseQualification::all();
        $subjects = CbseSubject::all();
        $academicYears = \App\Models\Cbse\CbseAcademicYear::orderBy('name', 'desc')->get();
        return view('cbse.results.upload', compact('qualifications', 'subjects', 'academicYears'));
    }

    public function storeUpload(Request $request)
    {
        $request->validate([
            'file_content'     => 'required|string',
            'subject_id'       => 'required|exists:cbse_subjects,id',
            'academic_year_id' => 'required|exists:cbse_academic_years,id',
        ]);

        $subject = CbseSubject::findOrFail($request->input('subject_id'));
        $yearId = $request->input('academic_year_id');
        $academicYear = \App\Models\Cbse\CbseAcademicYear::find($yearId);
        $examYear = $academicYear ? (int) substr($academicYear->name, -4) : date('Y');

        // Simple CSV parser from text content
        $lines = explode("\n", $request->input('file_content'));
        $successCount = 0;
        $errors = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = str_getcsv($line);
            if (count($parts) < 2) {
                $errors[] = "Line " . ($index + 1) . ": Invalid format. Must contain at least admission_number/roll_number, total_obtained OR admission_number/roll_number, theory_obtained, practical_obtained";
                continue;
            }

            $admissionNo = trim($parts[0]);

            // Match by admission_number or by roll_number in cbse_results
            $student = CbseStudent::where('admission_number', $admissionNo)->first();
            if (!$student) {
                $resultWithRoll = CbseResult::where('roll_number', $admissionNo)
                    ->where('academic_year_id', $yearId)
                    ->first();
                if ($resultWithRoll) {
                    $student = $resultWithRoll->student;
                }
            }

            if (!$student) {
                // Auto create student if not exists to make test entry easier
                $student = CbseStudent::create([
                    'admission_number' => $admissionNo,
                    'student_name'     => 'Student ' . $admissionNo,
                    'gender'           => 'M',
                    'qualification_type' => $subject->qualification->qualification_type,
                    'status'           => 'active',
                ]);
            }

            $totalMax = $subject->theory_marks + $subject->practical_marks;

            if (count($parts) == 2 || trim($parts[2]) === '') {
                // Single total score out of 100 (bifurcation can be uploaded later)
                $totalObtained = (float)trim($parts[1]);
                $theoryObt = null;
                $practObt = null;
                
                $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
                $grade = CbseResult::computeGrade($percentage);
                $isPassed = $percentage >= 33.0;
                $isCompartment = !$isPassed && ($percentage >= 25.0);
            } else {
                // Bifurcated marks (Theory & Practical/IA)
                $theoryObt = (float)trim($parts[1]);
                $practObt = (float)trim($parts[2]);
                $totalObtained = $theoryObt + $practObt;
                
                $percentage = $totalMax > 0 ? ($totalObtained / $totalMax) * 100 : 0;
                $grade = CbseResult::computeGrade($percentage);
                $isPassed = ($percentage >= 33.0) && ($theoryObt >= $subject->theory_passing_marks);
                $isCompartment = !$isPassed && ($percentage >= 25.0);
            }

            // Check if there is an existing result for this student, subject, and academic year
            $existingResult = CbseResult::where([
                'student_id'       => $student->id,
                'subject_id'       => $subject->id,
                'academic_year_id' => $yearId,
            ])->first();

            $rollNumber = $existingResult ? $existingResult->roll_number : (is_numeric($admissionNo) && strlen($admissionNo) === 8 ? $admissionNo : null);

            CbseResult::updateOrCreate(
                [
                    'student_id'       => $student->id,
                    'subject_id'       => $subject->id,
                    'academic_year_id' => $yearId,
                ],
                [
                    'qualification_id'   => $subject->qualification_id,
                    'exam_year'          => $examYear,
                    'roll_number'        => $rollNumber,
                    'theory_obtained'    => $theoryObt,
                    'practical_obtained' => $practObt,
                    'total_obtained'     => $totalObtained,
                    'total_marks'        => $totalMax,
                    'percentage'         => $percentage,
                    'grade'              => $grade,
                    'is_passed'          => $isPassed,
                    'is_absent'          => false,
                    'is_compartment'     => $isCompartment,
                ]
            );

            $successCount++;
        }

        if (count($errors) > 0) {
            return redirect()->route('cbse.results.index')->with('success', "Uploaded {$successCount} results with some errors.")
                ->withErrors($errors);
        }

        return redirect()->route('cbse.results.index')->with('success', "Successfully uploaded {$successCount} results.");
    }
}
