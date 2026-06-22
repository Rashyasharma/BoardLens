<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Subject;
use App\Models\Component;

$subjects = Subject::where('subject_code', 'like', '0510%')->get();
foreach ($subjects as $s) {
    echo "Subject: {$s->subject_name} ({$s->subject_code}), ID: {$s->id}\n";
    $components = Component::where('subject_id', $s->id)->get();
    foreach ($components as $c) {
        echo "  - Component ID: {$c->id} | Code: {$c->component_code} | Name: {$c->component_name} | Max: {$c->total_marks}\n";
    }
}
