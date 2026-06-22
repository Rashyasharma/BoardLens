<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$seriesId = '019e5ed2-be1b-7098-8157-5242cabfec27';
$subjectId = '019e5ed2-bbf3-71c9-82ab-224723e37b0c';

$subjectResultsCount = App\Models\SubjectResult::where('series_id', $seriesId)->where('subject_id', $subjectId)->count();
echo "SubjectResults count: " . $subjectResultsCount . "\n";

$enrollmentsCount = App\Models\CandidateEnrollment::where('series_id', $seriesId)->where('subject_id', $subjectId)->count();
echo "Enrollments count: " . $enrollmentsCount . "\n";

$firstResult = App\Models\SubjectResult::where('series_id', $seriesId)->where('subject_id', $subjectId)->first();
if ($firstResult) {
    echo "First result enrollment_id: " . $firstResult->enrollment_id . "\n";
    $enr = App\Models\CandidateEnrollment::find($firstResult->enrollment_id);
    if ($enr) {
        echo "Match: yes, enrollment exists.\n";
    } else {
        echo "Match: NO! Enrollment doesn't exist.\n";
    }
}
