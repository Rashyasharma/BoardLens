<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader
require __DIR__.'/../vendor/autoload.php';

// Vercel read-only filesystem: set up writable /tmp directories BEFORE bootstrapping
$storagePath = '/tmp/storage';
foreach (['app', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $dir) {
    if (!is_dir("$storagePath/$dir")) {
        mkdir("$storagePath/$dir", 0777, true);
    }
}

// Copy SQLite database to writable /tmp so WAL mode works
$dbPath = '/tmp/database.sqlite';
if (!file_exists($dbPath)) {
    copy(__DIR__.'/../database/database.sqlite', $dbPath);
}
putenv('DB_DATABASE=' . $dbPath);
$_ENV['DB_DATABASE'] = $dbPath;
$_SERVER['DB_DATABASE'] = $dbPath;

// Bootstrap Laravel
$app = require_once __DIR__.'/../bootstrap/app.php';

// Override storage path to /tmp
$app->useStoragePath($storagePath);

// Handle the request (Laravel 13 style)
$app->handleRequest(Request::capture());