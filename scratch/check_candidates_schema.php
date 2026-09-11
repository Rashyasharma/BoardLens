<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "Candidates table columns:\n";
print_r(Schema::getColumnListing('candidates'));

echo "Candidate Enrollments table columns:\n";
print_r(Schema::getColumnListing('candidate_enrollments'));

echo "Subject Results table columns:\n";
print_r(Schema::getColumnListing('subject_results'));

echo "Exam Series table columns:\n";
print_r(Schema::getColumnListing('exam_series'));
