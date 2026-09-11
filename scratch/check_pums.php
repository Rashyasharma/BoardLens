<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;
use Illuminate\Support\Facades\DB;

$total = SubjectResult::count();
$zeroPum = SubjectResult::where('pum', 0.0)->get();
$nullPum = SubjectResult::whereNull('pum')->get();

echo "Total Results: {$total}\n";
echo "Zero PUM count: " . $zeroPum->count() . "\n";
echo "Null PUM count: " . $nullPum->count() . "\n";

if ($zeroPum->count() > 0) {
    echo "\nSample of Zero PUM records:\n";
    foreach ($zeroPum->take(20) as $r) {
        $candidate = $r->enrollment?->candidate?->candidate_name ?? 'Unknown';
        $subject = $r->subject?->subject_code ?? 'Unknown';
        $series = $r->series?->series_code ?? 'Unknown';
        echo "Candidate: {$candidate}, Subject: {$subject}, Series: {$series}, Grade: {$r->grade}, PUM: {$r->pum}\n";
    }
}
