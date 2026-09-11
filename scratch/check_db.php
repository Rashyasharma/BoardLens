<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use App\Models\CandidateEnrollment;
use App\Models\SubjectResult;

echo "Total enrollments: " . CandidateEnrollment::count() . "\n";
echo "Total results: " . SubjectResult::count() . "\n";
