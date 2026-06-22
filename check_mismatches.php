<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;
use App\Models\CandidateEnrollment;
use Illuminate\Support\Facades\DB;

$results = SubjectResult::with('enrollment')->get();
$mismatches = 0;
foreach ($results as $result) {
    if ($result->enrollment && $result->enrollment->subject_id !== $result->subject_id) {
        $mismatches++;
    }
}
echo "Mismatched SubjectResults: $mismatches\n";
