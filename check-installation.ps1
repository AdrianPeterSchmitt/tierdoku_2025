# Installation Check Script
Write-Host "Checking installations..." -ForegroundColor Cyan
Write-Host ""

# Add Scoop shims to PATH
$env:PATH += ";$env:USERPROFILE\scoop\shims"

$missing = @()
$allOk = $true

# Check PHP
Write-Host "Checking PHP..." -ForegroundColor Yellow
try {
    $phpVersion = & php -v 2>&1 | Select-Object -First 1
    if ($phpVersion -and $phpVersion.Length -gt 0) {
        Write-Host "[OK] PHP installed: $phpVersion" -ForegroundColor Green
    } else {
        throw "PHP not found"
    }
} catch {
    Write-Host "[FAIL] PHP NOT installed" -ForegroundColor Red
    Write-Host "   Download: https://windows.php.net/download/" -ForegroundColor White
    $missing += "PHP"
    $allOk = $false
}

Write-Host ""

# Check Composer
Write-Host "Checking Composer..." -ForegroundColor Yellow
try {
    $composerVersion = composer -V 2>&1
    if ($LASTEXITCODE -eq 0 -and $composerVersion) {
        $versionLine = ($composerVersion -split "`n")[0]
        Write-Host "[OK] Composer installed: $versionLine" -ForegroundColor Green
    } else {
        throw "Composer not found"
    }
} catch {
    Write-Host "[FAIL] Composer NOT installed" -ForegroundColor Red
    Write-Host "   Download: https://getcomposer.org/download/" -ForegroundColor White
    $missing += "Composer"
    $allOk = $false
}

Write-Host ""

# Check Node.js
Write-Host "Checking Node.js..." -ForegroundColor Yellow
try {
    $nodeVersion = node -v 2>&1
    if ($LASTEXITCODE -eq 0 -and $nodeVersion) {
        Write-Host "[OK] Node.js installed: $nodeVersion" -ForegroundColor Green
    } else {
        throw "Node.js not found"
    }
} catch {
    Write-Host "[FAIL] Node.js NOT installed" -ForegroundColor Red
    Write-Host "   Download: https://nodejs.org/" -ForegroundColor White
    $missing += "Node.js"
    $allOk = $false
}

Write-Host ""

# Check npm
Write-Host "Checking npm..." -ForegroundColor Yellow
try {
    $npmVersion = npm -v 2>&1
    if ($LASTEXITCODE -eq 0 -and $npmVersion) {
        Write-Host "[OK] npm installed: v$npmVersion" -ForegroundColor Green
    } else {
        throw "npm not found"
    }
} catch {
    Write-Host "[FAIL] npm NOT installed" -ForegroundColor Red
    Write-Host "   Comes with Node.js - reinstall Node.js" -ForegroundColor White
    $missing += "npm"
    $allOk = $false
}

Write-Host ""
Write-Host "================================" -ForegroundColor Cyan

if ($allOk) {
    Write-Host ""
    Write-Host "SUCCESS: All programs installed!" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Yellow
    Write-Host "1. composer install" -ForegroundColor White
    Write-Host "2. npm install" -ForegroundColor White
    Write-Host "3. npm run build" -ForegroundColor White
    Write-Host "4. php migrate.php" -ForegroundColor White
    Write-Host "5. php -S localhost:8000 -t public" -ForegroundColor White
} else {
    Write-Host ""
    Write-Host "WARNING: Missing programs: $($missing -join ', ')" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Please read: INSTALLATION.md" -ForegroundColor Cyan
}

Write-Host ""

