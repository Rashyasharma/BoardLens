<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$ids = [
    '5027' => '019ef868-68d0-72c4-94f2-f05e2c1f7167',
    '4013' => '019ff3e6-0f43-730b-a1d1-a92ddf5a091f',
];

foreach ($ids as $num => $id) {
    echo "\n=== Enrollments for candidate $num (id: $id) ===\n";
    $enr = DB::table('candidate_enrollments as ce')
        ->leftJoin('subjects', 'ce.subject_id', '=', 'subjects.id')
        ->leftJoin('qualifications', 'ce.qualification_id', '=', 'qualifications.id')
        ->leftJoin('exam_series', 'ce.series_id', '=', 'exam_series.id')
        ->where('ce.candidate_id', $id)
        ->get([
            'subjects.subject_name',
            'subjects.subject_code',
            'qualifications.qualification_name',
            'exam_series.series_name',
            'ce.enrollment_status',
            'ce.enrolled_date',
        ]);

    if ($enr->isEmpty()) {
        echo "  No enrollments.\n";
    }
    foreach ($enr as $e) {
        echo "  [{$e->series_name}] {$e->subject_code} - {$e->subject_name} | Qual: {$e->qualification_name} | Status: {$e->enrollment_status}\n";
    }

    echo "\n  Subject Results:\n";
    $results = DB::table('subject_results as sr')
        ->leftJoin('subjects', 'sr.subject_id', '=', 'subjects.id')
        ->leftJoin('exam_series', 'sr.series_id', '=', 'exam_series.id')
        ->leftJoin('candidate_enrollments as ce', 'sr.enrollment_id', '=', 'ce.id')
        ->where('ce.candidate_id', $id)
        ->get(['subjects.subject_name','subjects.subject_code','sr.grade','sr.pum','exam_series.series_name','sr.status']);
    foreach ($results as $r) {
        echo "  [{$r->series_name}] {$r->subject_code} - {$r->subject_name} | Grade: {$r->grade} | Status: {$r->status}\n";
    }
}

echo "\n=== ALL subjects (checking for Travel/Tourism) ===\n";
$subs = DB::table('subjects')->orderBy('subject_name')->get(['subject_code','subject_name']);
foreach ($subs as $s) echo "  {$s->subject_code} | {$s->subject_name}\n";
