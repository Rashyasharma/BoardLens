<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SUBJECTS ===\n";
$subjects = App\Models\Subject::orderBy('subject_code')->get(['subject_code', 'subject_name']);
foreach ($subjects as $s) {
    echo $s->subject_code . ' | ' . $s->subject_name . "\n";
}

echo "\n=== EXAM SERIES ===\n";
$series = App\Models\ExamSeries::orderBy('year')->orderBy('month')->get(['id', 'year', 'month', 'series_code']);
foreach ($series as $s) {
    echo $s->id . ' | ' . $s->year . ' ' . $s->month . ' | ' . $s->series_code . "\n";
}
