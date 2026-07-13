@echo off
cd /d %~dp0..\..
start /B php artisan serve --host=127.0.0.1 --port=8000
timeout /t 3
cd tauri
npx tauri dev
