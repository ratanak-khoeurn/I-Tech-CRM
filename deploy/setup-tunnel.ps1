# One-time Cloudflare tunnel setup for the CRM.
#
#   powershell -ExecutionPolicy Bypass -File deploy\setup-tunnel.ps1 -Hostname crm.example.com
#
# Needs the domain behind Cloudflare's free DNS on the account you are about to sign in to.
# Nothing here touches the CRM code or the database: it creates a tunnel (outbound only, no
# router port forwarding) and one DNS record pointing at it.

param(
    [Parameter(Mandatory = $true)]
    [string]$Hostname,
    [string]$TunnelName = 'crm-office',
    [string]$Origin = 'http://127.0.0.1:8081'
)

$ErrorActionPreference = 'Stop'

$cf = Join-Path $env:LOCALAPPDATA 'Microsoft\WinGet\Links\cloudflared.exe'
if (-not (Test-Path $cf)) { throw "cloudflared not found at $cf" }

$cfDir = Join-Path $env:USERPROFILE '.cloudflared'
New-Item -ItemType Directory -Force -Path $cfDir | Out-Null

Write-Host "`n[1/4] Signing in to Cloudflare (a browser window opens)..."
& $cf tunnel login
if ($LASTEXITCODE -ne 0) { throw "cloudflared login failed" }

Write-Host "`n[2/4] Creating tunnel '$TunnelName'..."
& $cf tunnel list --output json | Out-Null
$existing = (& $cf tunnel list --output json) -join '' | ConvertFrom-Json
if (-not ($existing | Where-Object { $_.name -eq $TunnelName })) {
    & $cf tunnel create $TunnelName
    if ($LASTEXITCODE -ne 0) { throw "cloudflared tunnel create failed" }
} else {
    Write-Host "Tunnel '$TunnelName' already exists, reusing it."
}

$tunnel = ((& $cf tunnel list --output json) -join '') | ConvertFrom-Json |
    Where-Object { $_.name -eq $TunnelName } | Select-Object -First 1
$credentials = Join-Path $cfDir "$($tunnel.id).json"
if (-not (Test-Path $credentials)) { throw "credentials file missing: $credentials" }

Write-Host "`n[3/4] Writing $cfDir\config.yml -> $Origin"
$config = @"
tunnel: $TunnelName
credentials-file: $credentials

ingress:
  - hostname: $Hostname
    service: $Origin
  - service: http_status:404

log: $(Join-Path $cfDir 'crm-tunnel.log')
loglevel: info
"@
Set-Content -Path (Join-Path $cfDir 'config.yml') -Value $config -Encoding ascii

Write-Host "`n[4/4] Adding DNS record $Hostname..."
& $cf tunnel route dns $TunnelName $Hostname
if ($LASTEXITCODE -ne 0) {
    Write-Warning "Could not add the DNS record. Check that $Hostname's domain is using Cloudflare nameservers on the account you just signed in to, then rerun only: $cf tunnel route dns $TunnelName $Hostname"
}

Write-Host "`nDone. Start the tunnel with deploy\start-tunnel.bat (the CRM is reachable only through it)."
