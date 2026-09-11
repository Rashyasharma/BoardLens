<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Detect Vercel environment (read-only filesystem)
$isVercel = isset($_ENV['VERCEL']) || getenv('VERCEL') || is_dir('/var/task');

if ($isVercel) {
    // Create writable storage directories in /tmp
    $storagePath = '/tmp/storage';
    foreach (['app', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $dir) {
        if (!is_dir("$storagePath/$dir")) {
            mkdir("$storagePath/$dir", 0777, true);
        }
    }

    // Copy SQLite database to writable /tmp (WAL mode needs write access)
    $dbPath = '/tmp/database.sqlite';
    if (!file_exists($dbPath)) {
        $source = __DIR__ . '/../database/database.sqlite';
        if (file_exists($source)) {
            copy($source, $dbPath);
        }
    }
    putenv('DB_DATABASE=' . $dbPath);
    $_ENV['DB_DATABASE'] = $dbPath;
    $_SERVER['DB_DATABASE'] = $dbPath;
} else {
    // Local Windows environment
    $tempDir = 'C:/Users/HP11/CambridgeInsights_db';
    if (is_dir($tempDir)) {
        putenv("TEMP={$tempDir}");
        putenv("TMP={$tempDir}");
        $_ENV['TEMP'] = $tempDir;
        $_ENV['TMP'] = $tempDir;
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// On Vercel, redirect storage to /tmp
if ($isVercel) {
    $app->useStoragePath('/tmp/storage');
}

$app->handleRequest(Request::capture());
