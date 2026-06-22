<?php
// Identify which exam series and qualifications have missing PUM values,
// ordered chronologically by exam series.
require_once __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;
use App\Models\ExamSeries;
use App\Models\Qualification;

echo "=== SERIES & QUALIFICATIONS WITH MISSING PUM VALUES ===" . PHP_EOL;

// Fetch exam series ordered chronologically
$seriesList = ExamSeries::orderBy('year')->orderBy(Illuminate\Support\Facades\DB::raw("
    CASE 
        WHEN month = 'March' THEN 1
        WHEN month = 'June' THEN 2
        WHEN month = 'November' THEN 3
        ELSE 4
    END
"))->get();

$qualifications = Qualification::all();

foreach ($seriesList as $series) {
    foreach ($qualifications as $qual) {
        $totalResults = SubjectResult::where('series_id', $series->id)
            ->whereHas('subject', function($q) use ($qual) {
                $q->where('qualification_id', $qual->id);
            })->count();
            
        if ($totalResults === 0) {
            continue;
        }

        $zeroPum = SubjectResult::where('series_id', $series->id)
            ->where('pum', 0.0)
            ->whereNotIn('grade', ['U', 'X', 'Q', 'ENTRY', 'PENDING']) // Exclude ungraded or non-legitimate grades
            ->whereHas('subject', function($q) use ($qual) {
                $q->where('qualification_id', $qual->id);
            })->count();
            
        if ($zeroPum > 0) {
            $pct = round(($zeroPum / $totalResults) * 100, 1);
            echo sprintf(
                "Series: %s %s | Qualification: %s -> %d/%d results missing PUM (%d%% missing)" . PHP_EOL,
                $series->month,
                $series->year,
                $qual->qualification_type,
                $zeroPum,
                $totalResults,
                $pct
            );
        }
    }
}
