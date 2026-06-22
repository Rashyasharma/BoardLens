<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Subject;

$subjects = Subject::whereIn('subject_code', ['0654', '0653', '0610'])->get();

foreach ($subjects as $s) {
    echo "ID: {$s->id} | Code: {$s->subject_code} | Name: {$s->subject_name}\n";
}
