<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ExamSeries extends Model
{
    use HasUuids;

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

    protected $table = 'exam_series';

    protected $fillable = [
        'series_code',
        'year',
        'month',
        'series_name',
        'deadline_for_entry',
        'result_publication_date',
        'is_active'
    ];

    protected $casts = [
        'deadline_for_entry' => 'date',
        'result_publication_date' => 'date',
        'is_active' => 'boolean',
        'year' => 'integer',
    ];

    public function enrollments()
    {
        return $this->hasMany(CandidateEnrollment::class, 'series_id');
    }

    public function results()
    {
        return $this->hasMany(SubjectResult::class, 'series_id');
    }

    public function gradeThresholds()
    {
        return $this->hasMany(GradeThreshold::class, 'series_id');
    }

    // Accessor: Generate series name from year and month
    public function getSeriesNameAttribute()
    {
        return "{$this->month} {$this->year}";
    }

    // Scope: Get years
    public static function getYearsForQualification($qualificationId = null)
    {
        return static::distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }

    // Scope: Get months for a year
    public static function getMonthsForQualificationYear($qualificationId = null, $year = null)
    {
        $yearVal = $year;
        if ($yearVal === null && $qualificationId !== null) {
            $yearVal = $qualificationId;
        }

        $query = static::query();
        if ($yearVal) {
            $query->where('year', $yearVal);
        }

        $months = $query->distinct()
            ->pluck('month')
            ->toArray();

        $order = ['March', 'June', 'November'];
        usort($months, function($a, $b) use ($order) {
            return array_search($a, $order) <=> array_search($b, $order);
        });

        return $months;
    }
}
