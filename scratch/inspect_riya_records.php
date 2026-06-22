<?php
// Find Riya Bhandari results in database
require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use App\Models\SubjectResult;
use App\Models\ExamSeries;

$cands = Candidate::where('candidate_name', 'RIYA BHANDARI')->get();
echo "Total Riya Bhandari in DB: " . $cands->count() . PHP_EOL;

foreach ($cands as $c) {
    echo "ID: {$c->id} | Name: {$c->candidate_name} | Number: {$c->candidate_number}" . PHP_EOL;
    
    $results = SubjectResult::whereHas('enrollment', function($q) use ($c) {
        $q->where('candidate_id', $c->id);
    })->with(['subject', 'series'])->get();
    
    foreach ($results as $r) {
        echo "  - Series: {$r->series->series_name} ({$r->series->series_code}) | Subject: {$r->subject->subject_code} | Grade: {$r->grade} | PUM: {$r->pum}" . PHP_EOL;
    }
}
