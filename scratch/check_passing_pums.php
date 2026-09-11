<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;

$passingZeroPum = SubjectResult::where('pum', 0.0)
    ->whereNotIn(DB::raw('upper(grade)'), ['U', 'UU', 'X', 'Q', 'PENDING', 'ENTRY'])
    ->get();

echo "Zero PUM count for passing grades: " . $passingZeroPum->count() . "\n";

if ($passingZeroPum->count() > 0) {
    foreach ($passingZeroPum->take(20) as $r) {
        $candidate = $r->enrollment?->candidate?->candidate_name ?? 'Unknown';
        $subject = $r->subject?->subject_code ?? 'Unknown';
        $series = $r->series?->series_code ?? 'Unknown';
        echo "Candidate: {$candidate}, Subject: {$subject}, Series: {$series}, Grade: {$r->grade}, PUM: {$r->pum}\n";
    }
} else {
    echo "All passing grades have a valid PUM calculated/imported!\n";
}
