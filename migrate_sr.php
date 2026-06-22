<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\SubjectResult;
use App\Models\Subject;

$subject9608 = Subject::where('subject_code', '9608')->first();

$count = SubjectResult::whereHas('enrollment', function($q) use ($subject9608) {
    $q->where('subject_id', $subject9608->id);
})->where('subject_id', '!=', $subject9608->id)->update(['subject_id' => $subject9608->id]);

echo "Updated {$count} SubjectResult records to 9608.\n";
