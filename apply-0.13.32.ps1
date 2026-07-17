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
    throw 'VERSION is missing. Re-extract the complete 0.13.32 patch with file replacement enabled.'
}

$cmsVersion = (Get-Content 'VERSION' -Raw).Trim()
if ($cmsVersion -ne '0.13.32') {
    throw "Unexpected patch version: $cmsVersion"
}

$requiredFiles = @(
    'app\Http\Controllers\Admin\DashboardController.php',
    'app\Http\Controllers\Admin\SettingsController.php',
    'app\Http\Controllers\ServerMonitorStatusController.php',
    'app\Livewire\Admin\GameServerManager.php',
    'app\Services\Servers\ServerStatusOverview.php',
    'app\Services\Servers\ServerStatusPayload.php',
    'app\Services\SiteSettings.php',
    'database\migrations\2026_07_17_000300_add_game_server_maintenance_fields.php',
    'resources\views\admin\dashboard.blade.php',
    'resources\views\admin\settings\general.blade.php',
    'resources\views\livewire\admin\game-server-manager.blade.php',
    'routes\web.php',
    'themes\default\views\home.blade.php',
    'tests\Feature\ServerMonitoringTest.php',
    'tests\Feature\Admin\AdminSettingsManagementTest.php',
    'tests\Feature\Admin\ReactiveServerManagementTest.php',
    'tests\Feature\UpgradeGameServerMigrationTest.php',
    'update.ps1'
)

foreach ($requiredFile in $requiredFiles) {
    if (-not (Test-Path $requiredFile -PathType Leaf)) {
        throw "Patch file is missing: $requiredFile. Re-extract the complete 0.13.32 patch with file replacement enabled."
    }
}

Write-Host "L2Forge CMS $cmsVersion update"
Write-Host 'Adding GameServer maintenance mode and public online visibility settings.'
Write-Host 'Applying the SettingsController Pint formatting fix.'
Write-Host ''

Get-ChildItem -Path $PSScriptRoot -Filter 'apply-*.ps1' -File -ErrorAction SilentlyContinue |
    Where-Object { $_.Name -ne 'apply-0.13.32.ps1' } |
    Remove-Item -Force -ErrorAction SilentlyContinue

& "$PSScriptRoot\update.ps1" -SkipTests:$SkipTests

Write-Host ''
Write-Host "L2Forge CMS $cmsVersion is ready." -ForegroundColor Green
Write-Host 'Maintenance mode and global public online visibility are ready.'
Write-Host 'Developer quality gate: .\quality.ps1'
