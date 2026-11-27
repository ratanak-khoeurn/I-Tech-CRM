# Registers (or removes) the Windows scheduled tasks that keep the CRM serving after a reboot.
#
#   powershell -ExecutionPolicy Bypass -File deploy\register-autostart.ps1
#   powershell -ExecutionPolicy Bypass -File deploy\register-autostart.ps1 -Unregister
#
# Tasks run only while this Windows account is logged in, which is how XAMPP's Apache and MySQL
# are already started on this PC. Reboot -> log in -> everything is up again.

param(
    [switch]$Unregister,
    [string]$BackupTime = '22:30'
)

$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot

$tasks = [ordered]@{
    'CRM Apache'      = @{ tr = (Join-Path $PSScriptRoot 'start-apache.bat'); sc = 'onlogon' }
    'CRM Email Queue' = @{ tr = (Join-Path $root 'queue-worker.bat');         sc = 'onlogon' }
    'CRM Tunnel'      = @{ tr = (Join-Path $PSScriptRoot 'start-tunnel.bat'); sc = 'onlogon' }
    'CRM Backup'      = @{ tr = (Join-Path $PSScriptRoot 'backup-crm.bat');   sc = 'daily'; st = $BackupTime }
}

if ($Unregister) {
    foreach ($name in $tasks.Keys) {
        & schtasks.exe /delete /tn $name /f 2>$null
        if ($LASTEXITCODE -eq 0) { Write-Host "Removed $name" } else { Write-Host "$name was not registered" }
    }
    return
}

foreach ($name in $tasks.Keys) {
    $t = $tasks[$name]

    if ($name -eq 'CRM Tunnel' -and -not (Test-Path (Join-Path $env:USERPROFILE '.cloudflared\config.yml'))) {
        Write-Warning "Skipping $name - run deploy\setup-tunnel.ps1 first, then rerun this script."
        continue
    }

    $arg = '"' + $t.tr + '"'
    $cmd = @('/create', '/tn', $name, '/tr', $arg, '/sc', $t.sc, '/rl', 'limited', '/f')
    if ($t.sc -eq 'daily') { $cmd += @('/st', $t.st) }

    & schtasks.exe @cmd
    if ($LASTEXITCODE -ne 0) { Write-Error "Failed to register $name" } else { Write-Host "Registered $name -> $($t.tr)" }
}

Write-Host "`nVerify any time with: schtasks /query /fo LIST | findstr /I \"CRM \""
