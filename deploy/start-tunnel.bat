@echo off
rem Runs the named Cloudflare tunnel for the CRM.
rem Config + credentials live in %USERPROFILE%\.cloudflared (created by deploy\setup-tunnel.ps1).
rem The tunnel dials out to Cloudflare, so no inbound port or firewall rule is needed.

set "CF=%LOCALAPPDATA%\Microsoft\WinGet\Links\cloudflared.exe"
if not exist "%CF%" set "CF=%LOCALAPPDATA%\Programs\Cloudflare\cloudflared.exe"
if not exist "%CF%" (
    echo cloudflared.exe not found. Reinstall with: winget install --id Cloudflare.cloudflared
    exit /b 1
)

if not exist "%USERPROFILE%\.cloudflared\config.yml" (
    echo No tunnel config yet. Run deploy\setup-tunnel.ps1 once first.
    exit /b 1
)

"%CF%" tunnel run --config "%USERPROFILE%\.cloudflared\config.yml"
