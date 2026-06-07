<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Ensure SQLite has a writable temporary directory
$tempDir = 'C:/Users/HP11/CambridgeInsights_db';
putenv("TEMP={$tempDir}");
putenv("TMP={$tempDir}");
$_ENV['TEMP'] = $tempDir;
$_ENV['TMP'] = $tempDir;

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
