<?php
// Spot years and series where PUM is missing (0).
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;
use App\Models\ExamSeries;

echo "=== MISSING PUMS BY EXAM SERIES ===" . PHP_EOL;

$seriesList = ExamSeries::all();

foreach ($seriesList as $series) {
    $total = SubjectResult::where('series_id', $series->id)->count();
    if ($total === 0) {
        continue;
    }
    
    $zeroPum = SubjectResult::where('series_id', $series->id)->where('pum', 0)->count();
    $pct = round(($zeroPum / $total) * 100, 1);
    
    echo "Series: {$series->series_name} ({$series->series_code}) -> {$zeroPum}/{$total} missing PUMs ({$pct}%)" . PHP_EOL;
}
