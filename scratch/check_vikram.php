<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;

$vikrams = Candidate::where('candidate_name', 'like', '%Vikram%')
    ->orWhere('candidate_name', 'like', '%Prajapat%')
    ->get();

echo "Found " . $vikrams->count() . " candidates matching 'Vikram' or 'Prajapat':\n";
foreach ($vikrams as $v) {
    echo "ID: '{$v->id}' | Name: '{$v->candidate_name}' | Number: '{$v->candidate_number}'\n";
    
    // Enrollments
    $enrollments = CandidateEnrollment::where('candidate_id', $v->id)->with(['series', 'subject'])->get();
    echo "  Enrollments (" . $enrollments->count() . "):\n";
    foreach ($enrollments as $enr) {
        $subjectName = $enr->subject ? $enr->subject->subject_name : 'General/None';
        echo "    Enrollment ID: {$enr->id} | Series: {$enr->series->series_name} | Subject: {$subjectName}\n";
        
        // Subject Results
        $results = SubjectResult::where('enrollment_id', $enr->id)->get();
        foreach ($results as $res) {
            echo "      Result ID: {$res->id} | Subject ID: {$res->subject_id} | Grade: {$res->grade} | PUM: {$res->pum}\n";
        }
    }
    echo "\n";
}
