<?php

namespace App\Http\Controllers;

use App\Models\ExamSeries;
use App\Models\Qualification;
use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use Illuminate\Http\Request;

class StudentEntryController extends Controller
{
    /**
     * Show the enrollment grid for a specific series.
     */
    public function show(ExamSeries $examSeries)
    {
        $series = $examSeries;

        $igcseQual = Qualification::where('qualification_type', 'IGCSE')->firstOrFail();
        $gceQual   = Qualification::where('qualification_type', 'AS_A_LEVEL')->firstOrFail();

        // Enrolled candidates for IGCSE
        $igcseEnrollments = CandidateEnrollment::with('candidate')
            ->where('series_id', $series->id)
            ->where('qualification_id', $igcseQual->id)
            ->get()
            ->unique('candidate_id')
            ->sortBy(fn($e) => $e->candidate->candidate_number);

        // Enrolled candidates for GCE AS & A Level
        $gceEnrollments = CandidateEnrollment::with('candidate')
            ->where('series_id', $series->id)
            ->where('qualification_id', $gceQual->id)
            ->get()
            ->unique('candidate_id')
            ->sortBy(fn($e) => $e->candidate->candidate_number);

        // Subjects list for IGCSE & GCE
        $igcseSubjects = \App\Models\Subject::where('qualification_id', $igcseQual->id)->orderBy('subject_name')->get();
        $gceSubjects = \App\Models\Subject::where('qualification_id', $gceQual->id)->orderBy('subject_name')->get();

        // Map registered subject IDs per candidate ID
        $candidateSubjectsMap = [];
        $allEnrollments = CandidateEnrollment::where('series_id', $series->id)->get();
        foreach ($allEnrollments as $e) {
            if ($e->subject_id) {
                $candidateSubjectsMap[$e->candidate_id][] = $e->subject_id;
            }
        }

        return view('student-entries.show', compact(
            'series',
            'igcseQual',
            'gceQual',
            'igcseEnrollments',
            'gceEnrollments',
            'igcseSubjects',
            'gceSubjects',
            'candidateSubjectsMap'
        ));
    }

