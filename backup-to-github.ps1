# Automated GitHub Backup Script
# Run this script regularly to backup your code to GitHub

param(
    [string]$Message = "Auto-backup: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
)

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  GitHub Auto-Backup" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# Change to project directory
$projectDir = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $projectDir

# Check if Git repository
if (-not (Test-Path .git)) {
    Write-Host "[ERROR] Not a Git repository!" -ForegroundColor Red
    Write-Host "Run 'git init' first." -ForegroundColor Yellow
    exit 1
}

Write-Host "[1/4] Checking Git status..." -ForegroundColor Yellow
$status = git status --porcelain

if ([string]::IsNullOrWhiteSpace($status)) {
    Write-Host "  [OK] No changes to commit" -ForegroundColor Green
    Write-Host "  [INFO] Creating empty commit for backup timestamp" -ForegroundColor Cyan
    
    # Create empty commit to mark backup timestamp
    git commit --allow-empty -m "$Message"
} else {
    Write-Host "  [INFO] Changes detected, adding files..." -ForegroundColor Yellow
    
    # Add all changes
    git add .
    
    Write-Host "[2/4] Creating commit..." -ForegroundColor Yellow
    git commit -m $Message
}

Write-Host "  [OK] Commit created" -ForegroundColor Green
Write-Host ""

Write-Host "[3/4] Pushing to GitHub..." -ForegroundColor Yellow
git push origin main

if ($LASTEXITCODE -ne 0) {
    Write-Host "  [FAIL] Push failed!" -ForegroundColor Red
    Write-Host "  [INFO] Check your Git credentials or network connection" -ForegroundColor Yellow
    exit 1
}

Write-Host "  [OK] Successfully pushed to GitHub" -ForegroundColor Green
Write-Host ""

Write-Host "[4/4] Checking remote status..." -ForegroundColor Yellow
$lastCommit = git log -1 --pretty=format:"%h - %s (%cr)"
Write-Host "  Latest commit: $lastCommit" -ForegroundColor Cyan
Write-Host ""

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  ✅ BACKUP COMPLETE!" -ForegroundColor Green
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Repository: https://github.com/AdrianPeterSchmitt/tierdoku_2025" -ForegroundColor Cyan
Write-Host ""

