<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;

$unmappedResults = SubjectResult::where(function($q) {
        $q->whereNull('pum')->orWhere('pum', 0);
    })
    ->whereNotIn('grade', ['U', 'UU', 'Q', 'X'])
    ->with(['enrollment.candidate', 'series', 'subject'])
    ->get();

echo "Found " . $unmappedResults->count() . " results with missing or 0 PUM:\n\n";

if ($unmappedResults->isNotEmpty()) {
    echo str_pad("Candidate Name", 25) . " | " . 
         str_pad("Cand No", 10) . " | " . 
         str_pad("Series", 12) . " | " . 
         str_pad("Subject", 25) . " | " . 
         str_pad("Grade", 6) . " | " . 
         "PUM\n";
    echo str_repeat("-", 90) . "\n";
    
    foreach ($unmappedResults as $res) {
        $candidateName = $res->enrollment->candidate->candidate_name ?? 'Unknown';
        $candNumber = $res->enrollment->candidate->candidate_number ?? 'N/A';
        $seriesName = $res->series->series_name ?? 'N/A';
        $subjectName = $res->subject->subject_name ?? 'N/A';
        $grade = $res->grade ?? 'N/A';
        $pum = $res->pum ?? 'NULL';
        
        echo str_pad(substr($candidateName, 0, 25), 25) . " | " . 
             str_pad($candNumber, 10) . " | " . 
             str_pad($seriesName, 12) . " | " . 
             str_pad(substr($subjectName, 0, 25), 25) . " | " . 
             str_pad($grade, 6) . " | " . 
             $pum . "\n";
    }
} else {
    echo "All results have successfully mapped PUM values!\n";
}
