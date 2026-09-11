<?php
use App\Models\SubjectResult;
use App\Models\Subject;
use App\Models\Qualification;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$currentYear = (int) now()->format('Y');
$startYear = $currentYear - 2;

// GCE AS and A Level subjects
// Get qualifications that are AS_A_LEVEL
$gceQualificationIds = Qualification::whereIn('qualification_type', ['AS_A_LEVEL', 'AS_LEVEL', 'A_LEVEL'])->pluck('id');

$gceSubjectResults = SubjectResult::selectRaw('subject_id, COUNT(*) as total_results, SUM(CASE WHEN is_passed = 1 THEN 1 ELSE 0 END) as passed_results')
    ->whereHas('series', function($q) use ($startYear) {
        $q->where('year', '>=', $startYear);
    })
    ->whereHas('subject', function($q) use ($gceQualificationIds) {
        $q->whereIn('qualification_id', $gceQualificationIds);
    })
    ->groupBy('subject_id')
    ->havingRaw('COUNT(*) > 5') // to avoid 1 student = 100% pass rate
    ->get()
    ->map(function ($result) {
        $result->pass_rate = ($result->passed_results / $result->total_results) * 100;
        return $result;
    });

$bestGce = $gceSubjectResults->sortByDesc('pass_rate')->take(3);
$worstGce = $gceSubjectResults->sortBy('pass_rate')->take(3);

echo "Best GCE:\n";
foreach($bestGce as $res) echo Subject::find($res->subject_id)->subject_name . " - " . $res->pass_rate . "%\n";
echo "Worst GCE:\n";
foreach($worstGce as $res) echo Subject::find($res->subject_id)->subject_name . " - " . $res->pass_rate . "%\n";
