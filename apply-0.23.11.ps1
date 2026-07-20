param(
    [switch]$SkipTests
)

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

$fromVersion = '0.23.10'
$toVersion = '0.23.11'

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
    '.github\workflows\quality.yml',
    'CHANGELOG.md',
    'README.md',
    'VERSION',
    'update.ps1',
    'quality.ps1',
    'security-audit.ps1',
    'scripts\composer-audit-support.ps1',
    'tests\powershell\composer-audit-policy.ps1',
    'browser-quality.ps1',
    'browser-setup.ps1',
    'doctor.ps1',
    'config\cms.php',
    'routes\public.php',
    'routes\admin.php',
    'app\Auth\AdminAccessPolicy.php',
    'app\Http\Controllers\Admin\DashboardController.php',
    'app\Providers\AppServiceProvider.php',
    'app\Services\SystemInformation.php',
    'app\Services\Security\EncryptionHealth.php',
    'app\Console\Commands\EncryptionHealthCommand.php',
    'resources\views\admin\settings\system.blade.php',
    'lang\ru.json',
    'lang\en.json',
    'tests\Feature\Auth\PublicUserAuthenticationTest.php',
    'tests\Feature\Admin\EncryptionHealthTest.php',
    'tests\Feature\Admin\AdminPanelTest.php',
    'tests\Feature\Admin\AdminPathSettingsTest.php',
    'tests\Feature\Admin\SystemSettingsTest.php',
    'tests\Feature\CoreStabilizationTest.php',
    'tests\Feature\ReleaseMetadataTest.php',
    'tests\browser\run.mjs',
    'tests\browser\specs\admin-navigation.spec.mjs',
    'tests\browser\specs\player-character-directory.spec.mjs',
    'tests\browser\support\navigation.mjs',
    'tests\browser\support\navigation.test.mjs',
    'tests\powershell\update-workflow.ps1',
    'docs\ADMIN_PANEL.md',
    'docs\MAIL.md',
    'docs\PRODUCTION.md',
    'docs\SECURITY.md',
    'docs\SYSTEM.md',
    'docs\ROADMAP.md',
    'docs\development\QUALITY.md',
    'docs\development\TESTING.md',
    'app\Livewire\Admin\LoginServerManager.php',
    'public\assets\admin\css\app.css',
    'resources\views\livewire\admin\login-server-manager.blade.php',
    'tests\Feature\Admin\ReactiveServerManagementTest.php'
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
Write-Host 'Offline developer quality gate: .\quality.ps1'
Write-Host 'One-time browser setup: .\browser-setup.ps1'
Write-Host 'Offline browser smoke tests: .\browser-quality.ps1'
Write-Host 'Online dependency audit: .\security-audit.ps1'
