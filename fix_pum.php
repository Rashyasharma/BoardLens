<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Update empty string PUM to 0
$updated = DB::table('subject_results')
    ->where('pum', '')
    ->update(['pum' => 0]);

echo "Updated $updated records.\n";
