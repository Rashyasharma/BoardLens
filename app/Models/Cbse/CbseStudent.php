<?php

namespace App\Models\Cbse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CbseStudent extends Model
{
    use HasUuids;

    protected $table = 'cbse_students';

    protected $fillable = [
        'admission_number',
        'student_name',
        'father_name',
        'mother_name',
        'date_of_birth',
        'gender',
        'qualification_type',
        'admission_year',
        'status',
    ];

    protected $casts = [
        'date_of_birth'  => 'date',
        'admission_year' => 'integer',
    ];

    public function results(): HasMany
    {
        return $this->hasMany(CbseResult::class, 'student_id');
    }

    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            'M' => 'Male',
            'F' => 'Female',
            'O' => 'Other',
            default => '—',
        };
    }

    public function getQualificationLabelAttribute(): string
    {
        return match ($this->qualification_type) {
            'CLASS_10' => 'Class 10',
            'CLASS_12' => 'Class 12',
            default    => $this->qualification_type ?? '—',
        };
    }
}
