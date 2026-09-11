<?php
/**
 * SYSTEM RESET SCRIPT
 * Clears: Candidates, Schools, Enrollments, SubjectResults, ComponentMarks, UploadLogs
 * Keeps:  ExamSeries, Subjects, Qualifications, Components, ComponentSets, GradeThresholds, Users
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Show counts before deletion
echo "=== BEFORE RESET ===\n";
echo "ComponentMarks:      " . DB::table('component_marks')->count() . "\n";
echo "SubjectResults:      " . DB::table('subject_results')->count() . "\n";
echo "CandidateEnrollments:" . DB::table('candidate_enrollments')->count() . "\n";
echo "Candidates:          " . DB::table('candidates')->count() . "\n";
echo "Schools:             " . DB::table('schools')->count() . "\n";
echo "UploadLogs:          " . DB::table('upload_logs')->count() . "\n";

echo "\n--- Deleting data (order: marks → results → enrollments → candidates → schools → logs) ---\n";

// Delete in correct FK order
$deleted = DB::table('component_marks')->delete();
echo "Deleted $deleted component_marks\n";

$deleted = DB::table('subject_results')->delete();
echo "Deleted $deleted subject_results\n";

$deleted = DB::table('candidate_enrollments')->delete();
echo "Deleted $deleted candidate_enrollments\n";

$deleted = DB::table('candidates')->delete();
echo "Deleted $deleted candidates\n";

$deleted = DB::table('schools')->delete();
echo "Deleted $deleted schools\n";

$deleted = DB::table('upload_logs')->delete();
echo "Deleted $deleted upload_logs\n";

echo "\n=== AFTER RESET ===\n";
echo "ComponentMarks:      " . DB::table('component_marks')->count() . "\n";
echo "SubjectResults:      " . DB::table('subject_results')->count() . "\n";
echo "CandidateEnrollments:" . DB::table('candidate_enrollments')->count() . "\n";
echo "Candidates:          " . DB::table('candidates')->count() . "\n";
echo "Schools:             " . DB::table('schools')->count() . "\n";
echo "UploadLogs:          " . DB::table('upload_logs')->count() . "\n";

echo "\n=== PRESERVED ===\n";
echo "ExamSeries:          " . DB::table('exam_series')->count() . "\n";
echo "Subjects:            " . DB::table('subjects')->count() . "\n";
echo "Qualifications:      " . DB::table('qualifications')->count() . "\n";
echo "Components:          " . DB::table('components')->count() . "\n";
echo "ComponentSets:       " . DB::table('component_sets')->count() . "\n";
echo "GradeThresholds:     " . DB::table('grade_thresholds')->count() . "\n";
echo "Users:               " . DB::table('users')->count() . "\n";

echo "\n✅ System reset complete. All candidate data has been cleared.\n";
