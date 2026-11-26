@echo off
rem Runs the CRM email queue worker. Keep this window open while the CRM is in use:
rem emails are delivered by this process, not by the web page.
title CRM email queue worker

set PHP_BIN=C:\xampp\php\php.exe
if not exist "%PHP_BIN%" set PHP_BIN=php

:loop
"%PHP_BIN%" artisan queue:work database --queue=emails --sleep=2 --tries=2 --timeout=60
if errorlevel 1 (
    echo Worker stopped, restarting in 5 seconds... ^(Ctrl+C to quit^)
    timeout /t 5 /nobreak >nul
)
goto loop
