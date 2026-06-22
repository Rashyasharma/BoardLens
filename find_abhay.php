<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use App\Models\Subject;
use App\Models\ComponentMarks;

$candidates = Candidate::where('candidate_name', 'like', '%ABHAYRAJ TAK%')->get();
$csSubjectIds = Subject::whereIn('subject_code', ['9608', '9618'])->pluck('id')->toArray();

foreach ($candidates as $target) {
    $enrollments = CandidateEnrollment::where('candidate_id', $target->id)
        ->whereIn('subject_id', $csSubjectIds)
        ->get();

    foreach ($enrollments as $enr) {
        echo "Enrollment ID: {$enr->id}\n";
        
        $marks = ComponentMarks::where('enrollment_id', $enr->id)->get();
        echo "  Component Marks count: " . $marks->count() . "\n";
        foreach ($marks as $m) {
            echo "    Comp ID: {$m->component_id}, Obt: {$m->obtained_marks}, Total: {$m->total_marks}\n";
        }
        
        $result = SubjectResult::where('enrollment_id', $enr->id)->first();
        if ($result) {
            dump($result->toArray());
        } else {
            echo "  No Result found in SubjectResult table.\n";
        }
    }
}
