<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CandidateEnrollment extends Model
{
    use HasUuids;

    protected $table = 'candidate_enrollments';

    protected $fillable = [
        'candidate_id',
        'series_id',
        'qualification_id',
        'subject_id',
        'enrollment_status',
        'enrolled_date'
    ];

    protected $casts = [
        'enrolled_date' => 'date',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(ExamSeries::class, 'series_id');
    }

    public function qualification(): BelongsTo
    {
        return $this->belongsTo(Qualification::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function componentMarks(): HasMany
    {
        return $this->hasMany(ComponentMarks::class, 'enrollment_id');
    }

    public function subjectResult(): HasOne
    {
        return $this->hasOne(SubjectResult::class, 'enrollment_id');
    }
}
