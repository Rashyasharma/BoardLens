<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;
use Illuminate\Support\Facades\DB;

// Update all subject results
$results = SubjectResult::all();
$updated = 0;
foreach ($results as $result) {
    $grade = strtoupper(trim($result->grade));
    // Graded means it has a grade and is not U, X, Q, PENDING
    $isPassed = !empty($grade) && !in_array($grade, ['U', 'UU', 'X', 'Q', 'PENDING']);
    
    if ($result->is_passed !== $isPassed) {
        $result->is_passed = $isPassed;
        $result->save();
        $updated++;
    }
}

echo "Updated $updated results with new is_passed logic based on grade instead of PUM.\n";

$total = SubjectResult::count();
$passed = SubjectResult::where('is_passed', true)->count();
echo "New overall pass rate: " . ($total > 0 ? round(($passed/$total)*100, 2) : 0) . "%\n";
