<?php

namespace App\Models\Cbse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbseResult extends Model
{
    use HasUuids;

    protected $table = 'cbse_results';

    protected $fillable = [
        'student_id',
        'qualification_id',
        'subject_id',
        'academic_year_id',
        'exam_year',
        'roll_number',
        'theory_obtained',
        'practical_obtained',
        'total_obtained',
        'total_marks',
        'percentage',
        'grade',
        'is_passed',
        'is_absent',
        'is_compartment',
        'remarks',
    ];

    protected $casts = [
        'theory_obtained'    => 'decimal:2',
        'practical_obtained' => 'decimal:2',
        'total_obtained'     => 'decimal:2',
        'total_marks'        => 'integer',
        'percentage'         => 'decimal:2',
        'is_passed'          => 'boolean',
        'is_absent'          => 'boolean',
        'is_compartment'     => 'boolean',
    ];

    // Standard CBSE grade thresholds
    public static array $gradeThresholds = [
        'A1' => 91,
        'A2' => 81,
        'B1' => 71,
        'B2' => 61,
        'C1' => 51,
        'C2' => 41,
        'D'  => 33,
    ];

    public static function computeGrade(float $percentage): string
    {
        if ($percentage >= 91) return 'A1';
        if ($percentage >= 81) return 'A2';
        if ($percentage >= 71) return 'B1';
        if ($percentage >= 61) return 'B2';
        if ($percentage >= 51) return 'C1';
        if ($percentage >= 41) return 'C2';
        if ($percentage >= 33) return 'D';
        return 'E1'; // Failed
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(CbseStudent::class, 'student_id');
    }

    public function qualification(): BelongsTo
    {
        return $this->belongsTo(CbseQualification::class, 'qualification_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(CbseSubject::class, 'subject_id');
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(CbseAcademicYear::class, 'academic_year_id');
    }

    public function getGradeBadgeColorAttribute(): string
    {
        return match ($this->grade) {
            'A1' => 'emerald',
            'A2' => 'green',
            'B1' => 'teal',
            'B2' => 'cyan',
            'C1' => 'blue',
            'C2' => 'indigo',
            'D'  => 'amber',
            'E1', 'E2' => 'rose',
            default => 'slate',
        };
    }
}
