<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$components = \App\Models\Component::with('subject')->take(15)->get();
foreach ($components as $c) {
    echo "ID: {$c->id} | Code: {$c->component_code} | Name: {$c->component_name} | Subject: {$c->subject->subject_name} ({$c->subject->subject_code})\n";
}
