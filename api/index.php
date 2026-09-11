<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';

// Tell Laravel to use /tmp/storage instead of the read-only /var/task/storage
$storagePath = $_ENV['APP_STORAGE'] ?? '/tmp/storage';
$app->useStoragePath($storagePath);

// Ensure essential subdirectories exist
foreach (['app', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $dir) {
    if (!is_dir("$storagePath/$dir")) {
        mkdir("$storagePath/$dir", 0777, true);
    }
}

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);