#requires -Version 5.1
[CmdletBinding()]
param()

$ErrorActionPreference = 'Stop'
Set-Location -LiteralPath $PSScriptRoot

if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    throw 'Composer was not found in PATH.'
}

if (-not (Test-Path -LiteralPath (Join-Path $PSScriptRoot 'vendor\autoload.php'))) {
    throw 'Dependencies are not installed. Run composer install first.'
}

# The regular quality gate is intentionally deterministic and offline.
# Dependency advisories are checked separately by .\security-audit.ps1.
$composerNetworkVariable = Get-Item Env:COMPOSER_DISABLE_NETWORK -ErrorAction SilentlyContinue
$hadComposerNetworkSetting = $null -ne $composerNetworkVariable
$previousComposerNetworkSetting = if ($hadComposerNetworkSetting) {
    [string] $composerNetworkVariable.Value
} else {
    $null
}

try {
    $env:COMPOSER_DISABLE_NETWORK = '1'

    & "$PSScriptRoot\tests\powershell\update-workflow.ps1"
    & "$PSScriptRoot\tests\powershell\composer-audit-policy.ps1"

    composer validate --strict --no-check-publish
    if ($LASTEXITCODE -ne 0) { throw "Composer validation failed with exit code $LASTEXITCODE." }

    composer quality
    if ($LASTEXITCODE -ne 0) { throw "Quality checks failed with exit code $LASTEXITCODE." }

    php artisan route:clear
    if ($LASTEXITCODE -ne 0) { throw "Route cache cleanup failed with exit code $LASTEXITCODE." }

    php artisan route:cache
    if ($LASTEXITCODE -ne 0) { throw "Route cache build failed with exit code $LASTEXITCODE." }

    php artisan route:clear
    if ($LASTEXITCODE -ne 0) { throw "Route cache cleanup failed with exit code $LASTEXITCODE." }

    Write-Host 'Offline quality checks completed successfully: PowerShell updater, audit policy, Composer validation, Pint, PHPStan, PHPUnit and route cache.' -ForegroundColor Green
    Write-Host 'Run .\security-audit.ps1 separately when internet access is available.' -ForegroundColor DarkGray
} finally {
    if ($hadComposerNetworkSetting) {
        $env:COMPOSER_DISABLE_NETWORK = $previousComposerNetworkSetting
    } else {
        Remove-Item Env:COMPOSER_DISABLE_NETWORK -ErrorAction SilentlyContinue
    }
}
