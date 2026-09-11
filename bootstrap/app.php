<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Throwable $e) {
            if (is_dir('/var/task')) {
                // If on Vercel, print the first error immediately before Laravel swallows it
                http_response_code(500);
                header('Content-Type: text/plain');
                echo "REPORTABLE INTERCEPTED ERROR:\n";
                echo $e->getMessage() . "\n";
                echo $e->getFile() . ":" . $e->getLine() . "\n";
                echo $e->getTraceAsString();
                exit(1);
            }
        });
    })->create();

// On Vercel, redirect storage to writable /tmp IMMEDATELY after app creation
if (is_dir('/var/task')) {
    $app->useStoragePath('/tmp/storage');
}

return $app;
