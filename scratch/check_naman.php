<?php
require __DIR__.'/../vendor/autoload.php'; 
$app = require_once __DIR__.'/../bootstrap/app.php'; 
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class); 
$kernel->bootstrap(); 

$candidates = \App\Models\Candidate::where('candidate_name', 'like', '%NAMAN%KACHHWAHA%')->pluck('id'); 
$seriesCount = \App\Models\CandidateEnrollment::whereIn('candidate_id', $candidates)->distinct('series_id')->count('series_id'); 
$series = \App\Models\CandidateEnrollment::whereIn('candidate_id', $candidates)->with('series')->get()->pluck('series')->unique('id')->filter(); 

echo "Naman Kachhwaha appeared in the following series:\n";
foreach($series as $s) { 
    echo "- " . $s->month . ' ' . $s->year . "\n"; 
} 
echo "\nTotal distinct series: " . $seriesCount . "\n";
