<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SubjectResult;
use App\Models\CandidateEnrollment;
use Illuminate\Support\Facades\DB;

// Query all results with their candidate details
$results = SubjectResult::with('enrollment')->get();

// Group results by candidate_id, series_id, and subject_id
$grouped = [];
foreach ($results as $res) {
    if (!$res->enrollment) continue;
    $candId = $res->enrollment->candidate_id;
    $seriesId = $res->series_id;
    $subId = $res->subject_id;
    
    $key = "{$candId}_{$seriesId}_{$subId}";
    $grouped[$key][] = $res;
}

echo "Scanning for duplicate subject results...\n";
$deletedCount = 0;

foreach ($grouped as $key => $resGroup) {
    if (count($resGroup) > 1) {
        echo "Duplicate found for key {$key} (Count: " . count($resGroup) . "):\n";
        
        // Sort the group so that the one we want to KEEP is first.
        // We want to keep the one with the highest PUM. If PUMs are equal, keep the one created later.
        usort($resGroup, function($a, $b) {
            if ($a->pum != $b->pum) {
                return $b->pum <=> $a->pum; // descending by PUM
            }
            return strcmp($b->created_at, $a->created_at); // descending by creation time
        });
        
        $keep = $resGroup[0];
        echo "  KEEPING: ID: {$keep->id} | PUM: {$keep->pum} | Created: {$keep->created_at} | Enrollment ID: {$keep->enrollment_id}\n";
        
        for ($i = 1; $i < count($resGroup); $i++) {
            $dup = $resGroup[$i];
            echo "  DELETING: ID: {$dup->id} | PUM: {$dup->pum} | Created: {$dup->created_at} | Enrollment ID: {$dup->enrollment_id}\n";
            $dup->delete();
            $deletedCount++;
        }
    }
}

echo "\nTotal duplicates deleted: {$deletedCount}\n";

// Clean up general enrollments that have no results or subject-specific enrollments left
echo "Cleaning up empty enrollments...\n";
$enrollments = CandidateEnrollment::all();
$deletedEnrollments = 0;

foreach ($enrollments as $enr) {
    // If it's a general enrollment (subject_id is null)
    if (is_null($enr->subject_id)) {
        // Check if there are any subject-specific enrollments for this candidate, series, and qualification
        $hasSubjectEnrollments = CandidateEnrollment::where('candidate_id', $enr->candidate_id)
            ->where('series_id', $enr->series_id)
            ->where('qualification_id', $enr->qualification_id)
            ->whereNotNull('subject_id')
            ->exists();
            
        // Check if there are any subject results referencing this enrollment
        $hasResults = SubjectResult::where('enrollment_id', $enr->id)->exists();
        
        if (!$hasSubjectEnrollments && !$hasResults) {
            echo "  Deleting empty general enrollment: {$enr->id} (Cand: {$enr->candidate_id}, Series: {$enr->series_id})\n";
            $enr->delete();
            $deletedEnrollments++;
        }
    }
}

echo "Total empty enrollments deleted: {$deletedEnrollments}\n";
