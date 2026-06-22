<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$biology = \App\Models\Subject::where('subject_code', '0610')->first();
if (!$biology) {
    echo "Biology 0610 not found\n";
    exit(1);
}

echo "BIOLOGY 0610 COMPONENTS:\n";
foreach ($biology->components as $comp) {
    echo "Code: {$comp->component_code}, Name: {$comp->component_name}, Max Marks: {$comp->total_marks}, Label: {$comp->component_label}\n";
}

$subjects = \App\Models\Subject::all();
echo "\nALL SUBJECTS IN DB:\n";
foreach ($subjects as $sub) {
    echo "Code: {$sub->subject_code}, Name: {$sub->subject_name}\n";
}
