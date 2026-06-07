<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Candidate extends Model
{
    use HasUuids;

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

    protected $fillable = [
        'candidate_number',
        'candidate_name',
        'school_id',
        'date_of_birth',
        'gender',
        'enrollment_date',
        'status'
    ];

    /**
     * Find an existing candidate by name or number within the school, or create a new one.
     */
    public static function findOrCreateByNameAndNumber(string $schoolId, string $number, string $name, array $extraData = [])
    {
        $candidate = self::where('school_id', $schoolId)
            ->where('candidate_name', 'like', $name)
            ->first();

        if (!$candidate) {
            $candidate = self::where('school_id', $schoolId)
                ->where('candidate_number', $number)
                ->first();
        }

        if ($candidate) {
            $updateData = [];
            if ($candidate->candidate_number !== $number) {
                $updateData['candidate_number'] = $number;
            }
            if ($candidate->candidate_name !== $name) {
                $updateData['candidate_name'] = $name;
            }
            if (!empty($updateData)) {
                $candidate->update($updateData);
            }
        } else {
            $candidate = self::create(array_merge([
                'school_id' => $schoolId,
                'candidate_number' => $number,
                'candidate_name' => $name,
                'enrollment_date' => now()->toDateString(),
                'status' => 'active'
            ], $extraData));
        }

        return $candidate;
    }

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CandidateEnrollment::class);
    }

    public function results(): HasManyThrough
    {
        return $this->hasManyThrough(
            SubjectResult::class,
            CandidateEnrollment::class,
            'candidate_id', // Foreign key on CandidateEnrollment table
            'enrollment_id', // Foreign key on SubjectResult table
            'id', // Local key on Candidate table
            'id'  // Local key on CandidateEnrollment table
        );
    }
}
