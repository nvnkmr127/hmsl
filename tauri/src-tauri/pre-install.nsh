!include "WinVer.nsh"

# Minimum Windows 10 Check
${Unless} ${AtLeastWin10}
    MessageBox MB_OK|MB_ICONSTOP "Error: HMS requires Windows 10 or later."
    Abort
${EndUnless}

# Minimum Disk Space Check (500MB = 512000 KB)
StrCpy $0 "$PROGRAMFILES"
StrCpy $1 $0 3
GetDiskSpace $1 $2

# Compare free space ($2) against 512000 KB (500MB)
IntCmp $2 512000 space_ok space_low space_ok

space_low:
    MessageBox MB_OK|MB_ICONSTOP "Error: Free space on $1 is less than 500MB. Free space required: 500MB."
    Abort

space_ok:

# Kill any running HMS and PHP processes so DLL file locks are released before copying
DetailPrint "Stopping running HMS processes..."
nsExec::Exec 'taskkill /F /IM "HMS - Hospital Management.exe" /T'
nsExec::Exec 'taskkill /F /IM "php-x86_64-pc-windows-msvc.exe" /T'
nsExec::Exec 'taskkill /F /IM "php.exe" /T'
Sleep 1500
