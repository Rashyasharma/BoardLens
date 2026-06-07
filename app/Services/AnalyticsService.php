<?php

namespace App\Services;

use App\Models\SubjectResult;
use App\Models\Candidate;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get grade distribution for filters
     */
    public function getGradeDistribution(array $filters): array
    {
        $query = SubjectResult::query();
        $query = $this->applyFilters($query, $filters);

        $distribution = $query->groupBy('grade')
            ->selectRaw('grade, COUNT(*) as count')
            ->orderByRaw("CASE grade WHEN 'A*' THEN 1 WHEN 'A' THEN 2 WHEN 'B' THEN 3 WHEN 'C' THEN 4 WHEN 'D' THEN 5 WHEN 'E' THEN 6 WHEN 'a' THEN 7 WHEN 'b' THEN 8 WHEN 'c' THEN 9 WHEN 'd' THEN 10 WHEN 'e' THEN 11 WHEN 'U' THEN 12 ELSE 13 END")
            ->get()
            ->pluck('count', 'grade')
            ->toArray();

        // Ensure all possible grades exist in output for chart continuity
        $allGrades = ['A*', 'A', 'B', 'C', 'D', 'E', 'a', 'b', 'c', 'd', 'e', 'U'];
        $completeDistribution = [];
        foreach ($allGrades as $grade) {
            $completeDistribution[$grade] = $distribution[$grade] ?? 0;
        }

        return $completeDistribution;
    }

    /**
     * Get pass/fail statistics
     */
    public function getPassFailStats(array $filters): array
    {
        $query = SubjectResult::query();
        $query = $this->applyFilters($query, $filters);

        $total = $query->count();
        $passed = (clone $query)->where('is_passed', true)->count();
        $failed = $total - $passed;

        return [
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed,
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get subject-wise performance
     */
    public function getSubjectPerformance(array $filters): Collection
    {
        $query = SubjectResult::query()->with('subject');
        $query = $this->applyFilters($query, $filters);

        $results = $query->get();

        return $results->groupBy('subject_id')->map(function ($subjectResults) {
            $totalStudents = $subjectResults->count();
            $avgPercentage = $subjectResults->avg('overall_percentage');
            $minPercentage = $subjectResults->min('overall_percentage');
            $maxPercentage = $subjectResults->max('overall_percentage');
            $passCount = $subjectResults->where('is_passed', true)->count();

            // Calculate Standard Deviation in PHP (database-agnostic)
            $squareDiffs = $subjectResults->pluck('overall_percentage')->map(function ($x) use ($avgPercentage) {
                return pow($x - $avgPercentage, 2);
            });
            $stdDev = $totalStudents > 1 ? sqrt($squareDiffs->sum() / ($totalStudents - 1)) : 0;

            return (object)[
                'subject_id' => $subjectResults->first()->subject_id,
                'subject' => $subjectResults->first()->subject,
                'total_students' => $totalStudents,
                'avg_percentage' => round($avgPercentage, 2),
                'min_percentage' => round($minPercentage, 2),
                'max_percentage' => round($maxPercentage, 2),
                'std_dev' => round($stdDev, 2),
                'pass_count' => $passCount,
                'pass_rate' => $totalStudents > 0 ? round(($passCount / $totalStudents) * 100, 2) : 0,
            ];
        })->values();
    }

    /**
     * Get year-on-year comparison
     */
    public function getYearOnYearComparison(string $subjectId): array
    {
        $results = SubjectResult::where('subject_id', $subjectId)
            ->with('series')
            ->get();

        return $results->groupBy(function ($result) {
            return $result->series->year ?? Carbon::parse($result->created_at)->year;
        })->map(function ($yearResults, $year) {
            $total = $yearResults->count();
            $passed = $yearResults->where('is_passed', true)->count();
            $avgPercentage = $yearResults->avg('overall_percentage');

            return [
                'year' => (int)$year,
                'total' => $total,
                'passed' => $passed,
                'avg_percentage' => round($avgPercentage, 2),
                'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 2) : 0,
            ];
        })->sortKeys()->values()->toArray();
    }

    /**
     * Get statistical summary
     */
    public function getStatisticalSummary(array $filters): array
    {
        $query = SubjectResult::query();
        $query = $this->applyFilters($query, $filters);

        $results = $query->get();
        $scores = $results->pluck('overall_percentage');

        return [
            'total_students' => $results->count(),
            'average_percentage' => $scores->count() > 0 ? round($scores->avg(), 2) : 0,
            'median_percentage' => $scores->count() > 0 ? round($this->calculateMedian($scores), 2) : 0,
            'std_deviation' => $scores->count() > 0 ? round($this->calculateStdDev($scores), 2) : 0,
            'highest_score' => $scores->count() > 0 ? round($scores->max(), 2) : 0,
            'lowest_score' => $scores->count() > 0 ? round($scores->min(), 2) : 0,
        ];
    }

    /**
     * Apply filter conditions to query
     */
    private function applyFilters($query, array $filters)
    {
        if (!empty($filters['year'])) {
            $query->whereHas('series', function ($q) use ($filters) {
                $q->where('year', $filters['year']);
            });
        }

        if (!empty($filters['series_id'])) {
            $query->where('series_id', $filters['series_id']);
        }

        if (!empty($filters['subject_id'])) {
            $query->where('subject_id', $filters['subject_id']);
        }

        if (!empty($filters['qualification_type'])) {
            $query->whereHas('subject', function ($q) use ($filters) {
                $q->whereHas('qualification', function ($q2) use ($filters) {
                    $q2->where('qualification_type', $filters['qualification_type']);
                });
            });
        }

        if (!empty($filters['grade'])) {
            $query->where('grade', $filters['grade']);
        }

        if (!empty($filters['school_id'])) {
            $query->whereHas('enrollment.candidate', function ($q) use ($filters) {
                $q->where('school_id', $filters['school_id']);
            });
        }

        return $query;
    }

    /**
     * Calculate median
     */
    private function calculateMedian(Collection $values): float
    {
        $sorted = $values->sort()->values();
        $count = $sorted->count();
        if ($count == 0) {
            return 0;
        }
        $middle = intdiv($count, 2);

        if ($count % 2 == 1) {
            return $sorted[$middle];
        }

        return ($sorted[$middle - 1] + $sorted[$middle]) / 2;
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStdDev(Collection $values): float
    {
        $count = $values->count();
        if ($count <= 1) {
            return 0;
        }
        $avg = $values->avg();
        $squareDiffs = $values->map(function ($x) use ($avg) {
            return pow($x - $avg, 2);
        });
        return sqrt($squareDiffs->sum() / ($count - 1));
    }
}
