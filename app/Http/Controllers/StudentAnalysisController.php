<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\SubjectResult;
use Illuminate\Http\Request;

class StudentAnalysisController extends Controller
{
    /**
     * Student-wise analysis
     */
    public function studentWise(Request $request)
    {
        $candidateNumber = $request->get('candidate_number');
        $candidateName = $request->get('candidate_name');

        if (!$candidateNumber && !$candidateName) {
            return view('analysis.student-wise', [
                'student' => null,
            ]);
        }

        $student = Candidate::where(function ($q) use ($candidateNumber, $candidateName) {
            if ($candidateNumber) {
                $q->where('candidate_number', $candidateNumber);
            }
            if ($candidateName) {
                $q->where('candidate_name', 'like', "%{$candidateName}%");
            }
        })
        ->with(['enrollments.results.subject.qualification', 'enrollments.results.series', 'school'])
        ->first();

        if (!$student) {
            return back()->with('error', 'Student not found');
        }

        // Group results by series/qualification
        $resultsByQualification = $student->enrollments
            ->groupBy('qualification_id')
            ->map(function ($enrollments) {
                return $enrollments->flatMap(function ($enrollment) {
                    return $enrollment->results;
                })->groupBy('series_id');
            });

        // Get performance trend
        $trend = $this->calculateTrend($student);

        return view('analysis.student-wise', [
            'student' => $student,
            'resultsByQualification' => $resultsByQualification,
            'trend' => $trend,
        ]);
    }

    private function calculateTrend(Candidate $student)
    {
        $results = $student->enrollments()
            ->with(['results.series'])
            ->get()
            ->flatMap->results
            ->sortBy('series.year')
            ->groupBy('series.year')
            ->map(function ($yearResults) {
                return $yearResults->avg('pum');
            });

        return $results->toArray();
    }
}
