@echo off
title BoardLens Launcher
echo Starting BoardLens Backend Server...
cd /d "C:\Users\HP11\Desktop\My Projects\BoardLens"
start "BoardLens Backend" cmd /k "php artisan serve --port=8080"

echo Starting BoardLens Frontend Server...
start "BoardLens Frontend" cmd /k "npm run dev"

echo Waiting for servers to start...
timeout /t 3 /nobreak > nul

echo Opening BoardLens in browser...
start http://127.0.0.1:8080
exit
