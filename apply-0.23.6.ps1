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
    throw 'VERSION is missing. Re-extract the complete 0.23.6 patch with file replacement enabled.'
}

$cmsVersion = (Get-Content 'VERSION' -Raw).Trim()
if ($cmsVersion -ne '0.23.6') {
    throw "Unexpected patch version: $cmsVersion"
}

$requiredFiles = @(
    'CHANGELOG.md',
    'README.md',
    'VERSION',
    'update.ps1',
    'app\Http\Middleware\TrustProxies.php',
    'bootstrap\app.php',
    'tests\Feature\Infrastructure\TrustedProxyTest.php',
    'tests\Unit\ApplicationBootstrapTest.php'
)
foreach ($requiredFile in $requiredFiles) {
    if (-not (Test-Path $requiredFile -PathType Leaf)) {
        throw "Patch file is missing: $requiredFile. Re-extract the complete 0.23.6 patch with file replacement enabled."
    }
}

$currentApplyScript = "apply-$cmsVersion.ps1"
$obsoleteApplyScripts = Get-ChildItem -LiteralPath $PSScriptRoot -Filter 'apply-*.ps1' -File -ErrorAction Stop |
    Where-Object { $_.Name -ne 'apply-0.23.6.ps1' }

foreach ($obsoleteApplyScript in $obsoleteApplyScripts) {
    Remove-Item -LiteralPath $obsoleteApplyScript.FullName -Force -ErrorAction Stop

    if (Test-Path -LiteralPath $obsoleteApplyScript.FullName) {
        throw "Unable to remove obsolete apply script: $($obsoleteApplyScript.Name)"
    }
}

Write-Host "KaevCMS $cmsVersion update"
Write-Host 'Cleaning obsolete release artifacts before running the update checks.'
Write-Host ''

& "$PSScriptRoot\update.ps1" -SkipTests:$SkipTests

Write-Host ''
Write-Host "KaevCMS $cmsVersion is ready." -ForegroundColor Green
Write-Host 'Developer quality gate: .\quality.ps1'
Write-Host 'Browser smoke tests: .\browser-quality.ps1'
