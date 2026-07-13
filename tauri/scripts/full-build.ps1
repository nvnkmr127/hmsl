# E:\hmsl\tauri\scripts\full-build.ps1

$baseDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$tauriDir = Split-Path -Parent $baseDir
$hmslDir = Split-Path -Parent $tauriDir

Write-Host "=== Step 1: Running PHP bundling script ===" -ForegroundColor Green
powershell -ExecutionPolicy Bypass -File (Join-Path $baseDir "bundle-php.ps1")
if ($LASTEXITCODE -ne 0) {
    Write-Error "Step 1 failed: PHP bundling script returned exit code $LASTEXITCODE"
    exit 1
}

Write-Host "=== Step 2: Compiling Vite assets ===" -ForegroundColor Green
Push-Location $hmslDir
npm.cmd run build
if ($LASTEXITCODE -ne 0) {
    Write-Error "Step 2 failed: Vite compilation returned exit code $LASTEXITCODE"
    Pop-Location
    exit 1
}
Pop-Location

Write-Host "=== Step 3: Compiling Tauri Windows installer ===" -ForegroundColor Green

# Kill any running HMS/PHP processes to release file locks before building
Write-Host "  Stopping any running HMS processes..." -ForegroundColor Yellow
Stop-Process -Name "HMS - Hospital Management" -Force -ErrorAction SilentlyContinue
Stop-Process -Name "php-x86_64-pc-windows-msvc" -Force -ErrorAction SilentlyContinue
Stop-Process -Name "php" -Force -ErrorAction SilentlyContinue
Start-Sleep -Milliseconds 1500

Push-Location $tauriDir
npx.cmd tauri build
if ($LASTEXITCODE -ne 0) {
    Write-Error "Step 3 failed: Tauri compilation returned exit code $LASTEXITCODE"
    Pop-Location
    exit 1
}
Pop-Location

Write-Host "=== Step 4: Copying installer to dist/ ===" -ForegroundColor Green
$distDir = Join-Path $hmslDir "dist"
if (!(Test-Path $distDir)) {
    New-Item -ItemType Directory -Path $distDir -Force | Out-Null
}

$nsisDir = Join-Path $tauriDir "src-tauri\target\release\bundle\nsis"
if (Test-Path $nsisDir) {
    $installers = Get-ChildItem -Path $nsisDir -Filter "*.exe"
    foreach ($inst in $installers) {
        $destPath = Join-Path $distDir $inst.Name
        Copy-Item $inst.FullName $destPath -Force
        $sizeMB = [Math]::Round($inst.Length / 1MB, 2)
        Write-Host "[OK] Installer copied to: $destPath ($sizeMB MB)" -ForegroundColor Cyan
    }
} else {
    Write-Error "NSIS bundle directory not found at $nsisDir"
    exit 1
}

Write-Host "=== Full Build Complete! ===" -ForegroundColor Green
