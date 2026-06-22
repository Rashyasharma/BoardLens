<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Subject;
use App\Models\SubjectResult;

foreach (Subject::all() as $subject) {
    $allResults = $subject->results;
    $nonZeroResults = $subject->results()->where('pum', '>', 0)->get();
    echo "Subject: {$subject->subject_code} - {$subject->subject_name}\n";
    echo "  Total results: " . $allResults->count() . "\n";
    echo "  Avg PUM (all): " . $allResults->avg('pum') . "\n";
    echo "  Avg PUM (non-zero): " . $nonZeroResults->avg('pum') . "\n";
    echo "  Grades distribution: " . json_encode($allResults->groupBy('grade')->map->count()) . "\n";
}
