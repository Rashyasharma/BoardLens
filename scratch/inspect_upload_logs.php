<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\UploadLog;

$logs = UploadLog::orderBy('uploaded_at', 'desc')->get();
echo "Total Upload Logs: " . $logs->count() . "\n";
foreach ($logs as $log) {
    echo "ID: {$log->id} | File: {$log->file_name} | Type: {$log->upload_type} | Status: {$log->status} | Processed: {$log->records_processed} | Date: {$log->uploaded_at}\n";
}
