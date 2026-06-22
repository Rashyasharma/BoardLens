<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = App\Models\Subject::find('019e5ed2-bc8a-73e2-8d0f-98fb5825e1c8');
echo $s ? $s->subject_name : 'NOT FOUND';
