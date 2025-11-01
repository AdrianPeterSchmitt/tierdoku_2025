# Tierdokumentation - Tierkremations-Verwaltungssystem

Eine vollständige, moderne PHP-Webapplikation für die Verwaltung von Tierkremationen für Animea Tierkrematorium.

## 🚀 Hauptfunktionen

- **🔐 Benutzer & Rollen**: Admin, Manager, Mitarbeiter mit standortbasierten Berechtigungen
- **📊 Kremationsverwaltung**: Vollständiges CRUD mit Soft Deletes & Bulk Operations
- **📱 QR-Code-System**: QR-Code-Generierung & Mobile Scanner
- **📄 PDF-Labels**: Druckfertige Labels mit QR-Code
- **📈 Statistik-Dashboard**: Charts & Metriken mit Chart.js
- **🔍 Audit Logging**: Vollständiger Änderungshistorie
- **📤 Export**: CSV-Export für externe Tools
- **🎨 Modern UI**: Responsive Dark Theme mit TailwindCSS & Alpine.js

## 🛠️ Technologie-Stack

- **FastRoute** - High-Performance Routing
- **Illuminate/Eloquent** - ORM mit Migrations
- **TailwindCSS** - Utility-First CSS Framework
- **Alpine.js** - Leichtgewichtiges JavaScript
- **Chart.js** - Diagramme & Visualisierungen
- **Dompdf** - PDF-Generierung
- **Endroid QR Code** - QR-Code-Erstellung
- **Monolog** - PSR-3 Logging
- **Respect/Validation** - Input-Validierung
- **QA-Tools** - PHPStan (Level 7), PHPUnit, Laravel Pint

## 📋 Voraussetzungen

- **PHP** 8.2 oder höher
- **Composer** für Dependency-Management
- **Node.js** (nur für Development)
- **SQLite** oder **MySQL** Datenbank

## 🔧 Installation

### 1. Dependencies installieren

```bash
# Composer Dependencies
composer install

# Node.js Dependencies (für TailwindCSS)
npm install
```

### 2. Environment konfigurieren

Kopiere `.env.example` nach `.env` und passe die Konfiguration an:

```bash
copy .env.example .env
```

Bearbeite `.env` für lokale Entwicklung (SQLite):

```env
APP_NAME=Tierdokumentation
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=Europe/Berlin

DB_CONNECTION=sqlite
DB_DATABASE=./database/database.sqlite
```

**Zeitzone:** Die `APP_TIMEZONE` Variable bestimmt die Zeitzone für alle Datum/Zeit-Operationen. Standard ist `Europe/Berlin` (Deutschland). Weitere Beispiele:
- `America/New_York` - USA Ostküste
- `Asia/Tokyo` - Japan
- `UTC` - Koordinierte Weltzeit

### 3. Datenbank-Setup

```bash
# Migrationen ausführen
php migrate.php

# Seed-Daten einfügen (Standorte, Tierarten, Admin-User)
php seed.php tierdoku
```

### 4. Assets bauen

```bash
# Einmaliger Build
npm run build

# Oder im Watch-Modus (für Development)
npm run watch
```

## 🌐 Lokalen Server starten

```bash
php -S localhost:8000 -t public
```

Dann öffne: **http://localhost:8000**

### 🔑 Login

**Standard-Admin-Account:**
- **Benutzername**: `admin`
- **Passwort**: `admin123`

⚠️ **Wichtig**: Ändere das Admin-Passwort nach dem ersten Login!

## 📁 Projektstruktur

```
├── app/
│   └── Controllers/      # Controller-Klassen
├── config/
│   ├── container.php     # Dependency Injection Container
│   ├── database.php      # Datenbank-Konfiguration
│   └── routes.php        # Route-Definitionen
├── database/
│   ├── migrations/       # Migrations-Dateien
│   └── database.sqlite   # SQLite-Datenbank (lokal)
├── deploy/               # Deployment-Dokumentation
├── public/
│   ├── index.php         # Entry Point
│   ├── .htaccess         # Apache Rewrite Rules
│   ├── style.css         # TailwindCSS Input
│   └── dist/             # Kompilierte CSS-Dateien
├── resources/
│   └── views/            # Blade Templates
├── storage/
│   └── logs/             # Log-Dateien
└── tests/                # PHPUnit Tests
```

## 🛠️ QA-Tools

### Code formatieren

```bash
vendor/bin/pint
```

### Static Analysis

```bash
vendor/bin/phpstan analyse app
```

### Tests ausführen

```bash
vendor/bin/phpunit
```

## 📝 Best Practices

### Neue Route hinzufügen

Bearbeite `config/routes.php`:

```php
'GET' => [
    '/my-route' => [MyController::class, 'method'],
],
```

### Controller erstellen

Erstelle `app/Controllers/MyController.php`:

```php
<?php
namespace App\Controllers;

class MyController
{
    public function method(): string
    {
        return view('my-view', ['data' => 'value']);
    }
}
```

### Migration erstellen

Erstelle eine neue Datei in `database/migrations/`:

```php
<?php
return function ($schema) {
    $schema->create('table_name', function ($table) {
        $table->id();
        // Spalten...
    });
};
```

Dann ausführen: `php migrate.php`

### View erstellen

Erstelle `resources/views/my-view.php`:

```php
<?php
$layout = 'layouts/app';
$content = ob_start();
?>

<div class="container">
    <h1><?= $data ?></h1>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
```

## 🚢 Deployment

Siehe [deploy/README.md](deploy/README.md) für Deployment-Anleitung.

## 📚 Technologie-Stack

- **PHP 8.2+**
- **Composer** - Dependency Management
- **FastRoute** - Routing
- **Illuminate** - Database, Container, Events, View
- **TailwindCSS** - Styling
- **Monolog** - Logging
- **PHPStan** - Static Analysis
- **PHPUnit** - Testing
- **Laravel Pint** - Code Formatting

## 📄 Lizenz

Proprietär - Alle Rechte vorbehalten

