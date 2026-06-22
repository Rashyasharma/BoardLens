<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use App\Models\Qualification;
use App\Models\ExamSeries;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of candidates.
     */
    public function index(Request $request)
    {
        $schoolId = auth()->user()?->school_id;
        $search = $request->input('search');
        $qualId = $request->input('qualification_id');
        $year = $request->input('year');
        $seriesId = $request->input('series_id');

        $query = Candidate::query();

        // Scope to school
        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        // Search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('candidate_name', 'like', '%' . $search . '%')
                  ->orWhere('candidate_number', 'like', '%' . $search . '%');
            });
        }

        // Year, Series, Qualification filters (via enrollments relationship)
        if ($qualId || $year || $seriesId) {
            $query->whereHas('enrollments', function($q) use ($qualId, $year, $seriesId) {
                if ($qualId) {
                    $q->where('qualification_id', $qualId);
                }
                if ($seriesId) {
                    $q->where('series_id', $seriesId);
                }
                if ($year) {
                    $q->whereHas('series', function($sq) use ($year) {
                        $sq->where('year', $year);
                    });
                }
            });
        }

        $candidates = $query->select('candidate_name')
            ->groupBy('candidate_name')
            ->orderBy('candidate_name')
            ->paginate(15);

        foreach ($candidates as $cand) {
            $studentRecords = Candidate::where('candidate_name', $cand->candidate_name)
                ->where(function($q) use ($schoolId) {
                    if ($schoolId) {
                        $q->where('school_id', $schoolId);
                    }
                })
                ->with('enrollments.series')
                ->get();
            
            $numberAndSeries = [];
            foreach ($studentRecords as $record) {
                $seriesNames = $record->enrollments
                    ->map(fn($e) => $e->series ? $e->series->series_name : null)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                if ($seriesNames) {
                    $numberAndSeries[] = "{$record->candidate_number} ({$seriesNames})";
                } else {
                    $numberAndSeries[] = "{$record->candidate_number}";
                }
            }
            $cand->candidate_numbers_with_series = implode(', ', array_unique($numberAndSeries));
        }

        // If AJAX request, return only the table partial
        if ($request->ajax()) {
            return view('students._table', compact('candidates'))->render();
        }

        // Metadata for filters
        $qualifications = Qualification::orderBy('qualification_name')->get();
        $years = ExamSeries::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');
        $seriesList = ExamSeries::orderBy('year', 'desc')
            ->orderByRaw("CASE WHEN month = 'March' THEN 1 WHEN month = 'June' THEN 2 WHEN month = 'November' THEN 3 ELSE 4 END")
            ->get();

        return view('students.index', compact(
            'candidates', 
            'search', 
            'qualifications', 
            'years', 
            'seriesList', 
            'qualId', 
            'year', 
            'seriesId'
        ));
    }

    /**
     * Display search results for Ajax queries.
     */
    public function search(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $term = $request->input('q');

        if (empty($term)) {
            return response()->json([]);
        }

        $query = Candidate::query();

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        $candidates = $query->where(function($q) use ($term) {
                $q->where('candidate_name', 'like', '%' . $term . '%')
                  ->orWhere('candidate_number', 'like', '%' . $term . '%');
            })
            ->take(10)
            ->get(['id', 'candidate_number', 'candidate_name']);

        return response()->json($candidates);
    }

    /**
     * Display a specific candidate's profile and results.
     */
    public function show(Candidate $candidate)
    {
        $schoolId = auth()->user()->school_id;
        if ($schoolId && $candidate->school_id !== $schoolId) {
            abort(403, 'Unauthorized access to student record.');
        }

        // Load enrollments with subjects, series, results, and component marks
        $enrollments = CandidateEnrollment::where('candidate_id', $candidate->id)
            ->whereNotNull('subject_id')
            ->with(['subject', 'series', 'qualification', 'subjectResult', 'componentMarks.component'])
            ->get();

        return view('students.show', compact('candidate', 'enrollments'));
    }

    /**
     * Show candidate edit form.
     */
    public function edit(Candidate $candidate)
    {
        $schoolId = auth()->user()->school_id;
        if ($schoolId && $candidate->school_id !== $schoolId) {
            abort(403, 'Unauthorized access to student record.');
        }

        return view('students.edit', compact('candidate'));
    }

    /**
     * Update candidate details.
     */
    public function update(Request $request, Candidate $candidate)
    {
        $schoolId = auth()->user()->school_id;
        if ($schoolId && $candidate->school_id !== $schoolId) {
            abort(403, 'Unauthorized access to student record.');
        }

        $data = $request->validate([
            'candidate_number' => 'required|string|max:255|unique:candidates,candidate_number,' . $candidate->id,
            'candidate_name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|in:M,F,O',
            'status' => 'required|in:active,inactive,graduated',
        ]);

        $candidate->update($data);

        return redirect()->route('students.show', $candidate->id)
            ->with('success', 'Candidate profile updated successfully.');
    }
}
