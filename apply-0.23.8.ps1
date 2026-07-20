param(
    [switch]$SkipTests
)

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

$fromVersion = '0.23.7'
$toVersion = '0.23.8'

if (-not (Test-Path 'artisan')) {
    throw 'Run this script from the KaevCMS project root.'
}

if (-not (Test-Path '.env')) {
    throw '.env is missing. This patch must be applied to an installed KaevCMS project.'
}

if (-not (Test-Path 'VERSION')) {
    throw "VERSION is missing. Re-extract the complete $toVersion patch with file replacement enabled."
}

$cmsVersion = (Get-Content 'VERSION' -Raw).Trim()
if ($cmsVersion -ne $toVersion) {
    throw "Unexpected patch version: $cmsVersion"
}

$requiredFiles = @(
    'CHANGELOG.md',
    'README.md',
    'VERSION',
    'update.ps1',
    'phpunit.xml',
    'scripts\release-update-support.ps1',
    'app\Console\Commands\ReleaseVersionCommand.php',
    'app\Console\Commands\MaintenanceStatusCommand.php',
    'app\Services\Releases\InstalledVersion.php',
    'app\Jobs\Mail\SendUserMailNotification.php',
    'tests\Feature\ReleaseInstalledVersionTest.php',
    'tests\Feature\ReleaseMetadataTest.php',
    'tests\powershell\update-workflow.ps1'
)
foreach ($requiredFile in $requiredFiles) {
    if (-not (Test-Path $requiredFile -PathType Leaf)) {
        throw "Patch file is missing: $requiredFile. Re-extract the complete $toVersion patch with file replacement enabled."
    }
}

Write-Host "KaevCMS $fromVersion -> $toVersion update"
Write-Host 'The source release will be verified before migrations or cleanup.'
Write-Host ''

& "$PSScriptRoot\update.ps1" -SkipTests:$SkipTests

Write-Host ''
Write-Host "KaevCMS $toVersion is ready." -ForegroundColor Green
Write-Host 'Developer quality gate: .\quality.ps1'
Write-Host 'Browser smoke tests: .\browser-quality.ps1'
