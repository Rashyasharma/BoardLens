<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class GradeThreshold extends Model
{
    use HasUuids;

    protected $table = 'grade_thresholds';

    protected $fillable = [
        'series_id',
        'subject_id',
        'grade',
        'qualification_type',
        'minimum_percentage',
        'maximum_percentage',
        'minimum_marks',
        'maximum_marks',
        'created_by'
    ];

    protected $casts = [
        'minimum_percentage' => 'decimal:2',
        'maximum_percentage' => 'decimal:2',
        'minimum_marks' => 'integer',
        'maximum_marks' => 'integer',
    ];

    public function series(): BelongsTo
    {
        return $this->belongsTo(ExamSeries::class, 'series_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
