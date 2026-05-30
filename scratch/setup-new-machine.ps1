# HMS Desktop — New Machine Setup Script
# Run this once on a fresh Windows installation to bootstrap the app.

param(
    [switch]$Force
)

$ErrorActionPreference = "Stop"
$ProjectRoot = Resolve-Path "$PSScriptRoot\.."
Set-Location $ProjectRoot

Write-Host ""
Write-Host "  HMS Desktop — First-Run Setup" -ForegroundColor Cyan
Write-Host "  ================================" -ForegroundColor Cyan
Write-Host ""

# ─── 1. Locate PHP ────────────────────────────────────────────────────────────
Write-Host "[1/6] Checking for PHP..." -ForegroundColor Yellow

$phpExe = $null

# Try XAMPP first (most common on Windows HMS installs)
$xamppPaths = @(
    "C:\xampp\php\php.exe",
    "C:\xampp8\php\php.exe",
    "C:\xampp7\php\php.exe"
)
foreach ($p in $xamppPaths) {
    if (Test-Path $p) { $phpExe = $p; break }
}

# Fall back to system PATH
if (-not $phpExe) {
    try {
        $phpExe = (Get-Command php -ErrorAction Stop).Source
    } catch {
        Write-Host "  ERROR: PHP not found. Install XAMPP or add PHP to your PATH." -ForegroundColor Red
        exit 1
    }
}

$phpVersion = & $phpExe -r "echo PHP_VERSION;"
Write-Host "  Found PHP $phpVersion at: $phpExe" -ForegroundColor Green

# ─── 2. Locate Composer ───────────────────────────────────────────────────────
Write-Host "[2/6] Checking for Composer..." -ForegroundColor Yellow

$composerExe = $null
$composerPaths = @(
    "C:\ProgramData\ComposerSetup\bin\composer.bat",
    "$env:APPDATA\Composer\vendor\bin\composer.bat",
    "composer"
)
foreach ($p in $composerPaths) {
    try {
        $null = & $phpExe (Get-Command composer.phar -ErrorAction SilentlyContinue).Source --version 2>$null
    } catch {}
    if (Get-Command $p -ErrorAction SilentlyContinue) { $composerExe = $p; break }
}
if (-not $composerExe) {
    try {
        $composerExe = (Get-Command composer -ErrorAction Stop).Source
    } catch {
        Write-Host "  ERROR: Composer not found. Download from https://getcomposer.org" -ForegroundColor Red
        exit 1
    }
}

Write-Host "  Found Composer: $composerExe" -ForegroundColor Green

# ─── 3. Install Composer dependencies ────────────────────────────────────────
Write-Host "[3/6] Installing PHP dependencies..." -ForegroundColor Yellow

if (-not (Test-Path "$ProjectRoot\vendor") -or $Force) {
    & $composerExe install --no-dev --optimize-autoloader --no-interaction
    if ($LASTEXITCODE -ne 0) {
        Write-Host "  ERROR: composer install failed." -ForegroundColor Red
        exit 1
    }
    Write-Host "  Dependencies installed." -ForegroundColor Green
} else {
    Write-Host "  vendor/ already exists, skipping. Use -Force to reinstall." -ForegroundColor DarkGray
}

# ─── 4. Create SQLite database ────────────────────────────────────────────────
Write-Host "[4/6] Checking database file..." -ForegroundColor Yellow

$dbPath = "$ProjectRoot\database\database.sqlite"
if (-not (Test-Path $dbPath)) {
    New-Item -ItemType File -Path $dbPath -Force | Out-Null
    Write-Host "  Created database/database.sqlite" -ForegroundColor Green
} else {
    Write-Host "  database.sqlite already exists." -ForegroundColor DarkGray
}

# ─── 5. Copy .env ─────────────────────────────────────────────────────────────
Write-Host "[5/6] Checking .env file..." -ForegroundColor Yellow

if (-not (Test-Path "$ProjectRoot\.env")) {
    if (Test-Path "$ProjectRoot\.env.offline") {
        Copy-Item "$ProjectRoot\.env.offline" "$ProjectRoot\.env"
        Write-Host "  Copied .env.offline -> .env" -ForegroundColor Green
    } elseif (Test-Path "$ProjectRoot\.env.example") {
        Copy-Item "$ProjectRoot\.env.example" "$ProjectRoot\.env"
        Write-Host "  Copied .env.example -> .env" -ForegroundColor Green
    } else {
        Write-Host "  WARNING: No .env.offline or .env.example found. Creating minimal .env." -ForegroundColor DarkYellow
        Set-Content "$ProjectRoot\.env" "APP_NAME=HMS`nAPP_ENV=production`nAPP_KEY=`nAPP_DEBUG=false`nAPP_URL=http://localhost`n`nDB_CONNECTION=sqlite`nDB_DATABASE=database/database.sqlite`n`nSYNC_SERVER_URL=`n"
    }
} else {
    Write-Host "  .env already exists." -ForegroundColor DarkGray
}

# ─── 6. Artisan bootstrap ─────────────────────────────────────────────────────
Write-Host "[6/6] Running artisan setup..." -ForegroundColor Yellow

& $phpExe artisan key:generate --force
if ($LASTEXITCODE -ne 0) { Write-Host "  WARNING: key:generate returned non-zero." -ForegroundColor DarkYellow }

& $phpExe artisan migrate --force
if ($LASTEXITCODE -ne 0) {
    Write-Host "  ERROR: Migration failed." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "  Setup complete! Launch the HMS Desktop app now." -ForegroundColor Green
Write-Host ""
Write-Host "  The app will open the first-run wizard in your browser." -ForegroundColor Cyan
Write-Host "  Run:  php artisan serve" -ForegroundColor White
Write-Host ""
