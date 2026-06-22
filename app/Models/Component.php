<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Component extends Model
{
    use HasUuids;

    protected $fillable = [
        'subject_id',
        'component_set_id',
        'component_code',
        'component_name',
        'component_type',
        'component_label',
        'total_marks',
        'scaling_factor',
        'is_mandatory',
        'description',
        'level_id',
        'series_id',
    ];

    protected $casts = [
        'total_marks' => 'integer',
        'scaling_factor' => 'integer',
        'is_mandatory' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function componentSet(): BelongsTo
    {
        return $this->belongsTo(ComponentSet::class, 'component_set_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function componentMarks(): HasMany
    {
        return $this->hasMany(ComponentMarks::class);
    }
}
