<?php
// Spot subjects where PUM is missing (0) or partially missing.
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\SubjectResult;

echo "=== SUBJECTS WITH MISSING PUM VALUES ===" . PHP_EOL;

$subjects = Subject::with('qualification')->get();

foreach ($subjects as $subject) {
    $totalResults = SubjectResult::where('subject_id', $subject->id)->count();
    if ($totalResults === 0) {
        continue;
    }

    $zeroPumCount = SubjectResult::where('subject_id', $subject->id)->where('pum', 0)->count();
    $nonZeroPumCount = $totalResults - $zeroPumCount;

    if ($zeroPumCount > 0) {
        $percentageMissing = round(($zeroPumCount / $totalResults) * 100, 1);
        echo sprintf(
            "[%s] %s (%s): %d/%d results missing PUM (%d%% missing)" . PHP_EOL,
            $subject->qualification->qualification_type,
            $subject->subject_code,
            $subject->subject_name,
            $zeroPumCount,
            $totalResults,
            $percentageMissing
        );
    }
}
