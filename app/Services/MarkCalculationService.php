<?php

namespace App\Services;

use App\Models\ComponentMarks;
use App\Models\Component;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use App\Models\GradeThreshold;
use Illuminate\Support\Collection;

class MarkCalculationService
{
    /**
     * Calculate subject result from component marks
     */
    public function calculateSubjectResult(CandidateEnrollment $enrollment): SubjectResult
    {
        // Get all component marks for this enrollment
        $componentMarks = ComponentMarks::where('enrollment_id', $enrollment->id)
            ->with('component')
            ->get();

        if ($componentMarks->isEmpty()) {
            throw new \Exception("No component marks found for this enrollment");
        }

        // Calculate weighted marks
        $totalWeightedMark = $this->calculateWeightedMark($componentMarks);
        
        // Get component-wise totals
        $totalObtained = $componentMarks->sum('obtained_marks');
        $totalPossible = $componentMarks->sum('total_marks');

        if ($totalPossible == 0) {
            throw new \Exception("Total possible marks cannot be zero");
        }

        // Calculate percentage
        $percentage = ($totalObtained / $totalPossible) * 100;

        // Assign grade based on thresholds
        $grade = $this->assignGrade(
            $enrollment->subject_id,
            $enrollment->series_id,
            $percentage,
            $enrollment->qualification->qualification_type
        );

        // Determine if passed
        $subject = $enrollment->subject;
        $isPassed = $percentage >= $subject->passing_percentage;

        // Create or update result
        $result = SubjectResult::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'subject_id' => $enrollment->subject_id,
                'series_id' => $enrollment->series_id,
            ],
            [
                'total_obtained_marks' => $totalObtained,
                'total_marks' => $totalPossible,
                'overall_percentage' => round($percentage, 2),
                'uniform_mark' => round($totalWeightedMark, 2),
                'grade' => $grade,
                'is_passed' => $isPassed,
                'calculated_at' => now(),
            ]
        );

        return $result;
    }

    /**
     * Calculate weighted mark based on component scaling factors
     */
    private function calculateWeightedMark(Collection $componentMarks): float
    {
        $totalScaling = 0;
        $weightedSum = 0;

        foreach ($componentMarks as $mark) {
            $scalingFactor = $mark->component->scaling_factor ?? 1;
            $totalScaling += $scalingFactor;
            $weightedSum += ($mark->percentage * $scalingFactor);
        }

        if ($totalScaling == 0) {
            return 0;
        }

        return $weightedSum / $totalScaling;
    }

    /**
     * Assign grade based on percentage and thresholds
     */
    private function assignGrade(
        string $subjectId,
        string $seriesId,
        float $percentage,
        string $qualificationType
    ): string {
        // Get all thresholds for this series and subject, ordered by minimum percentage desc
        $thresholds = GradeThreshold::where('series_id', $seriesId)
            ->where('subject_id', $subjectId)
            ->where('qualification_type', $qualificationType)
            ->orderBy('minimum_percentage', 'desc')
            ->get();

        foreach ($thresholds as $threshold) {
            if ($percentage >= $threshold->minimum_percentage) {
                return $threshold->grade;
            }
        }

        // If no threshold matched, return U (ungraded)
        return 'U';
    }

    /**
     * Bulk calculate results for all enrollments in a series
     */
    public function calculateSeriesResults(string $seriesId): array
    {
        $enrollments = CandidateEnrollment::where('series_id', $seriesId)
            ->with(['candidate', 'subject', 'qualification'])
            ->get();

        $results = [];
        foreach ($enrollments as $enrollment) {
            try {
                $result = $this->calculateSubjectResult($enrollment);
                $results[] = [
                    'status' => 'success',
                    'enrollment_id' => $enrollment->id,
                    'result_id' => $result->id,
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'status' => 'failed',
                    'enrollment_id' => $enrollment->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
