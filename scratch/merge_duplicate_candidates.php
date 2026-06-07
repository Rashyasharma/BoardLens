<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

try {
    $candidates = Candidate::all();
    $byNumber = $candidates->groupBy('candidate_number');
    $mergedCount = 0;

    foreach ($byNumber as $num => $group) {
        if ($group->count() > 1) {
            // Group by normalized name within the same candidate number
            $byNormalizedName = $group->groupBy(function($c) {
                return strtolower(preg_replace('/\s+/', ' ', trim($c->candidate_name)));
            });

            foreach ($byNormalizedName as $name => $nameGroup) {
                if ($nameGroup->count() > 1) {
                    echo "Merging duplicates for candidate number [{$num}] ('{$name}'):\n";
                    
                    // We'll keep the one with the cleaner name (no double spaces) or the oldest one
                    $sorted = $nameGroup->sortBy(function($c) {
                        // Prefer names without multiple spaces
                        $hasDoubleSpace = strpos($c->candidate_name, '  ') !== false;
                        return ($hasDoubleSpace ? 1 : 0) . '_' . $c->created_at;
                    })->values();

                    $primary = $sorted[0];
                    echo "  KEEPING primary: ID: '{$primary->id}' | Name: '{$primary->candidate_name}'\n";

                    for ($i = 1; $i < $sorted->count(); $i++) {
                        $duplicate = $sorted[$i];
                        echo "  MERGING duplicate: ID: '{$duplicate->id}' | Name: '{$duplicate->candidate_name}'\n";

                        // Update enrollments
                        $updatedEnrollments = CandidateEnrollment::where('candidate_id', $duplicate->id)
                            ->update(['candidate_id' => $primary->id]);

                        echo "    Updated {$updatedEnrollments} enrollments.\n";

                        // Delete the duplicate candidate
                        $duplicate->delete();
                        $mergedCount++;
                    }
                }
            }
        }
    }

    DB::commit();
    echo "\nSuccessfully merged {$mergedCount} duplicate candidate profiles.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Error merging candidates: " . $e->getMessage() . "\n";
}
