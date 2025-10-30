# Testing Dokumentation

## Überblick

Dieses Projekt verwendet mehrere QA-Tools für Code-Qualität und Zuverlässigkeit:

- **Laravel Pint** - Code Formatting (PSR-12)
- **PHPStan** - Static Analysis (Level 4)
- **PHPUnit** - Unit & Feature Tests

## Test-Struktur

```
tests/
├── Unit/              # Unit Tests (isolierte Komponenten)
│   ├── ExampleTest.php
│   ├── RouterTest.php
│   └── ViewHelperTest.php
├── Feature/           # Feature/E2E Tests (komplette Features)
│   ├── HomePageTest.php
│   └── AboutPageTest.php
```

## Test ausführen

### Alle Tests ausführen

```powershell
# Windows PowerShell
.\run-tests.ps1
```

```bash
# Linux/Mac
vendor/bin/phpunit
```

### Spezifische Test-Gruppen

```bash
# Nur Unit Tests
vendor/bin/phpunit tests/Unit

# Nur Feature/E2E Tests
vendor/bin/phpunit tests/Feature

# Bestimmter Test
vendor/bin/phpunit tests/Unit/ExampleTest.php
```

### Mit Coverage

```bash
vendor/bin/phpunit --coverage-text
vendor/bin/phpunit --coverage-html coverage
```

## Einzelne Tools

### Code Formatting

```bash
# Code formatieren
vendor/bin/pint

# Nur prüfen (ohne Änderungen)
vendor/bin/pint --test
```

### Static Analysis

```bash
# PHPStan ausführen
vendor/bin/phpstan analyse app

# Mit höherem Level (strenger)
vendor/bin/phpstan analyse app --level=8

# Mit Baseline
vendor/bin/phpstan analyse app --generate-baseline
```

### Unit Tests

```bash
# Alle Unit Tests
vendor/bin/phpunit tests/Unit

# Mit Verbose Output
vendor/bin/phpunit tests/Unit --verbose

# Mit Filter
vendor/bin/phpunit --filter testExample
```

### Feature/E2E Tests

```bash
# Alle Feature Tests
vendor/bin/phpunit tests/Feature

# Einzelner Test
vendor/bin/phpunit tests/Feature/HomePageTest.php
```

## Composer Scripts

Verfügbare Scripts in `composer.json`:

```bash
# Alle Tests ausführen
composer test

# Code formatieren
composer format

# Static Analysis
composer analyse
```

## CI/CD Integration

### GitHub Actions

Ein Workflow ist in `.github/workflows/tests.yml` definiert.

Automatische Ausführung bei:
- Push auf `main` oder `develop`
- Pull Requests

Getestet mit:
- PHP 8.2, 8.3, 8.4
- Alle Test-Suites
- Code Coverage

### Lokale CI-Simulation

```powershell
# Windows
.\run-tests.ps1

# Linux/Mac
./run-tests.sh
```

## Test schreiben

### Unit Test Beispiel

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    public function testSomething(): void
    {
        $this->assertTrue(true);
    }
}
```

### Feature Test Beispiel

```php
<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    public function testPageRenders(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/my-page';
        
        ob_start();
        require __DIR__ . '/../../public/index.php';
        $output = ob_get_clean();
        
        $this->assertStringContainsString('Expected Content', $output);
    }
}
```

## Best Practices

### 1. Test-Namen
- Beschreibend und eindeutig
- `test` Prefix
- Kleingeschrieben

### 2. Test-Struktur
- **Arrange** - Setup
- **Act** - Execute
- **Assert** - Verify

### 3. Unabhängigkeit
- Jeder Test ist unabhängig
- Keine Reihenfolge-Abhängigkeit
- Clean State

### 4. Coverage
- Ziel: 80%+ Code Coverage
- Teste kritische Pfade
- Teste Edge Cases

### 5. Performance
- Unit Tests: < 100ms
- Feature Tests: < 1s
- Mock externe Dependencies

## Troubleshooting

### Tests laufen nicht

```bash
# Autoloader neu generieren
composer dump-autoload

# Tests mit Verbose
vendor/bin/phpunit --verbose
```

### PHPStan Fehler

```bash
# Baseline generieren (für Legacy-Code)
vendor/bin/phpstan analyse app --generate-baseline

# Ignore-Liste in phpstan.neon erweitern
```

### Coverage nicht korrekt

```bash
# Xdebug prüfen
php -m | grep xdebug

# PHP Version prüfen
php -v
```

## Weiterführende Links

- [PHPUnit Dokumentation](https://phpunit.de/documentation.html)
- [PHPStan Dokumentation](https://phpstan.org/user-guide/getting-started)
- [Laravel Pint Dokumentation](https://laravel.com/docs/pint)

