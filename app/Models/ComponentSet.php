<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ComponentSet extends Model
{
    use HasUuids;

    protected $fillable = [
        'subject_id',
        'start_year',
        'end_year',
        'label',
        'is_default',
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year' => 'integer',
        'is_default' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(Component::class, 'component_set_id');
    }

    /**
     * Generate a display label like "2018 – 2026" or "2023 – Present"
     */
    public function getDisplayLabelAttribute(): string
    {
        // If we have a label already stored, use it
        if ($this->label && $this->label !== 'Default') {
            return $this->label;
        }

        // Fallback: if truly no years set
        if ($this->start_year === null && $this->end_year === null) {
            return 'Default (All Years)';
        }

        $start = $this->start_year ?? '?';
        $end = $this->end_year ? $this->end_year : 'Present';

        return "{$start} – {$end}";
    }

    /**
     * Check if this set's year range overlaps with a given range.
     * Used for validation when creating/editing sets.
     */
    public function overlapsWith(int $startYear, ?int $endYear): bool
    {
        // Default sets (null start/end) don't participate in overlap checks
        if ($this->start_year === null && $this->end_year === null) {
            return false;
        }

        $thisStart = $this->start_year;
        $thisEnd = $this->end_year ?? PHP_INT_MAX;
        $otherEnd = $endYear ?? PHP_INT_MAX;

        return $thisStart <= $otherEnd && $startYear <= $thisEnd;
    }

    /**
     * Check if this set covers a specific year.
     */
    public function coversYear(int $year): bool
    {
        // is_default always acts as a catch-all fallback regardless of stored years
        if ($this->is_default) {
            return true;
        }

        $start = $this->start_year ?? 0;
        $end = $this->end_year ?? PHP_INT_MAX;

        return $year >= $start && $year <= $end;
    }

    /**
     * Find the component set that applies for a given subject and year.
     * Falls back to the default set if no specific range matches.
     */
    public static function findForSubjectYear(string $subjectId, int $year): ?self
    {
        // First try to find a specific year-range set (non-default), prefer latest
        $set = static::where('subject_id', $subjectId)
            ->where('is_default', false)
            ->where('start_year', '<=', $year)
            ->where(function ($q) use ($year) {
                $q->where('end_year', '>=', $year)->orWhereNull('end_year');
            })
            ->orderByDesc('end_year')
            ->first();

        // Fall back to the default set (regardless of its stored year range)
        if (!$set) {
            $set = static::where('subject_id', $subjectId)
                ->where('is_default', true)
                ->first();
        }

        return $set;
    }

    /**
     * Get the latest (most recent end_year) non-default set for a subject.
     * Falls back to the default set.
     */
    public static function findLatestForSubject(string $subjectId): ?self
    {
        $set = static::where('subject_id', $subjectId)
            ->where('is_default', false)
            ->orderByDesc('end_year')
            ->orderByDesc('start_year')
            ->first();

        return $set ?? static::where('subject_id', $subjectId)
            ->where('is_default', true)
            ->first();
    }
}
