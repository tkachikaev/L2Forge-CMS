param(
    [switch]$SkipTests
)

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

if (-not (Test-Path 'artisan')) {
    throw 'Run this script from the L2Forge CMS project root.'
}

if (-not (Test-Path '.env')) {
    throw '.env is missing. This patch must be applied to an installed L2Forge CMS project.'
}

if (-not (Test-Path 'VERSION')) {
    throw 'VERSION is missing. Re-extract the complete 0.18.0 patch with file replacement enabled.'
}

$cmsVersion = (Get-Content 'VERSION' -Raw).Trim()
if ($cmsVersion -ne '0.18.0') {
    throw "Unexpected patch version: $cmsVersion"
}

$requiredFiles = @(
    'app\Http\Controllers\Account\GameAccountController.php',
    'public\assets\account\css\app.css',
    'public\assets\account\js\navigation.js',
    'resources\views\account\layouts\app.blade.php',
    'resources\views\account\partials\navigation.blade.php',
    'resources\views\account\game-accounts\index.blade.php',
    'routes\web.php',
    'tests\Feature\Account\AccountNavigationTest.php',
    'tests\browser\specs\player-character-directory.spec.mjs',
    'CHANGELOG.md',
    'README.md',
    'VERSION',
    'docs\PLAYER_ACCOUNT.md',
    'docs\SYSTEM.md',
    'update.ps1'
)
foreach ($requiredFile in $requiredFiles) {
    if (-not (Test-Path $requiredFile -PathType Leaf)) {
        throw "Patch file is missing: $requiredFile. Re-extract the complete 0.18.0 patch with file replacement enabled."
    }
}

Write-Host "L2Forge CMS $cmsVersion update"
Write-Host 'Enabling the persistent Livewire player account shell and SPA navigation.'
Write-Host ''

Get-ChildItem -Path $PSScriptRoot -Filter 'apply-*.ps1' -File -ErrorAction SilentlyContinue |
    Where-Object { $_.Name -ne 'apply-0.18.0.ps1' } |
    Remove-Item -Force -ErrorAction SilentlyContinue

& "$PSScriptRoot\update.ps1" -SkipTests:$SkipTests

Write-Host ''
Write-Host "L2Forge CMS $cmsVersion is ready." -ForegroundColor Green
Write-Host 'Developer quality gate: .\quality.ps1'
Write-Host 'Browser smoke tests: .\browser-quality.ps1'
