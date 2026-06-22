<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Qualification extends Model
{
    use HasUuids;

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

    protected $fillable = [
        'qualification_type',
        'qualification_name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function examSeries(): HasMany
    {
        return $this->hasMany(ExamSeries::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function getTypeDisplayAttribute(): string
    {
        $map = [
            'IGCSE'      => 'IGCSE',
            'AS_A_LEVEL' => 'GCE AS and A Level',
            // Legacy — kept for any old rows
            'AS_LEVEL'   => 'GCE AS Level',
            'A_LEVEL'    => 'GCE A Level',
        ];
        return $map[$this->qualification_type] ?? $this->qualification_type;
    }

    // Get subjects with their summary stats
    public function subjectsWithStats()
    {
        $currentYear = (int) now()->format('Y');

        return $this->subjects()
            ->with([
                'componentSets' => function ($query) {
                    $query->with(['components' => function ($q) {
                        $q->orderBy('component_code');
                    }])
                    // Latest non-default set first (highest end_year wins), default last as fallback
                    ->orderByRaw("CASE WHEN is_default = 0 THEN 0 ELSE 1 END ASC")
                    ->orderByRaw("COALESCE(end_year, start_year, 0) DESC");
                },
                'results' => function ($query) {
                    $query->latest();
                }
            ])
            ->get()
            ->map(function ($subject) use ($currentYear) {
                // Use the latest non-default set; fall back to default if none
                $latestSet = $subject->componentSets
                    ->where('is_default', false)
                    ->sortByDesc(fn($s) => $s->end_year ?? $s->start_year ?? 0)
                    ->first();
                $representativeSet = $latestSet ?? $subject->componentSets->where('is_default', true)->first();
                $components = $representativeSet ? $representativeSet->components : collect();

                // Check if ANY set covers the current year
                $currentYearCovered = $subject->componentSets->contains(function ($set) use ($currentYear) {
                    $start = $set->start_year ?? 0;
                    $end   = $set->end_year ?? PHP_INT_MAX;
                    return $currentYear >= $start && $currentYear <= $end;
                });

                return [
                    'id'                       => $subject->id,
                    'name'                     => $subject->subject_name,
                    'code'                     => $subject->subject_code,
                    'components'               => $components,
                    'total_students'           => $subject->results->count(),
                    'grade_distribution'       => $this->getGradeDistribution($subject),
                    'statistics'               => $this->getSubjectStats($subject),
                    'missing_current_year_set' => !$currentYearCovered,
                ];
            });
    }

    private function getGradeDistribution($subject)
    {
        return $subject->results()
            ->groupBy('grade')
            ->selectRaw('grade, COUNT(*) as count')
            ->pluck('count', 'grade')
            ->toArray();
    }

    private function getSubjectStats($subject)
    {
        $results = $subject->results;

        if ($results->isEmpty()) {
            return null;
        }

        $totalCount = $results->count();
        $passedCount = $results->where('is_passed', true)->count();

        $pumResults = $results->filter(function ($r) {
            return $r->pum !== null && $r->pum > 0;
        });

        return [
            'pass_rate' => $totalCount > 0 ? ($passedCount / $totalCount) * 100 : 0,
            'avg_pum' => $pumResults->isNotEmpty() ? $pumResults->avg('pum') : 0,
            'highest' => $pumResults->isNotEmpty() ? $pumResults->max('pum') : 0,
            'lowest' => $pumResults->isNotEmpty() ? $pumResults->min('pum') : 0,
        ];
    }
}
