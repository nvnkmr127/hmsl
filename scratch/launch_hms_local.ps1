# HMS Local Launch & Installation Script
$ProjectRoot = Resolve-Path "$PSScriptRoot\.."
Set-Location $ProjectRoot

Write-Host "------------------------------------------------" -ForegroundColor Green
Write-Host "HMS OFFLINE-FIRST LOCAL INSTALLER" -ForegroundColor Green
Write-Host "------------------------------------------------" -ForegroundColor Green
Write-Host "Running from: $ProjectRoot" -ForegroundColor Gray

# 0. Cleanup lingering processes
Write-Host "Cleaning up background processes..." -ForegroundColor Gray
$TargetProcesses = "php", "HMS Desktop", "hms-desktop", "hms_desktop", "cargo", "tauri", "rustc", "rust-analyzer", "node", "npx", "msedge", "WebView2Loader", "rust"
Get-Process | Where-Object { $TargetProcesses -contains $_.Name -or $_.Name -like "*tauri*" -or $_.Name -like "*cargo*" -or $_.Name -like "*rustc*" } | Stop-Process -Force -ErrorAction SilentlyContinue

# Disable incremental compilation to avoid file locks on Windows
$env:CARGO_INCREMENTAL = "0"
$env:RUST_BACKTRACE = "1"

# Move build target to C: drive to bypass locks on E: drive (common Windows fix)
$CustomTarget = "$env:LOCALAPPDATA\hmsl_tauri_target"
if (!(Test-Path $CustomTarget)) { New-Item $CustomTarget -ItemType Directory -Force }
$env:CARGO_TARGET_DIR = $CustomTarget
Write-Host "Using build target: $CustomTarget" -ForegroundColor Gray

Write-Host "Waiting for file handles to release..." -ForegroundColor Gray
Start-Sleep -Seconds 2

# Clear port 8000 specifically
$portProcess = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
if ($portProcess) {
    Write-Host "Freeing port 8000..." -ForegroundColor Gray
    Stop-Process -Id $portProcess.OwningProcess -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 1
}

# Optional: Try to clear the Rust target folder if locks persist
# If you still see "file in use" errors, uncomment the line below:
# Remove-Item -Recurse -Force "tauri/src-tauri/target" -ErrorAction SilentlyContinue

# 1. Validate Sidecar
$sidecarDir = "tauri/src-tauri/bin"
if (!(Test-Path $sidecarDir)) {
    New-Item -ItemType Directory -Path $sidecarDir -Force
}

$sidecarPath = "$sidecarDir/php-x86_64-pc-windows-msvc.exe"
$sysPhp = "C:\xampp\php\php.exe"

if (!(Test-Path $sidecarPath)) {
    Write-Host "Attempting to locate system PHP..." -ForegroundColor Gray
    if (Test-Path $sysPhp) {
        Copy-Item $sysPhp $sidecarPath
        Write-Host "Copied XAMPP PHP to sidecar." -ForegroundColor Green
    } else {
        Write-Error "PHP not found at $sysPhp. Please update the path in this script."
        exit
    }
}

# 2. Setup Laravel Backend
Write-Host "Setting up Laravel backend (Fresh SQLite)..." -ForegroundColor Cyan
if (!(Test-Path "database")) {
    New-Item "database" -ItemType Directory
}
if (!(Test-Path "database/database.sqlite")) {
    New-Item "database/database.sqlite" -ItemType File
}
# Use migrate:fresh with --seed to ensure test users and roles are created
& $sysPhp artisan migrate:fresh --force --database=sqlite --seed

# 3. Check for Rust (Required for Tauri)
if (!(where.exe cargo)) {
    Write-Error "❌ Rust (Cargo) not found! Please install it from https://rustup.rs/ and restart your terminal."
    exit
}

# 3. Build Assets (Laravel & React)
Write-Host "Building frontend assets..." -ForegroundColor Cyan
# Remove Vite hot file to ensure Laravel loads built assets instead of looking for dev server
if (Test-Path "public/hot") { Remove-Item "public/hot" -Force }

Write-Host "Building Laravel assets..." -ForegroundColor Gray
npm install
npm run build

if (Test-Path "webapp") {
    Write-Host "Building React assets..." -ForegroundColor Gray
    cd webapp
    npm install
    npm run build
    if ($LASTEXITCODE -ne 0) {
        Write-Host "TypeScript check failed, trying direct Vite build..." -ForegroundColor Yellow
        npx vite build
    }
    cd ..
}

# 4. Launch Backend & Tauri
Write-Host "Launching Laravel Backend Server..." -ForegroundColor Cyan
# Start PHP artisan serve in the background
Start-Process $sysPhp -ArgumentList "artisan", "serve", "--port=8000" -WindowStyle Hidden

Write-Host "Starting Windows Application..." -ForegroundColor Green
cd tauri

# Fix for "Cannot find native binding" error
if (Test-Path "node_modules") {
    Write-Host "Cleaning up old Tauri modules..." -ForegroundColor Gray
    Remove-Item -Recurse -Force node_modules
    if (Test-Path "package-lock.json") { Remove-Item -Force package-lock.json }
}

Write-Host "Installing Tauri CLI and native bindings..." -ForegroundColor Cyan
npm install

# Give the backend server a moment to initialize
Start-Sleep -Seconds 2

# Run Tauri dev or build
if ($args -contains "--build") {
    Write-Host "Building Production Installer..." -ForegroundColor Cyan
    $maxRetries = 3
    $retryCount = 0
    $success = $false

    while (-not $success -and $retryCount -lt $maxRetries) {
        $retryCount++
        $env:CARGO_INCREMENTAL = "0"
        $env:CARGO_BUILD_JOBS = "2"
        cmd /c "npx tauri build"
        if ($LASTEXITCODE -eq 0) {
            $success = $true
        } else {
            Write-Warning "Build failed (Exit Code: $LASTEXITCODE). Likely a file lock. Retrying ($retryCount/$maxRetries)..."
            Start-Sleep -Seconds 5
        }
    }

    if ($success) {
        Write-Host "✅ Installer built successfully!" -ForegroundColor Green
        $bundlePath = Join-Path $env:CARGO_TARGET_DIR "release/bundle"
        if (Test-Path $bundlePath) {
            $installerPath = Resolve-Path $bundlePath
            Write-Host "Opening installer folder: $installerPath" -ForegroundColor Gray
            explorer $installerPath
        } else {
            Write-Warning "Build reported success but bundle path not found at $bundlePath"
        }
    } else {
        Write-Error "❌ Build failed after $maxRetries attempts. Please try adding an exclusion for this folder in Windows Defender."
    }
} else {
    Write-Host "Launching in Development Mode..." -ForegroundColor Cyan
    $env:CARGO_INCREMENTAL = "0"
    $env:CARGO_BUILD_JOBS = "2"
    cmd /c "npx tauri dev"
}
