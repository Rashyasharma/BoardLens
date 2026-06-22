<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$result = App\Models\SubjectResult::first();
echo "SubjectResult enrollment_id: " . $result->enrollment_id . "\n";
$enrollment = App\Models\CandidateEnrollment::find($result->enrollment_id);
echo "Enrollment subject_id: " . ($enrollment ? $enrollment->subject_id : 'NULL') . "\n";

// Let's also check if there are any enrollments where subject_id is NULL
$generalEnrollmentCount = App\Models\CandidateEnrollment::whereNull('subject_id')->count();
echo "General Enrollments: " . $generalEnrollmentCount . "\n";
