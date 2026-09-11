<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Get uploaded_by from an existing result
$sample = DB::table('subject_results')->whereNotNull('uploaded_by')->first(['uploaded_by']);
$uploadedBy = $sample ? $sample->uploaded_by : 'system';
echo "Using uploaded_by: $uploadedBy\n";

$enrollmentId = '500bb5dd-91b0-4aaa-8f26-073d84e27d3f';
$subjectId    = '01ksffsqrqb9tnm4tajthhn0yj';
$seriesId     = '019ff3e5-a875-718b-be8b-6aa62b75fe76';

$existing = DB::table('subject_results')->where('enrollment_id', $enrollmentId)->first();
if ($existing) {
    echo "Result already exists.\n";
    exit;
}

$resultId = \Illuminate\Support\Str::uuid()->toString();
DB::table('subject_results')->insert([
    'id'                      => $resultId,
    'enrollment_id'           => $enrollmentId,
    'subject_id'              => $subjectId,
    'series_id'               => $seriesId,
    'grade'                   => 'B',
    'pum'                     => 75,
    'total_obtained_marks'    => null,
    'total_marks'             => null,
    'overall_percentage'      => null,
    'calculated_uniform_mark' => null,
    'is_passed'               => 1,
    'remarks'                 => 'Manually added - grade from June 2026 marksheet',
    'status'                  => 'complete',
    'result_uploaded_at'      => now(),
    'components_uploaded_at'  => now(),
    'uploaded_by'             => $uploadedBy,
    'created_at'              => now(),
    'updated_at'              => now(),
]);
echo "Created result: $resultId (grade B, pum 75)\n";

echo "\nFinal verification - all subjects for CHRISANNE NADINE D'SILVA (4013):\n";
$verify = DB::table('candidate_enrollments as ce')
    ->join('subjects', 'ce.subject_id', '=', 'subjects.id')
    ->join('exam_series', 'ce.series_id', '=', 'exam_series.id')
    ->leftJoin('subject_results as sr', 'sr.enrollment_id', '=', 'ce.id')
    ->where('ce.candidate_id', '019ff3e6-0f43-730b-a1d1-a92ddf5a091f')
    ->get(['subjects.subject_code','subjects.subject_name','exam_series.series_name','sr.grade','sr.status']);

foreach ($verify as $v) {
    echo "  [{$v->series_name}] {$v->subject_code} - {$v->subject_name} | Grade: {$v->grade} | Status: {$v->status}\n";
}
