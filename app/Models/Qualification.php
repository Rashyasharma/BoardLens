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
        return $this->subjects()
            ->with(['components', 'results' => function ($query) {
                $query->latest();
            }])
            ->get()
            ->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->subject_name,
                    'code' => $subject->subject_code,
                    'components' => $subject->components,
                    'total_students' => $subject->results->count(),
                    'grade_distribution' => $this->getGradeDistribution($subject),
                    'statistics' => $this->getSubjectStats($subject),
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

        return [
            'pass_rate' => $totalCount > 0 ? ($passedCount / $totalCount) * 100 : 0,
            'avg_pum' => $results->avg('pum'),
            'highest' => $results->max('pum'),
            'lowest' => $results->min('pum'),
        ];
    }
}
