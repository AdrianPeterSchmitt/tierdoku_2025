# Comprehensive Test Runner
# Runs all QA checks: Formatting, Static Analysis, Unit Tests, Feature Tests

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  PHP WebApp - QA Pipeline" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

$errors = 0

# Check if required tools are available
Write-Host "[1/5] Checking dependencies..." -ForegroundColor Yellow

$requiredTools = @('php', 'composer', 'vendor/bin/pint', 'vendor/bin/phpstan', 'vendor/bin/phpunit')
foreach ($tool in $requiredTools) {
    if ($tool -eq 'php' -or $tool -eq 'composer') {
        $result = Get-Command $tool -ErrorAction SilentlyContinue
    } else {
        $result = Test-Path $tool
    }
    
    if ($result) {
        Write-Host "  [OK] $tool" -ForegroundColor Green
    } else {
        Write-Host "  [FAIL] $tool not found" -ForegroundColor Red
        $errors++
    }
}

if ($errors -gt 0) {
    Write-Host ""
    Write-Host "[ERROR] Missing dependencies. Please run: composer install" -ForegroundColor Red
    exit 1
}

Write-Host ""

# Code Formatting
Write-Host "[2/5] Running Code Formatter (Laravel Pint)..." -ForegroundColor Yellow
$startTime = Get-Date

& vendor/bin/pint

$endTime = Get-Date
$duration = ($endTime - $startTime).TotalSeconds
Write-Host "  [OK] Code formatting completed in $([math]::Round($duration, 2))s" -ForegroundColor Green
Write-Host ""

# Static Analysis
Write-Host "[3/5] Running Static Analysis (PHPStan)..." -ForegroundColor Yellow
$startTime = Get-Date

& vendor/bin/phpstan analyse app --no-progress

if ($LASTEXITCODE -ne 0) {
    Write-Host "  [FAIL] PHPStan found errors" -ForegroundColor Red
    $errors++
} else {
    $endTime = Get-Date
    $duration = ($endTime - $startTime).TotalSeconds
    Write-Host "  [OK] Static analysis completed in $([math]::Round($duration, 2))s" -ForegroundColor Green
}
Write-Host ""

# Unit Tests
Write-Host "[4/5] Running Unit Tests (PHPUnit)..." -ForegroundColor Yellow
$startTime = Get-Date

& vendor/bin/phpunit tests/Unit --no-coverage

if ($LASTEXITCODE -ne 0) {
    Write-Host "  [FAIL] Unit tests failed" -ForegroundColor Red
    $errors++
} else {
    $endTime = Get-Date
    $duration = ($endTime - $startTime).TotalSeconds
    Write-Host "  [OK] Unit tests completed in $([math]::Round($duration, 2))s" -ForegroundColor Green
}
Write-Host ""

# Feature/E2E Tests
Write-Host "[5/5] Running Feature/E2E Tests (PHPUnit)..." -ForegroundColor Yellow
$startTime = Get-Date

& vendor/bin/phpunit tests/Feature --no-coverage

if ($LASTEXITCODE -ne 0) {
    Write-Host "  [FAIL] Feature tests failed" -ForegroundColor Red
    $errors++
} else {
    $endTime = Get-Date
    $duration = ($endTime - $startTime).TotalSeconds
    Write-Host "  [OK] Feature tests completed in $([math]::Round($duration, 2))s" -ForegroundColor Green
}
Write-Host ""

# Summary
Write-Host "============================================" -ForegroundColor Cyan
if ($errors -eq 0) {
    Write-Host "  ALL TESTS PASSED!" -ForegroundColor Green
    Write-Host "============================================" -ForegroundColor Cyan
    exit 0
} else {
    Write-Host "  FAILED: $errors error(s) found" -ForegroundColor Red
    Write-Host "============================================" -ForegroundColor Cyan
    exit 1
}


