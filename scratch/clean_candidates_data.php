<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Starting cleanup of candidate data..." . PHP_EOL;

// We use DB::table()->delete() to delete all records.
// Delete from child tables first to avoid foreign key constraints (if any exist at DB level).

$deletedComponentMarks = DB::table('component_marks')->delete();
echo "Deleted {$deletedComponentMarks} records from component_marks." . PHP_EOL;

$deletedSubjectResults = DB::table('subject_results')->delete();
echo "Deleted {$deletedSubjectResults} records from subject_results." . PHP_EOL;

$deletedCandidateEnrollments = DB::table('candidate_enrollments')->delete();
echo "Deleted {$deletedCandidateEnrollments} records from candidate_enrollments." . PHP_EOL;

$deletedCandidates = DB::table('candidates')->delete();
echo "Deleted {$deletedCandidates} records from candidates." . PHP_EOL;

// Also delete the upload logs since the data they generated is now gone
$deletedUploadLogs = DB::table('upload_logs')->delete();
echo "Deleted {$deletedUploadLogs} records from upload_logs." . PHP_EOL;

echo "Database successfully cleaned of all candidate data." . PHP_EOL;
