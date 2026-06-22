<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$grades = DB::table('subject_results')->select('grade')->distinct()->pluck('grade');
echo "All unique grades in database:\n";
foreach ($grades as $g) {
    echo "  - '{$g}'\n";
}
