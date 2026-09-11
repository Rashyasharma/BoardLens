<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;
use App\Models\ExamSeries;
use Illuminate\Support\Facades\DB;

$seriesList = ExamSeries::orderBy('year')->orderByRaw("CASE WHEN month='March' THEN 1 WHEN month='June' THEN 2 ELSE 3 END")->get();

$markdown = "# Import Report: Series by Series\n\n";

foreach ($seriesList as $series) {
    $results = SubjectResult::with(['subject', 'enrollment.candidate'])
        ->where('series_id', $series->id)
        ->get();
        
    if ($results->isEmpty()) {
        continue;
    }
    
    $candidatesCount = $results->pluck('enrollment.candidate_id')->unique()->count();
    $subjectsList = $results->pluck('subject.subject_code')->unique()->implode(', ');
    
    $markdown .= "## {$series->month} {$series->year} ({$series->series_code})\n";
    $markdown .= "- **Candidates Imported:** {$candidatesCount}\n";
    $markdown .= "- **Subjects Processed:** {$subjectsList}\n";
    $markdown .= "- **Results Imported:** {$results->count()}\n";
    
    // Check for Midpoint
    $midpointsCount = 0;
    foreach ($results as $r) {
        $g = strtolower(trim($r->grade));
        // Simple heuristic: if PUM matches exact midpoint values
        if (in_array((float)$r->pum, [95, 84.5, 74.5, 64.5, 54.5, 44.5, 34.5, 24.5])) {
            $midpointsCount++;
        }
    }
    
    if ($midpointsCount > 0) {
        $markdown .= "- **Midpoint Strategy Applied:** Yes (approx. {$midpointsCount} results)\n";
    }
    
    // Grades distribution
    $gradeDist = $results->groupBy('grade')->map->count()->toArray();
    arsort($gradeDist);
    $gradesStr = [];
    foreach ($gradeDist as $grade => $count) {
        $gradesStr[] = "{$grade} ($count)";
    }
    $markdown .= "- **Grades Distribution:** " . implode(', ', $gradesStr) . "\n\n";
    
    // Check for Computer Science (9608 vs 9618)
    $csSubjects = $results->filter(function($r) {
        return in_array($r->subject->subject_code, ['9608', '9618']);
    });
    if ($csSubjects->count() > 0) {
        $csCodes = $csSubjects->pluck('subject.subject_code')->unique()->implode(', ');
        $markdown .= "> [!NOTE]\n> **Computer Science Code Verification:** Mapped to **{$csCodes}** for this year.\n\n";
    }
}

file_put_contents(__DIR__ . '/report.md', $markdown);
echo "Report generated at scratch/report.md\n";
