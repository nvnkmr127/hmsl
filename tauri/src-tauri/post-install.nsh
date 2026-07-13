# Create Firewall Exception for local port 8000
nsExec::ExecToLog 'netsh advfirewall firewall add rule name="HMS Local Server" dir=in action=allow protocol=TCP localport=8000 remoteip=127.0.0.1'

# Create scheduled task to run HMS on login (30s delay)
nsExec::ExecToLog 'schtasks /create /tn "HMS Hospital Management" /tr "\"$INSTDIR\HMS - Hospital Management.exe\"" /sc onlogon /delay 0000:30 /f'
