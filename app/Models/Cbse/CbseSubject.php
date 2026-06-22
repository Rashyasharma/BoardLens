<?php

namespace App\Models\Cbse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbseSubject extends Model
{
    use HasUuids;

    protected $table = 'cbse_subjects';

    protected $fillable = [
        'qualification_id',
        'subject_code',
        'subject_name',
        'theory_marks',
        'practical_marks',
        'practical_type',
        'passing_percentage',
        'theory_passing_marks',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'theory_marks'         => 'integer',
        'practical_marks'      => 'integer',
        'passing_percentage'   => 'decimal:2',
        'theory_passing_marks' => 'decimal:2',
    ];

    public function qualification(): BelongsTo
    {
        return $this->belongsTo(CbseQualification::class, 'qualification_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(CbseResult::class, 'subject_id');
    }

    public function getTotalMarksAttribute(): int
    {
        return $this->theory_marks + $this->practical_marks;
    }

    public function getPracticalTypeLabelAttribute(): string
    {
        return match ($this->practical_type) {
            'Practical'           => '🔬 Practical',
            'Project'             => '📁 Project',
            'Internal Assessment' => '📋 Internal Assessment',
            default               => $this->practical_type,
        };
    }
}
