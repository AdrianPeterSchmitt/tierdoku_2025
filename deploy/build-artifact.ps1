$ErrorActionPreference = 'Stop'

Write-Host "==> Baue Release-Artefakt fuer Shared Hosting" -ForegroundColor Cyan

$root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $root

$buildDir = Join-Path $root 'build'
if (!(Test-Path $buildDir)) { New-Item -ItemType Directory -Path $buildDir | Out-Null }

$zipPath = Join-Path $buildDir 'tierdokumentation_release.zip'
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }

# Composer / Assets lokal sicherstellen (falls vergessen)
if (!(Test-Path (Join-Path $root 'vendor'))) {
  Write-Host "composer install ..." -ForegroundColor Yellow
  composer install --no-dev --prefer-dist --no-progress
}

if (!(Test-Path (Join-Path $root 'public\dist\style.css'))) {
  if (Test-Path (Join-Path $root 'package.json')) {
    Write-Host "npm install & build ..." -ForegroundColor Yellow
    npm install
    npm run build
  }
}

# Dateien zusammenstellen
$include = @(
  'app',
  'config',
  'database',
  'public',
  'resources',
  'storage',
  'vendor',
  'composer.json',
  'composer.lock',
  'phpstan.neon',
  'pint.json',
  '.env.example'
)

$temp = Join-Path $buildDir 'staging'
if (Test-Path $temp) { Remove-Item $temp -Recurse -Force }
New-Item -ItemType Directory -Path $temp | Out-Null

foreach ($item in $include) {
  Copy-Item -Path (Join-Path $root $item) -Destination (Join-Path $temp $item) -Recurse -Force
}

# Keine Entwicklungsordner
Remove-Item (Join-Path $temp 'node_modules') -Recurse -Force -ErrorAction SilentlyContinue

Write-Host "Zip erstelle: $zipPath" -ForegroundColor Green
Add-Type -AssemblyName 'System.IO.Compression.FileSystem'
[System.IO.Compression.ZipFile]::CreateFromDirectory($temp, $zipPath)

Write-Host "Fertig: $zipPath" -ForegroundColor Green


