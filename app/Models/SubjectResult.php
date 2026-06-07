<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectResult extends Model
{
    use HasUuids;

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

    protected $table = 'subject_results';

    protected $fillable = [
        'enrollment_id',
        'subject_id',
        'series_id',
        'grade',
        'pum',
        'total_obtained_marks',
        'total_marks',
        'overall_percentage',
        'calculated_uniform_mark',
        'is_passed',
        'remarks',
        'status',
        'result_uploaded_at',
        'components_uploaded_at',
        'uploaded_by',
    ];

    protected $casts = [
        'pum' => 'decimal:2',
        'total_obtained_marks' => 'decimal:2',
        'overall_percentage' => 'decimal:2',
        'calculated_uniform_mark' => 'decimal:2',
        'is_passed' => 'boolean',
        'result_uploaded_at' => 'datetime',
        'components_uploaded_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $subject = $model->subject ?: \App\Models\Subject::find($model->subject_id);
            if ($subject) {
                $model->is_passed = $model->pum >= $subject->passing_percentage;
            }
        });
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CandidateEnrollment::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(ExamSeries::class);
    }

    public function componentMarks(): HasMany
    {
        return $this->hasMany(ComponentMarks::class, 'subject_result_id');
    }

    public function candidate()
    {
        return $this->enrollment->candidate();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Check if all components are uploaded
    public function hasAllComponentsUploaded(): bool
    {
        $subject = $this->subject;
        $componentCount = $subject->components()->count();
        $uploadedCount = $this->componentMarks()->count();

        return $componentCount > 0 && $componentCount === $uploadedCount;
    }

    // Calculate overall percentage from component marks
    public function calculateFromComponents(): void
    {
        $componentMarks = $this->componentMarks()->with('component')->get();

        if ($componentMarks->isEmpty()) {
            return;
        }

        $totalObtained = $componentMarks->sum('obtained_marks');
        $totalPossible = $componentMarks->sum('total_marks');

        if ($totalPossible > 0) {
            $percentage = ($totalObtained / $totalPossible) * 100;
            
            // Calculate weighted percentage (uniform mark) using scaling factors
            $totalScaling = 0;
            $weightedSum = 0;
            foreach ($componentMarks as $mark) {
                $scalingFactor = $mark->component->scaling_factor ?? 1;
                $totalScaling += $scalingFactor;
                $weightedSum += ($mark->percentage * $scalingFactor);
            }
            $calculatedUniformMark = $totalScaling > 0 ? ($weightedSum / $totalScaling) : $percentage;
            
            $this->update([
                'total_obtained_marks' => $totalObtained,
                'total_marks' => $totalPossible,
                'overall_percentage' => round($percentage, 2),
                'calculated_uniform_mark' => round($calculatedUniformMark, 2),
                'components_uploaded_at' => now(),
                'status' => 'component_marks_added',
            ]);
        }
    }

    // Mark as passed/failed
    public function updatePassStatus()
    {
        $subject = $this->subject ?: \App\Models\Subject::find($this->subject_id);
        if ($subject) {
            $this->is_passed = $this->pum >= $subject->passing_percentage;
            $this->save();
        }
    }

    // Scope for analysis queries
    public static function scopeForAnalysis($query, $subjectId = null, $year = null, $month = null, $seriesId = null)
    {
        return $query->when($subjectId, function ($q) use ($subjectId) {
            $q->where('subject_id', $subjectId);
        })
        ->when($seriesId, function ($q) use ($seriesId) {
            $q->where('series_id', $seriesId);
        })
        ->when($year || $month, function ($q) use ($year, $month) {
            $q->whereHas('series', function ($sq) use ($year, $month) {
                if ($year) $sq->where('year', $year);
                if ($month) $sq->where('month', $month);
            });
        });
    }

    // Legacy Scopes
    public function scopeFilterByYear($query, $year)
    {
        return $query->whereHas('series', function ($q) use ($year) {
            $q->where('year', $year);
        });
    }

    public function scopeFilterByMonth($query, $month)
    {
        return $query->whereHas('series', function ($q) use ($month) {
            $q->where('month', $month);
        });
    }

    public function scopeFilterBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeFilterBySeries($query, $seriesId)
    {
        return $query->where('series_id', $seriesId);
    }
}
