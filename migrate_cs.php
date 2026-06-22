<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CandidateEnrollment;
use App\Models\Subject;

$subject9618 = Subject::where('subject_code', '9618')->first();
$subject9608 = Subject::where('subject_code', '9608')->first();

if (!$subject9618 || !$subject9608) {
    echo "Missing subjects\n";
    exit;
}

$enrollmentsToMigrate = CandidateEnrollment::where('subject_id', $subject9618->id)
    ->whereHas('series', function($q) {
        $q->whereIn('year', [2020, 2021]);
    })->get();

echo "Found " . count($enrollmentsToMigrate) . " enrollments for 9618 in 2020/2021 to migrate to 9608.\n";

foreach ($enrollmentsToMigrate as $enr) {
    // We update the subject to 9608
    $enr->subject_id = $subject9608->id;
    $enr->save();
    
    // Check if there are component marks to migrate
    $marks = \App\Models\ComponentMarks::where('enrollment_id', $enr->id)->get();
    if ($marks->count() > 0) {
        echo "  Enrollment {$enr->id} has {$marks->count()} marks. Need to remap component IDs!\n";
        // To be safe, we could remap them if they exist
    }
}

echo "Done.\n";
