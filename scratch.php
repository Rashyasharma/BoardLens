<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$subjs = \App\Models\Cbse\CbseSubject::where('subject_name', 'LIKE', 'Subject %')->get();
if ($subjs->isEmpty()) {
    echo "No unnamed subjects found.\n";
} else {
    foreach ($subjs as $s) {
        echo $s->subject_code . " - " . $s->subject_name . "\n";
    }
}
