<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;
use App\Models\CandidateEnrollment;
use Illuminate\Support\Facades\DB;

DB::transaction(function() {
    $results = SubjectResult::with('enrollment')->get();
    $fixed = 0;
    
    foreach ($results as $result) {
        if ($result->enrollment && $result->enrollment->subject_id !== $result->subject_id) {
            // It points to a mismatched enrollment!
            $candidateId = $result->enrollment->candidate_id;
            $seriesId = $result->series_id;
            $subjectId = $result->subject_id;
            
            // Find or create the correct subject-specific enrollment
            $subjectEnrollment = CandidateEnrollment::firstOrCreate(
                [
                    'candidate_id' => $candidateId,
                    'series_id' => $seriesId,
                    'subject_id' => $subjectId,
                ],
                [
                    'qualification_id' => $result->enrollment->qualification_id,
                    'enrolled_date' => $result->enrollment->enrolled_date,
                    'enrollment_status' => $result->enrollment->enrollment_status,
                ]
            );
            
            $result->enrollment_id = $subjectEnrollment->id;
            $result->save();
            $fixed++;
        }
    }
    
    echo "Migrated $fixed SubjectResults to correct subject-specific enrollments.\n";
});
