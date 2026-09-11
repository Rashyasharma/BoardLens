<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Subject;
use App\Models\ComponentSet;

$subject = Subject::where('subject_code', '9709')->first();
echo "Subject ID: " . $subject->id . "\n";

$sets = ComponentSet::where('subject_id', $subject->id)->get();
foreach($sets as $s) {
    echo "Set: {$s->id} - default: {$s->is_default} - label: {$s->label} - start_year: {$s->start_year} - end_year: {$s->end_year}\n";
}
