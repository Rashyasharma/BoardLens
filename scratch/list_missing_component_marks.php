<?php

use App\Models\ExamSeries;
use App\Models\Qualification;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;

// Bootstrap Laravel if executed directly (e.g. via artisan tinker or require)
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n============================================================\n";
echo "EXAM SERIES & QUALIFICATIONS MISSING COMPONENT MARKS UPLOADS\n";
echo "============================================================\n\n";

$series = ExamSeries::orderBy('year', 'desc')->get();
$qualifications = Qualification::all();

$foundAny = false;

foreach ($series as $s) {
    foreach ($qualifications as $q) {
        // Find if we have candidate enrollments in this series & qualification
        $hasEnrollments = CandidateEnrollment::where('series_id', $s->id)
            ->where('qualification_id', $q->id)
            ->exists();
            
        if (!$hasEnrollments) {
            // No student registrations/enrollments exist, so it's not active for this series
            continue;
        }

        // Check if there are subject results matching this series and qualification
        $results = SubjectResult::where('series_id', $s->id)
            ->whereHas('subject', function($query) use ($q) {
                $query->where('qualification_id', $q->id);
            })
            ->get();

        if ($results->isEmpty()) {
            echo "❌ Series: {$s->series_name} | Qualification: {$q->type_display} - (NO RESULTS UPLOADED AT ALL)\n";
            $foundAny = true;
            continue;
        }

        // For the existing results, check if ANY are missing component marks
        $resultsCount = $results->count();
        $withComponentMarks = 0;
        foreach ($results as $res) {
            if ($res->componentMarks()->exists()) {
                $withComponentMarks++;
            }
        }

        if ($withComponentMarks === 0) {
            echo "❌ Series: {$s->series_name} | Qualification: {$q->type_display} - (0 / {$resultsCount} subject results have component marks)\n";
            $foundAny = true;
        } elseif ($withComponentMarks < $resultsCount) {
            $missing = $resultsCount - $withComponentMarks;
            echo "⚠️  Series: {$s->series_name} | Qualification: {$q->type_display} - (PARTIALLY MISSING: {$missing} / {$resultsCount} results are missing component marks)\n";
            $foundAny = true;
        }
    }
}

if (!$foundAny) {
    echo "✅ All active series and qualification combinations have completed component marks uploads!\n";
}

echo "\n";
