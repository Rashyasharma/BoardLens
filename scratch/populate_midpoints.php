<?php
// Script to fix midpoint PUM mapping according to the provided image chart
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;
use App\Services\AiSpreadsheetParser;

// Check existing database records where pum = 0 and grade is legitimate
$results = SubjectResult::where('pum', 0.0)
    ->whereNotIn('grade', ['U', 'X', 'Q', 'ENTRY', 'PENDING'])
    ->with(['subject.qualification'])
    ->get();

echo "Found " . $results->count() . " results with legitimate grades but 0 PUM." . PHP_EOL;

$updated = 0;
foreach ($results as $r) {
    $qual = $r->subject->qualification->qualification_type ?? 'IGCSE';
    $grade = $r->grade;
    
    $pum = getCorrectMidpointPum($grade, $qual);
    if ($pum > 0) {
        $r->pum = $pum;
        $r->save();
        $updated++;
    }
}

echo "Successfully updated {$updated} results with midpoint PUM fallbacks!" . PHP_EOL;

function getCorrectMidpointPum(string $grade, string $qualification): float
{
    $g = trim($grade);
    
    // AS Level (lower case letters)
    if ($qualification === 'AS_A_LEVEL' && in_array($g, ['a', 'b', 'c', 'd', 'e'])) {
        $asMap = [
            'a' => 90.0, // Midpoint of 80-100 range
            'b' => 74.5, // Midpoint of 70-79 range (74.5 or 75)
            'c' => 64.5, // Midpoint of 60-69 range
            'd' => 54.5, // Midpoint of 50-59 range
            'e' => 44.5  // Midpoint of 40-49 range
        ];
        return $asMap[$g] ?? 0.0;
    }
    
    // Let's also check if AS level grade is uppercase but qualification is AS_A_LEVEL
    // Note: A Level uses uppercase A*, A, B, C, D, E.
    // IGCSE uses A*, A, B, C, D, E, F, G.
    $gUpper = strtoupper($g);
    
    switch ($gUpper) {
        case 'A*':
        case 'A*A*':
            return 95.0; // Midpoint of 90-100
        case 'A':
        case 'AA':
            return 84.5; // Midpoint of 80-89
        case 'B':
        case 'BB':
            return 74.5; // Midpoint of 70-79
        case 'C':
        case 'CC':
            return 64.5; // Midpoint of 60-69
        case 'D':
        case 'DD':
            return 54.5; // Midpoint of 50-59
        case 'E':
        case 'EE':
            return 44.5; // Midpoint of 40-49
        case 'F':
        case 'FF':
            return 34.5; // Midpoint of 30-39
        case 'G':
        case 'GG':
            return 24.5; // Midpoint of 20-29
        default:
            return 0.0;
    }
}
