<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Subject extends Model
{
    use HasUuids;

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

    protected $fillable = [
        'subject_code',
        'subject_name',
        'qualification_id',
        'total_marks',
        'passing_percentage',
        'description'
    ];

    protected $casts = [
        'passing_percentage' => 'decimal:2',
        'total_marks' => 'integer',
    ];

    public function qualification(): BelongsTo
    {
        return $this->belongsTo(Qualification::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(Component::class);
    }

    public function componentSets(): HasMany
    {
        return $this->hasMany(ComponentSet::class);
    }

    public function gradeThresholds(): HasMany
    {
        return $this->hasMany(GradeThreshold::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CandidateEnrollment::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(SubjectResult::class);
    }

    // Get results with detailed breakdown
    public function resultsWithComponents($seriesId = null, $year = null, $month = null)
    {
        $query = $this->results()
            ->with(['enrollment.candidate', 'componentMarks.component'])
            ->whereHas('series', function ($q) use ($year, $month) {
                if ($year) $q->where('year', $year);
                if ($month) $q->where('month', $month);
            });

        if ($seriesId) {
            $query->where('series_id', $seriesId);
        }

        return $query->get();
    }
}
