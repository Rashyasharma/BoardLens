@echo off
:: Ensure Node.js is in the path for this script session
set "PATH=C:\Program Files\nodejs;%PATH%"

:: Start Laravel Server
start /b php artisan serve --port=8004

:: Check if npm is available before running dev server
where npm >nul 2>nul
if %errorlevel% equ 0 (
    start /b npm run dev
) else (
    echo [WARNING] npm was not found on your system. Vite dev server for frontend assets could not be started.
    echo Please install Node.js (which includes npm) to compile and render frontend styles correctly.
)

:: Wait a few seconds for the server to spin up
timeout /t 3 /nobreak >nul
:: Open Browser
start http://127.0.0.1:8004
