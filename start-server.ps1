param(
    [int]$Port = 8000,
    [string]$BindAddress = "127.0.0.1"
)

$ErrorActionPreference = "Stop"

if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    throw "PHP was not found in PATH. Install PHP and reopen PowerShell before running this script."
}

Push-Location $PSScriptRoot
try {
    Write-Host "Starting Simplr at http://${BindAddress}:${Port}"
    Write-Host "Press Ctrl+C to stop the server."
    & php -S "${BindAddress}:${Port}" -t $PSScriptRoot (Join-Path $PSScriptRoot "router.php")
}
finally {
    Pop-Location
}