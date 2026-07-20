#requires -Version 5.1
[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot

if (-not (Get-Command npm -ErrorAction SilentlyContinue)) {
    throw 'Node.js and npm are required for browser test setup.'
}

npm ci
if ($LASTEXITCODE -ne 0) { throw "npm ci failed with exit code $LASTEXITCODE." }

npx playwright install chromium
if ($LASTEXITCODE -ne 0) { throw "Playwright browser installation failed with exit code $LASTEXITCODE." }

Write-Host 'Browser test dependencies and Chromium were installed successfully.' -ForegroundColor Green
Write-Host 'Run .\browser-quality.ps1 to execute the offline browser smoke tests.' -ForegroundColor DarkGray
