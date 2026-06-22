<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = App\Models\SubjectResult::where('subject_results.series_id', '019e5ed2-bdb6-7240-8f2f-a4af758f4fbf')
    ->where('subject_results.subject_id', '01ksffsqqn44k67s3fxpwq8edc')
    ->join('candidate_enrollments', 'subject_results.enrollment_id', '=', 'candidate_enrollments.id')
    ->join('candidates', 'candidate_enrollments.candidate_id', '=', 'candidates.id');

echo "Count: " . $q->count() . "\n";
