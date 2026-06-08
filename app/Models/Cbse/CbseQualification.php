<?php

namespace App\Models\Cbse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbseQualification extends Model
{
    use HasUuids;

    protected $table = 'cbse_qualifications';

    protected $fillable = [
        'qualification_type',
        'qualification_name',
        'board_code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subjects(): HasMany
    {
        return $this->hasMany(CbseSubject::class, 'qualification_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(CbseResult::class, 'qualification_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->qualification_type) {
            'CLASS_10' => 'Class 10 (Secondary)',
            'CLASS_12' => 'Class 12 (Senior Secondary)',
            default    => $this->qualification_type,
        };
    }

    public function getColorClassAttribute(): string
    {
        return match ($this->qualification_type) {
            'CLASS_10' => 'indigo',
            'CLASS_12' => 'orange',
            default    => 'slate',
        };
    }
}
