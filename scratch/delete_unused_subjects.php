<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cbse\CbseSubject;

$unused = CbseSubject::doesntHave('results')->get();

echo "Found " . $unused->count() . " unused subjects:\n";
foreach ($unused as $sub) {
    $qualName = $sub->qualification ? $sub->qualification->qualification_name : 'N/A';
    echo "- [{$sub->subject_code}] {$sub->subject_name} ({$qualName})\n";
}

$deleted = CbseSubject::doesntHave('results')->delete();
echo "Successfully deleted {$deleted} unused subjects.\n";
