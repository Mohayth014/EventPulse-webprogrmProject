@echo off
title EventPulse - Next-Gen Event Registration & Ticketing System
echo ================================================================
echo           EventPulse Server - PHP & MySQL Local Runner
echo ================================================================
echo.

set PHP_PATH=C:\Users\mohet\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe

if exist "%PHP_PATH%" (
    echo [OK] Detected PHP 8.3 binary at: %PHP_PATH%
) else (
    set PHP_PATH=php
    echo [INFO] Using global php command from PATH
)

echo.
echo [1/3] Running database schema migration & seeder...
"%PHP_PATH%" config\setup.php

echo.
echo [2/3] Launching local PHP development server on http://localhost:8000 ...
echo [INFO] Press Ctrl+C in this console anytime to shut down the server.
echo.

start http://localhost:8000

"%PHP_PATH%" -S localhost:8000
pause
