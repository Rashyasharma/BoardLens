<?php

namespace App\Http\Controllers;

use App\Models\ExamSeries;
use App\Models\Qualification;
use App\Models\CandidateEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExamSeriesController extends Controller
{
    /**
     * List all exam series.
     */
    public function index(Request $request)
    {
        $query = ExamSeries::with(['enrollments'])
            ->orderBy('year', 'desc')
            ->orderByRaw("CASE month WHEN 'November' THEN 1 WHEN 'June' THEN 2 WHEN 'March' THEN 3 END");

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $seriesList = $query->get()->map(function ($s) {
            $enrollmentCount = $s->enrollments->count();
            $uniqueCandidates = $s->enrollments->pluck('candidate_id')->unique()->count();

            // Calculate average PUM
            $avgPum = \App\Models\SubjectResult::where('series_id', $s->id)->avg('pum');
            
            // Calculate pass rate
            $totalResults = \App\Models\SubjectResult::where('series_id', $s->id)->count();
            $passedResults = \App\Models\SubjectResult::where('series_id', $s->id)->where('is_passed', true)->count();
            $passRate = $totalResults > 0 ? round(($passedResults / $totalResults) * 100, 1) : null;

            // Calculate distinct subjects count
            $subjectsCount = \App\Models\CandidateEnrollment::where('series_id', $s->id)
                ->whereNotNull('subject_id')
                ->distinct('subject_id')
                ->count('subject_id');

            return [
                'id'               => $s->id,
                'series_code'      => $s->series_code,
                'year'             => $s->year,
                'month'            => $s->month,
                'label'            => $this->monthLabel($s->month) . ' ' . $s->year,
                'is_active'        => $s->is_active,
                'enrollments'      => $enrollmentCount,
                'candidates'       => $uniqueCandidates,
                'avg_pum'          => $avgPum ? round($avgPum, 1) : 'N/A',
                'pass_rate'        => $passRate !== null ? "{$passRate}%" : 'N/A',
                'subjects_count'   => $subjectsCount,
            ];
        });

        $seriesGrouped = $seriesList->groupBy('year');
        $years = ExamSeries::distinct()->orderBy('year', 'desc')->pluck('year');

        return view('exam-series.index', compact('seriesGrouped', 'years'));
    }

    /**
     * Show form to create a new exam series.
     */
    public function create()
    {
        $years = range(2030, 2018);
        return view('exam-series.create', compact('years'));
    }

    /**
     * Store new exam series.
     */
    public function store(Request $request)
    {
        $request->validate([
            'year'                     => 'required|integer|min:2000|max:2100',
            'month'                    => 'required|in:March,June,November',
            'deadline_for_entry'       => 'nullable|date',
            'result_publication_date'  => 'nullable|date',
            'is_active'                => 'boolean',
        ]);

        // Check unique combination
        $exists = ExamSeries::where('year', $request->year)
            ->where('month', $request->month)
            ->exists();

        if ($exists) {
            return back()->withErrors(['month' => 'A series already exists for this year and month.'])
                         ->withInput();
        }

        $monthCode = strtoupper(substr($request->month, 0, 3)); // MAR / JUN / NOV
        $seriesCode = "{$monthCode}-{$request->year}";

        // Handle duplicate series codes
        if (ExamSeries::where('series_code', $seriesCode)->exists()) {
            $seriesCode .= '-' . strtoupper(Str::random(4));
        }

        ExamSeries::create([
            'series_code'             => $seriesCode,
            'year'                    => $request->year,
            'month'                   => $request->month,
            'deadline_for_entry'      => $request->deadline_for_entry,
            'result_publication_date' => $request->result_publication_date,
            'is_active'               => $request->boolean('is_active', true),
        ]);

        return redirect()->route('exam-series.index')
            ->with('success', "Exam series created successfully: {$seriesCode}");
    }

    /**
     * Show edit form.
     */
    public function edit(ExamSeries $examSeries)
    {
        $years = range(2030, 2018);
        $series = $examSeries;

        return view('exam-series.edit', compact('series', 'years'));
    }

    /**
     * Update existing series.
     */
    public function update(Request $request, ExamSeries $examSeries)
    {
        $request->validate([
            'year'                    => 'required|integer|min:2000|max:2100',
            'month'                   => 'required|in:March,June,November',
            'deadline_for_entry'      => 'nullable|date',
            'result_publication_date' => 'nullable|date',
            'is_active'               => 'boolean',
        ]);

        // Check unique combination (excluding self)
        $exists = ExamSeries::where('year', $request->year)
            ->where('month', $request->month)
            ->where('id', '!=', $examSeries->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['month' => 'A series already exists for this year and month.'])
                         ->withInput();
        }

        $examSeries->update([
            'year'                    => $request->year,
            'month'                   => $request->month,
            'deadline_for_entry'      => $request->deadline_for_entry,
            'result_publication_date' => $request->result_publication_date,
            'is_active'               => $request->boolean('is_active', true),
        ]);

        return redirect()->route('exam-series.index')
            ->with('success', 'Exam series updated successfully.');
    }

    /**
     * Delete a series.
     */
    public function destroy(ExamSeries $examSeries)
    {
        $examSeries->delete();
        return redirect()->route('exam-series.index')
            ->with('success', 'Exam series deleted.');
    }

    /**
     * Helper: human-readable month label.
     */
    private function monthLabel(string $month): string
    {
        return match($month) {
            'March'    => 'February/March',
            'June'     => 'May/June',
            'November' => 'October/November',
            default    => $month,
        };
    }
}
