<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Detect Vercel (read-only filesystem)
if (is_dir('/var/task')) {
    // Set env vars that vercel.json fails to inject into PHP runtime
    $vercelEnv = [
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false', // Turn off debug mode for production
        'APP_KEY' => 'base64:84ddEbXcSCiYt/MgVwPwBDpIONNdNPTVmgnRyyA9zRI=',
        'LOG_CHANNEL' => 'stderr',
        'DB_CONNECTION' => 'sqlite',
        'CACHE_DRIVER' => 'array',
        'SESSION_DRIVER' => 'file', // Change back to file since we have /tmp
        'QUEUE_CONNECTION' => 'sync',
        'VIEW_COMPILED_PATH' => '/tmp/views',
        'APP_MAINTENANCE_DRIVER' => 'file',
    ];
    foreach ($vercelEnv as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    // Create writable storage directories in /tmp
    foreach (['app', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $dir) {
        $path = "/tmp/storage/$dir";
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    // Create /tmp/views for compiled Blade templates
    if (!is_dir('/tmp/views')) {
        mkdir('/tmp/views', 0777, true);
    }
    
    // Create /tmp/bootstrap/cache for package discovery
    if (!is_dir('/tmp/bootstrap/cache')) {
        mkdir('/tmp/bootstrap/cache', 0777, true);
    }

    // Copy SQLite database to writable /tmp
    $dbPath = '/tmp/database.sqlite';
    if (!file_exists($dbPath)) {
        $sourcePath = __DIR__ . '/../database/database.sqlite';
        if (file_exists($sourcePath)) {
            copy($sourcePath, $dbPath);
        } else {
            // Touch it so it exists if it wasn't bundled
            touch($dbPath);
        }
    }
    putenv("DB_DATABASE=$dbPath");
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

// We can safely handle requests normally now that the boot issues are fixed!
$app->handleRequest(Request::capture());
