<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComponentMarks extends Model
{
    use HasUuids;

    protected $table = 'component_marks';

    protected $fillable = [
        'subject_result_id',
        'enrollment_id',
        'component_id',
        'obtained_marks',
        'total_marks',
        'percentage',
        'grade',
        'remarks',
        'uploaded_by',
        'uploaded_at',
    ];

    protected $casts = [
        'obtained_marks' => 'decimal:2',
        'percentage' => 'decimal:2',
        'uploaded_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->total_marks > 0) {
                $model->percentage = ($model->obtained_marks / $model->total_marks) * 100;
            } else {
                $model->percentage = 0;
            }
        });
    }

    public function subjectResult(): BelongsTo
    {
        return $this->belongsTo(SubjectResult::class, 'subject_result_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(CandidateEnrollment::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
