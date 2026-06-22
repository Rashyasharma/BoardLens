<?php
// Find Riya Bhandari results in NOV-2025 where PUM is missing and print db records
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use App\Models\SubjectResult;
use App\Models\ExamSeries;

$series = ExamSeries::where('series_code', 'NOV-2025')->first();
$cand = Candidate::where('candidate_name', 'RIYA BHANDARI')
    ->where('candidate_number', '0027')
    ->first();

if ($cand && $series) {
    echo "Found Candidate: ID: {$cand->id}, Name: {$cand->candidate_name}, Number: {$cand->candidate_number}" . PHP_EOL;
    
    // Let's directly search for results in subject_results table associated with this candidate's enrollments
    $results = SubjectResult::where('series_id', $series->id)
        ->whereHas('enrollment', function($q) use ($cand) {
            $q->where('candidate_id', $cand->id);
        })
        ->with('subject')
        ->get();
        
    echo "Results count: " . $results->count() . PHP_EOL;
    foreach ($results as $r) {
        echo "  - Subject: {$r->subject->subject_code} ({$r->subject->subject_name}) | Grade: {$r->grade} | PUM: {$r->pum}" . PHP_EOL;
    }
} else {
    echo "Candidate or Series not found!" . PHP_EOL;
}
