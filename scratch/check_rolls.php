<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mismatches = \App\Models\Cbse\CbseResult::with('student')
    ->get()
    ->filter(function($r) {
        return $r->student && $r->roll_number && $r->student->admission_number !== 'CBSE-' . $r->roll_number;
    })
    ->map(function($r) {
        return [
            'student_name' => $r->student->student_name,
            'admission_number' => $r->student->admission_number,
            'roll_number' => $r->roll_number
        ];
    })
    ->unique('admission_number')
    ->values();

echo "Mismatches: " . $mismatches->count() . "\n";
if ($mismatches->count() > 0) {
    print_r($mismatches->toArray());
} else {
    echo "All roll numbers match the admission numbers perfectly!\n";
}
