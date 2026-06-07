<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use Illuminate\Support\Facades\DB;

// Query candidates by candidate_number or normalized name duplicates
$candidates = Candidate::all();

echo "Total Candidates: " . $candidates->count() . "\n\n";

$byNumber = $candidates->groupBy('candidate_number');
echo "Checking Duplicate Candidate Numbers:\n";
echo "-------------------------------------\n";
foreach ($byNumber as $num => $group) {
    if ($group->count() > 1) {
        echo "Candidate Number [{$num}] has " . $group->count() . " records:\n";
        foreach ($group as $cand) {
            echo "  ID: {$cand->id} | Name: '{$cand->candidate_name}' | DOB: " . ($cand->date_of_birth ? $cand->date_of_birth->format('Y-m-d') : 'N/A') . "\n";
        }
        echo "\n";
    }
}

echo "\nChecking Duplicate Names (Normalized - lowercased & spaces cleaned):\n";
echo "------------------------------------------------------------------\n";
$byNormalizedName = $candidates->groupBy(function($c) {
    return strtolower(preg_replace('/\s+/', ' ', trim($c->candidate_name)));
});

foreach ($byNormalizedName as $name => $group) {
    if ($group->count() > 1) {
        // If they have different IDs
        echo "Normalized Name ['{$name}'] has " . $group->count() . " records:\n";
        foreach ($group as $cand) {
            echo "  ID: {$cand->id} | Name: '{$cand->candidate_name}' | Number: {$cand->candidate_number} | DOB: " . ($cand->date_of_birth ? $cand->date_of_birth->format('Y-m-d') : 'N/A') . "\n";
        }
        echo "\n";
    }
}
