param(
    [switch]$SkipTests
)

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

if (-not (Test-Path 'artisan')) {
    throw 'Run this script from the KaevCMS project root.'
}

if (-not (Test-Path '.env')) {
    throw '.env is missing. This patch must be applied to an installed KaevCMS project.'
}

if (-not (Test-Path 'VERSION')) {
    throw 'VERSION is missing. Re-extract the complete 0.20.2 patch with file replacement enabled.'
}

$cmsVersion = (Get-Content 'VERSION' -Raw).Trim()
if ($cmsVersion -ne '0.20.2') {
    throw "Unexpected patch version: $cmsVersion"
}

$requiredFiles = @(
    'app\Services\GameAccounts\AccountCharacterDirectory.php',
    'tests\Fakes\FakeGameAccountGateway.php',
    'tests\Feature\Account\GameAccountCabinetTest.php',
    'docs\PLAYER_ACCOUNT.md',
    'CHANGELOG.md',
    'README.md',
    'VERSION',
    'update.ps1'
)

foreach ($requiredFile in $requiredFiles) {
    if (-not (Test-Path $requiredFile -PathType Leaf)) {
        throw "Patch file is missing: $requiredFile. Re-extract the complete 0.20.2 patch with file replacement enabled."
    }
}

Write-Host "KaevCMS $cmsVersion update"
Write-Host 'Adding cache and failure cooldown protection to the player character directory.'
Write-Host ''

Get-ChildItem -Path $PSScriptRoot -Filter 'apply-*.ps1' -File -ErrorAction SilentlyContinue |
    Where-Object { $_.Name -ne 'apply-0.20.2.ps1' } |
    Remove-Item -Force -ErrorAction SilentlyContinue

& "$PSScriptRoot\update.ps1" -SkipTests:$SkipTests

Write-Host ''
Write-Host "KaevCMS $cmsVersion is ready." -ForegroundColor Green
Write-Host 'Developer quality gate: .\quality.ps1'
Write-Host 'Browser smoke tests: .\browser-quality.ps1'
