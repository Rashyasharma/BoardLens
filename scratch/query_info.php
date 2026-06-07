<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$candidates = \App\Models\Candidate::where('candidate_number', '0016')
    ->orWhere('candidate_number', '16')
    ->orWhere('candidate_name', 'like', '%PREET%')
    ->get();

foreach ($candidates as $c) {
    echo "ID: {$c->id} | Num: {$c->candidate_number} | Name: {$c->candidate_name} | School ID: {$c->school_id}\n";
}
