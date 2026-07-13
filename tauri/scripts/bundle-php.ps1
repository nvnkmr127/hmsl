# E:\hmsl\tauri\scripts\bundle-php.ps1

$baseDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$tauriDir = Split-Path -Parent $baseDir
$srcTauriDir = Join-Path $tauriDir "src-tauri"
$binariesDir = Join-Path $srcTauriDir "binaries"
$phpDestDir = Join-Path $srcTauriDir "php_bin"

# Ensure directories exist
if (!(Test-Path $binariesDir)) {
    New-Item -ItemType Directory -Path $binariesDir -Force
}
if (!(Test-Path $phpDestDir)) {
    New-Item -ItemType Directory -Path $phpDestDir -Force
}

# Download PHP 8.2 NTS x64
$phpUrl = "https://windows.php.net/downloads/releases/archives/php-8.2.12-nts-Win32-vs16-x64.zip"
$zipPath = Join-Path $binariesDir "php.zip"

Write-Host "Downloading PHP from $phpUrl..."
Invoke-WebRequest -Uri $phpUrl -OutFile $zipPath

Write-Host "Extracting PHP to $phpDestDir..."
Expand-Archive -Path $zipPath -DestinationPath $phpDestDir -Force

# Remove zip
Remove-Item $zipPath -Force

# Copy php.ini.dist from tauri/php.ini.dist to binaries/php/php.ini
$iniDist = Join-Path $tauriDir "php.ini.dist"
$iniDest = Join-Path $phpDestDir "php.ini"
if (Test-Path $iniDist) {
    Copy-Item $iniDist $iniDest -Force
    Write-Host "Copied php.ini"
} else {
    Write-Warning "php.ini.dist not found at $iniDist!"
}

# Copy php.exe to binaries/php-x86_64-pc-windows-msvc.exe for sidecar
$phpExe = Join-Path $phpDestDir "php.exe"
$sidecarExe = Join-Path $binariesDir "php-x86_64-pc-windows-msvc.exe"
if (Test-Path $phpExe) {
    Copy-Item $phpExe $sidecarExe -Force
    Write-Host "Copied php.exe as sidecar: $sidecarExe"
}

# Download composer.phar
$composerUrl = "https://getcomposer.org/composer.phar"
$composerDest = Join-Path $binariesDir "composer.phar"
Write-Host "Downloading Composer from $composerUrl..."
Invoke-WebRequest -Uri $composerUrl -OutFile $composerDest

Write-Host "PHP Bundling Complete!"
