<?php
// Find all candidates matching "Riya Bhandari" or similar, and display their results
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use App\Models\SubjectResult;

$cands = Candidate::where('candidate_name', 'like', '%Riya%')
    ->orWhere('candidate_name', 'like', '%Bhandar%')
    ->get();

echo "Found " . $cands->count() . " candidates matching Riya/Bhandari:" . PHP_EOL;
foreach ($cands as $c) {
    echo "ID: {$c->id} | Name: {$c->candidate_name} | Number: {$c->candidate_number}" . PHP_EOL;
    
    // Find all results across all series
    $results = SubjectResult::whereHas('enrollment', function($q) use ($c) {
        $q->where('candidate_id', $c->id);
    })->with(['subject', 'series'])->get();
    
    foreach ($results as $r) {
        echo "  - Series: {$r->series->series_name} | Subject: {$r->subject->subject_code} ({$r->subject->subject_name}) | Grade: {$r->grade} | PUM: {$r->pum}" . PHP_EOL;
    }
}
