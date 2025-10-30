# Cursor 2.0 Metadata - Tierdokumentation

## Projekt-Ziel

**Moderne PHP WebApp** für Shared Hosting mit hoher Code-Qualität und minimalen Dependencies.

## Architektur

### MVC-Pattern

```
┌─────────────────────────────────────┐
│  public/index.php (Entry Point)    │  ← Apache leitet alle Anfragen hier hin
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  config/routes.php (Routing)       │  ← FastRoute
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  app/Controllers/*.php (Logic)      │  ← Controllers
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  resources/views/*.php (Templates)  │  ← Views
└─────────────────────────────────────┘
```

### Wichtige Verzeichnisse

- **`app/`** - Anwendungslogik (Controllers, Models, Services)
- **`public/`** - Öffentlicher Webroot (nur hier sind Dateien über HTTP erreichbar)
- **`config/`** - Konfigurationsdateien (Database, Routes, Container)
- **`resources/views/`** - View-Templates (Blade-kompatibel)
- **`database/migrations/`** - Datenbank-Migrations
- **`storage/logs/`** - Log-Dateien
- **`tests/`** - PHPUnit Tests

### Routing-System

**FastRoute** leitet HTTP-Requests zu Controller-Methoden weiter.

**Route-Definition:** `config/routes.php`
```php
'GET' => [
    '/my-route' => [MyController::class, 'method'],
],
```

**URL-Parameter:**
```php
'/users/{id}' => [UserController::class, 'show'],
// → $vars['id'] enthält den Wert
```

### Datenbank-Layer

**Illuminate Database** (Capsule) mit Eloquent-ähnlicher Syntax.

**Migrations ausführen:**
```bash
php migrate.php
```

**Query-Beispiel:**
```php
use Illuminate\Database\Capsule\Manager as DB;

$users = DB::table('users')->get();
$user = DB::table('users')->find($id);
```

### View-System

Einfaches PHP-basiertes Templating mit Layout-Support.

**View rendern:**
```php
return view('page-name', ['data' => $value]);
```

**Layout verwenden:**
```php
// In View
$layout = 'layouts/app';
$content = ob_start();
// ... View-Inhalt ...
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
```

## QA-Tools

### Laravel Pint (Code Formatting)

```bash
vendor/bin/pint
```

**Config:** `pint.json` (PSR-12 Standard)

### PHPStan (Static Analysis)

```bash
vendor/bin/phpstan analyse app
```

**Config:** `phpstan.neon` (Level 4)

### PHPUnit (Testing)

```bash
vendor/bin/phpunit
```

**Config:** `phpunit.xml`

## Deployment

**Production-Build:**
1. `composer install --no-dev --optimize-autoloader`
2. `npm run build` (TailwindCSS kompilieren)
3. `.env.prod` → `.env` kopieren
4. Migrationen ausführen
5. Via FTP uploaden

**Siehe:** `deploy/README.md` für Details

## Für Cursor 2.0 Agents

### Wichtige Hinweise

1. **Nie `vendor/` oder `node_modules/` committen** (`.gitignore` beachten)
2. **Immer `.env.example` aktualisieren** wenn neue Environment-Variablen hinzugefügt werden
3. **Views verwenden Layout-System** (`layouts/app.php`)
4. **Migrations für Datenbank-Änderungen** verwenden
5. **QA-Tools nach Code-Änderungen ausführen**

### Häufige Tasks

**Neue Route hinzufügen:**
```php
// 1. config/routes.php erweitern
// 2. app/Controllers/NewController.php erstellen
// 3. resources/views/new-view.php erstellen
```

**Neue Migration erstellen:**
```php
// 1. database/migrations/YYYY_MM_DD_name.php erstellen
// 2. php migrate.php ausführen
```

**Neuen Controller erstellen:**
```php
namespace App\Controllers;

class NewController
{
    public function index(): string
    {
        return view('view-name', ['data' => $value]);
    }
}
```

### Code-Stil

- **PSR-12** für Code-Formatting
- **Type Hints** wo möglich (`: string`, `: void`)
- **Return Types** immer angeben
- **Namespaces** korrekt verwenden

## Dependencies

**Runtime:**
- vlucas/phpdotenv
- illuminate/database
- illuminate/container
- illuminate/view
- nikic/fast-route
- monolog/monolog
- respect/validation

**Dev-Tools:**
- phpunit/phpunit
- laravel/pint
- phpstan/phpstan

## Shared Hosting Kompatibilität

✅ Keine Node.js Runtime nötig (nur für Dev-Build)
✅ Keine SSH-Zugang nötig (FTP reicht)
✅ Keine Composer nötig auf Server (vendor/ hochladen)
✅ SQLite für lokale Entwicklung
✅ MySQL für Production

## Nächste Schritte

Nach Installation:
1. `composer install` ausführen
2. `npm install` ausführen
3. `npm run build` ausführen
4. `php migrate.php` ausführen
5. Lokalen Server starten: `php -S localhost:8000 -t public`

