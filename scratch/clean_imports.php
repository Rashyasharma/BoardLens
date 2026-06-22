<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Cbse\CbseStudent;
use App\Models\Cbse\CbseResult;

$ids = CbseStudent::where('admission_number', 'like', 'CBSE-%')->pluck('id');
$rCount = CbseResult::whereIn('student_id', $ids)->delete();
$sCount = CbseStudent::whereIn('id', $ids)->delete();

echo "Deleted {$rCount} results and {$sCount} students.\n";
