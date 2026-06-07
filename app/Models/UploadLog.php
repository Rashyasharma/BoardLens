<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UploadLog extends Model
{
    use HasUuids;

    protected $table = 'upload_logs';

    public $timestamps = false;

    protected $fillable = [
        'uploaded_by',
        'school_id',
        'series_id',
        'subject_id',
        'file_name',
        'file_path',
        'upload_type',
        'records_processed',
        'records_failed',
        'status',
        'error_details',
        'uploaded_at'
    ];

    protected $casts = [
        'records_processed' => 'integer',
        'records_failed' => 'integer',
        'error_details' => 'array',
        'uploaded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(ExamSeries::class, 'series_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function getCreatedAtAttribute()
    {
        return $this->uploaded_at;
    }

    public function getQualificationNameAttribute()
    {
        if ($this->upload_type === 'component_marks') {
            if ($this->subject_id) {
                return $this->subject?->qualification?->qualification_name ?? 'Unknown';
            }
            
            $qualName = \App\Models\ComponentMarks::where('component_marks.uploaded_by', $this->uploaded_by)
                ->whereBetween('component_marks.uploaded_at', [
                    $this->uploaded_at->copy()->subSeconds(5),
                    $this->uploaded_at->copy()->addSeconds(5)
                ])
                ->with('enrollment.qualification')
                ->get()
                ->map(fn($m) => $m->enrollment?->qualification?->qualification_name)
                ->filter()
                ->unique()
                ->implode(', ');
                
            return $qualName ?: 'Unknown';
        }
        
        $qualName = \App\Models\SubjectResult::where('subject_results.uploaded_by', $this->uploaded_by)
            ->where('subject_results.series_id', $this->series_id)
            ->whereBetween('subject_results.result_uploaded_at', [
                $this->uploaded_at->copy()->subSeconds(5),
                $this->uploaded_at->copy()->addSeconds(5)
            ])
            ->with('subject.qualification')
            ->get()
            ->map(fn($r) => $r->subject?->qualification?->qualification_name)
            ->filter()
            ->unique()
            ->implode(', ');
            
        return $qualName ?: 'Unknown';
    }

    public function getCandidatesUpdatedCountAttribute()
    {
        if ($this->upload_type === 'candidate_data') {
            return \App\Models\SubjectResult::where('subject_results.uploaded_by', $this->uploaded_by)
                ->where('subject_results.series_id', $this->series_id)
                ->whereBetween('subject_results.result_uploaded_at', [
                    $this->uploaded_at->copy()->subSeconds(5),
                    $this->uploaded_at->copy()->addSeconds(5)
                ])
                ->join('candidate_enrollments', 'subject_results.enrollment_id', '=', 'candidate_enrollments.id')
                ->distinct('candidate_enrollments.candidate_id')
                ->count('candidate_enrollments.candidate_id');
        }
        
        return \App\Models\ComponentMarks::where('component_marks.uploaded_by', $this->uploaded_by)
            ->whereBetween('component_marks.uploaded_at', [
                $this->uploaded_at->copy()->subSeconds(5),
                $this->uploaded_at->copy()->addSeconds(5)
            ])
            ->join('candidate_enrollments', 'component_marks.enrollment_id', '=', 'candidate_enrollments.id')
            ->distinct('candidate_enrollments.candidate_id')
            ->count('candidate_enrollments.candidate_id');
    }

    public function getSubjectsUpdatedCountAttribute()
    {
        if ($this->upload_type === 'candidate_data') {
            return \App\Models\SubjectResult::where('subject_results.uploaded_by', $this->uploaded_by)
                ->where('subject_results.series_id', $this->series_id)
                ->whereBetween('subject_results.result_uploaded_at', [
                    $this->uploaded_at->copy()->subSeconds(5),
                    $this->uploaded_at->copy()->addSeconds(5)
                ])
                ->distinct('subject_id')
                ->count('subject_id');
        }
        
        return \App\Models\ComponentMarks::where('component_marks.uploaded_by', $this->uploaded_by)
            ->whereBetween('component_marks.uploaded_at', [
                $this->uploaded_at->copy()->subSeconds(5),
                $this->uploaded_at->copy()->addSeconds(5)
            ])
            ->join('subject_results', 'component_marks.subject_result_id', '=', 'subject_results.id')
            ->distinct('subject_results.subject_id')
            ->count('subject_results.subject_id');
    }
}