    /**
     * Parse raw text or upload CSV to create candidates and enroll them.
     */
    public function upload(Request $request, ExamSeries $examSeries)
    {
        $request->validate([
            'qualification_id' => 'required|exists:qualifications,id',
            'raw_text'         => 'nullable|string',
            'candidate_file'   => 'nullable|file|mimes:csv,txt',
        ]);

        $schoolId = auth()->user()->school_id;
        if (!$schoolId) {
            $schoolId = \App\Models\School::first()->id;
        }

        $qualificationId = $request->qualification_id;
        $processedCount = 0;

        // 1. Process manual list textareas
        if ($request->filled('raw_text')) {
            $processedCount += $this->processRawCandidatesText($request->raw_text, $schoolId, $examSeries->id, $qualificationId);
        }

        // 2. Process CSV file
        if ($request->hasFile('candidate_file')) {
            $file = $request->file('candidate_file');
            $path = $file->getRealPath();
            $handle = fopen($path, 'r');
            
            // Skip header if it exists
            $header = fgetcsv($handle);
            
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) >= 2) {
                    $num = trim($row[0]);
                    $name = trim($row[1]);
                    
                    if (!empty($num) && !empty($name)) {
                        $candidate = Candidate::findOrCreateByNameAndNumber($schoolId, (string)$num, (string)$name);

                        CandidateEnrollment::firstOrCreate([
                            'candidate_id'     => $candidate->id,
                            'series_id'        => $examSeries->id,
                            'qualification_id' => $qualificationId,
                            'subject_id'       => null,
                        ], [
                            'enrolled_date'     => now()->toDateString(),
                            'enrollment_status' => 'enrolled',
                        ]);

                        $processedCount++;
                    }
                }
            }
            fclose($handle);
        }

        if ($processedCount === 0) {
            return back()->withErrors(['error' => 'No valid candidates were entered or imported. Check the formatting and try again.'])->withInput();
        }

        return back()->with('success', "{$processedCount} candidates successfully imported/enrolled.");
    }

    /**
     * Unenroll a candidate from a series.
     */
    public function unenroll(Request $request, ExamSeries $examSeries, Candidate $candidate)
    {
        $request->validate([
            'qualification_id' => 'required|exists:qualifications,id',
        ]);

        CandidateEnrollment::where('candidate_id', $candidate->id)
            ->where('series_id', $examSeries->id)
            ->where('qualification_id', $request->qualification_id)
            ->delete();

        return back()->with('success', 'Candidate removed from this series.');
    }

    /**
     * Helper to parse pasted raw candidates text (handles comma and tab separation).
     */
    private function processRawCandidatesText(string $text, string $schoolId, string $seriesId, string $qualificationId): int
    {
        $count = 0;
        $lines = explode("\n", $text);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Check if tab-separated first, else comma-separated
            if (str_contains($line, "\t")) {
                $parts = explode("\t", $line);
            } else {
                $parts = str_getcsv($line);
            }
            
            if (count($parts) >= 2) {
                $num = trim($parts[0]);
                $name = trim($parts[1]);
                
                if (!empty($num) && !empty($name)) {
                    $candidate = Candidate::findOrCreateByNameAndNumber($schoolId, (string)$num, (string)$name);

                    CandidateEnrollment::firstOrCreate([
                        'candidate_id'     => $candidate->id,
                        'series_id'        => $seriesId,
                        'qualification_id' => $qualificationId,
                        'subject_id'       => null,
                    ], [
                        'enrolled_date'     => now()->toDateString(),
                        'enrollment_status' => 'enrolled',
                    ]);

                    $count++;
                }
            }
        }
        
        return $count;
    }

    /**
     * AJAX handler to register or unregister a candidate for a specific subject in a series.
     */
    public function toggleSubject(Request $request, ExamSeries $examSeries)
    {
        $request->validate([
            'candidate_id'     => 'required|exists:candidates,id',
            'subject_id'       => 'required|exists:subjects,id',
            'registered'       => 'required|boolean',
            'qualification_id' => 'required|exists:qualifications,id',
        ]);

        $candidateId = $request->candidate_id;
        $subjectId = $request->subject_id;
        $registered = $request->boolean('registered');
        $qualId = $request->qualification_id;

        // Ensure general enrollment exists
        CandidateEnrollment::firstOrCreate([
            'candidate_id'     => $candidateId,
            'series_id'        => $examSeries->id,
            'qualification_id' => $qualId,
            'subject_id'       => null,
        ], [
            'enrolled_date'     => now()->toDateString(),
            'enrollment_status' => 'enrolled',
        ]);

        if ($registered) {
            CandidateEnrollment::firstOrCreate([
                'candidate_id'     => $candidateId,
                'series_id'        => $examSeries->id,
                'qualification_id' => $qualId,
                'subject_id'       => $subjectId,
            ], [
                'enrolled_date'     => now()->toDateString(),
                'enrollment_status' => 'enrolled',
            ]);
        } else {
            CandidateEnrollment::where('candidate_id', $candidateId)
                ->where('series_id', $examSeries->id)
                ->where('subject_id', $subjectId)
                ->delete();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Manually add/register a candidate and enroll them in selected subjects.
     */
    public function addCandidate(Request $request, ExamSeries $examSeries)
    {
        $request->validate([
            'qualification_id' => 'required|exists:qualifications,id',
            'candidate_number' => 'required|string',
            'candidate_name'   => 'required|string',
            'subjects'         => 'nullable|array',
            'subjects.*'       => 'exists:subjects,id',
        ]);

        $schoolId = auth()->user()->school_id;
        if (!$schoolId) {
            $schoolId = \App\Models\School::first()->id;
        }

        $paddedNum = str_pad(trim($request->candidate_number), 4, '0', STR_PAD_LEFT);
        $nameClean = trim($request->candidate_name);

        $candidate = Candidate::findOrCreateByNameAndNumber($schoolId, $paddedNum, $nameClean);

        // General Enrollment
        CandidateEnrollment::firstOrCreate([
            'candidate_id'     => $candidate->id,
            'series_id'        => $examSeries->id,
            'qualification_id' => $request->qualification_id,
            'subject_id'       => null,
        ], [
            'enrolled_date'     => now()->toDateString(),
            'enrollment_status' => 'enrolled',
        ]);

        // Specific subject enrollments
        $subjects = $request->subjects ?? [];
        foreach ($subjects as $subId) {
            CandidateEnrollment::firstOrCreate([
                'candidate_id'     => $candidate->id,
                'series_id'        => $examSeries->id,
                'qualification_id' => $request->qualification_id,
                'subject_id'       => $subId,
            ], [
                'enrolled_date'     => now()->toDateString(),
                'enrollment_status' => 'enrolled',
            ]);
        }

        return back()->with('success', 'Candidate successfully registered.');
    }
}
