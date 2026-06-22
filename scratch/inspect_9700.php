<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$biology = \App\Models\Subject::where('subject_code', '9700')->first();
if (!$biology) {
    echo "Biology not found\n";
    exit(1);
}

echo "BIOLOGY 9700 COMPONENTS:\n";
foreach ($biology->components as $comp) {
    echo "Code: {$comp->component_code}, Name: {$comp->component_name}, Max Marks: {$comp->total_marks}, Label: {$comp->component_label}\n";
}
