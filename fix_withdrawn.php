<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$count = \App\Models\CandidateEnrollment::where('enrollment_status', 'withdrawn')->update(['enrollment_status' => 'enrolled']);
echo "Reset $count enrollments from withdrawn to enrolled.\n";
