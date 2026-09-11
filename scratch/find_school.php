<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$schoolId = '019e5ed2-be69-7193-b485-69770f96e60c';

// Find Chrisanne in both candidate records
echo "=== All Chrisanne records ===\n";
$all = DB::table('candidates')->where('candidate_name','LIKE','%CHRISANNE%')->get(['id','candidate_number','candidate_name','school_id']);
foreach ($all as $c) echo "  id={$c->id} | num={$c->candidate_number} | name={$c->candidate_name}\n";

// Find candidates with 4xxx numbers
echo "\n=== Candidates with 4xxx numbers ===\n";
$fours = DB::table('candidates')
    ->where('candidate_number','LIKE','4%')
    ->where('school_id', $schoolId)
    ->orderBy('candidate_number')
    ->get(['candidate_number','candidate_name']);
foreach ($fours as $c) echo "  #{$c->candidate_number} | {$c->candidate_name}\n";

echo "\n=== Candidates with 5xxx numbers ===\n";
$fives = DB::table('candidates')
    ->where('candidate_number','LIKE','5%')
    ->where('school_id', $schoolId)
    ->orderBy('candidate_number')
    ->get(['candidate_number','candidate_name']);
foreach ($fives as $c) echo "  #{$c->candidate_number} | {$c->candidate_name}\n";

// Also check single-digit
echo "\n=== Candidate #8 ===\n";
$eight = DB::table('candidates')->where('candidate_number','8')->get(['candidate_number','candidate_name']);
foreach ($eight as $c) echo "  #{$c->candidate_number} | {$c->candidate_name}\n";
