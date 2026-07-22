param(
    [string]$PublicDirectoryName = 'public_html',
    [string]$CoreDirectoryName = 'kaevcms-core',
    [string]$OutputDirectory = 'dist'
)

$ErrorActionPreference = 'Stop'
$ProjectRoot = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\..'))
Set-Location -LiteralPath $ProjectRoot

$builder = Join-Path $ProjectRoot 'deployment\hosting\build-shared-hosting-package.php'
if (-not (Test-Path -LiteralPath $builder -PathType Leaf)) {
    throw 'Shared-hosting package builder is missing.'
}
if (-not (Get-Command php -ErrorAction SilentlyContinue)) {
    throw 'PHP CLI was not found in PATH.'
}
if (-not (Test-Path -LiteralPath (Join-Path $ProjectRoot 'vendor\autoload.php') -PathType Leaf)) {
    throw 'vendor\autoload.php is missing. Run setup.ps1 or Composer first.'
}

php $builder `
    "--public-dir=$PublicDirectoryName" `
    "--core-dir=$CoreDirectoryName" `
    "--output=$OutputDirectory" `
    --no-zip

if ($LASTEXITCODE -ne 0) {
    throw "Shared-hosting package build failed with exit code $LASTEXITCODE."
}

$version = (Get-Content -LiteralPath (Join-Path $ProjectRoot 'VERSION') -Raw).Trim()
$outputRoot = if ([System.IO.Path]::IsPathRooted($OutputDirectory)) {
    [System.IO.Path]::GetFullPath($OutputDirectory)
} else {
    [System.IO.Path]::GetFullPath((Join-Path $ProjectRoot $OutputDirectory))
}
$packageDirectory = Join-Path $outputRoot "KaevCMS-$version-shared-hosting"
$zipPath = Join-Path $outputRoot "KaevCMS-$version-shared-hosting.zip"
$shaPath = "$zipPath.sha256"

if (-not (Test-Path -LiteralPath $packageDirectory -PathType Container)) {
    throw "Prepared package directory was not found: $packageDirectory"
}

Add-Type -AssemblyName System.IO.Compression.FileSystem
if (Test-Path -LiteralPath $zipPath) {
    Remove-Item -LiteralPath $zipPath -Force
}
[System.IO.Compression.ZipFile]::CreateFromDirectory(
    $packageDirectory,
    $zipPath,
    [System.IO.Compression.CompressionLevel]::Optimal,
    $false
)

$hash = (Get-FileHash -LiteralPath $zipPath -Algorithm SHA256).Hash.ToLowerInvariant()
"$hash  $([System.IO.Path]::GetFileName($zipPath))" | Set-Content -LiteralPath $shaPath -Encoding ASCII

Write-Host ''
Write-Host "Shared-hosting archive: $zipPath" -ForegroundColor Green
Write-Host "SHA256: $shaPath"
Write-Host "Public directory in archive: $PublicDirectoryName"
Write-Host "Private core directory in archive: $CoreDirectoryName"
