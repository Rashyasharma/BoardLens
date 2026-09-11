<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$asLevelWrong = DB::table('qualifications')->where('qualification_type', 'AS_LEVEL')->first();
$asALevel = DB::table('qualifications')->where('qualification_type', 'AS_A_LEVEL')->first();
$aLevelWrong = DB::table('qualifications')->where('qualification_type', 'A_LEVEL')->first();

if (!$asALevel) {
    echo "AS_A_LEVEL not found!\n";
    exit;
}

$correctId = $asALevel->id;

if ($asLevelWrong) {
    $wrongId = $asLevelWrong->id;
    echo "Fixing AS_LEVEL records...\n";
    DB::table('subjects')->where('qualification_id', $wrongId)->update(['qualification_id' => $correctId]);
    DB::table('candidate_enrollments')->where('qualification_id', $wrongId)->update(['qualification_id' => $correctId]);
    DB::table('qualifications')->where('id', $wrongId)->delete();
}

if ($aLevelWrong) {
    $wrongId = $aLevelWrong->id;
    echo "Fixing A_LEVEL records...\n";
    DB::table('subjects')->where('qualification_id', $wrongId)->update(['qualification_id' => $correctId]);
    DB::table('candidate_enrollments')->where('qualification_id', $wrongId)->update(['qualification_id' => $correctId]);
    DB::table('qualifications')->where('id', $wrongId)->delete();
}

echo "Database fixed.\n";
