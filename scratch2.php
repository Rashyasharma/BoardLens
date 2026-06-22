<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cbse\CbseResult;
use App\Models\Cbse\CbseAcademicYear;
use App\Models\Cbse\CbseQualification;

$year = CbseAcademicYear::where('name', '2025-2026')->first();
$qual = CbseQualification::where('qualification_name', 'Senior Secondary (Class 12)')->first();

$subjectIds = CbseResult::where('academic_year_id', $year->id)
    ->where('qualification_id', $qual->id)
    ->distinct()
    ->pluck('subject_id')->toArray();

$academicYears = CbseAcademicYear::orderByDesc('name')->get();
$yearIndex = $academicYears->search(fn($y) => $y->id == $year->id);

$last1YearIds = $academicYears->slice($yearIndex + 1, 1)->pluck('id')->toArray();

$stats = CbseResult::whereIn('academic_year_id', $last1YearIds)
    ->where('qualification_id', $qual->id)
    ->whereIn('subject_id', $subjectIds)
    ->selectRaw('subject_id, AVG(total_obtained) as avg_marks')
    ->groupBy('subject_id')
    ->get();

print_r($stats->toArray());
