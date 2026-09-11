<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Candidate;
use App\Models\CandidateEnrollment;
use Illuminate\Support\Facades\DB;

$c1 = Candidate::find('01a08f13-aa1f-7344-bf42-6b1eabd69347'); // JAIN YOGITA SURESH
$c2 = Candidate::find('01a08f15-ee99-73e6-9e97-9ef8f04e6c73'); // YOGITA SURESH JAIN

if ($c1 && $c2) {
    DB::transaction(function () use ($c1, $c2) {
        // Update all enrollments to point to C2
        CandidateEnrollment::where('candidate_id', $c1->id)
            ->update(['candidate_id' => $c2->id]);
            
        // Delete C1
        $c1->delete();
        
        echo "Successfully merged '{$c1->candidate_name}' into '{$c2->candidate_name}'." . PHP_EOL;
    });
} else {
    echo "Candidates not found." . PHP_EOL;
}
