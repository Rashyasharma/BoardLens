<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$candidates = \App\Models\Candidate::where('candidate_name', 'LIKE', '%Yogita%')->get();
foreach ($candidates as $candidate) {
    echo $candidate->id . ' - ' . $candidate->candidate_name . PHP_EOL;
}
