@echo off
cd /d %~dp0..\..
call npm run build
cd tauri
npx tauri build
echo Build complete. Installer at: tauri/src-tauri/target/release/bundle/
