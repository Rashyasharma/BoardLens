<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c1 = \App\Models\Candidate::find('01a08f13-aa1f-7344-bf42-6b1eabd69347');
$c2 = \App\Models\Candidate::find('01a08f15-ee99-73e6-9e97-9ef8f04e6c73');

echo "C1: " . $c1->candidate_name . PHP_EOL;
foreach ($c1->enrollments as $enr) {
    echo "  - Series: " . $enr->series->month . ' ' . $enr->series->year . PHP_EOL;
}
echo PHP_EOL;
echo "C2: " . $c2->candidate_name . PHP_EOL;
foreach ($c2->enrollments as $enr) {
    echo "  - Series: " . $enr->series->month . ' ' . $enr->series->year . PHP_EOL;
}
