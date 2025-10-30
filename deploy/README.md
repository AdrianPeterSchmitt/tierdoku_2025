# Deployment-Anleitung

Diese Anleitung beschreibt die Schritte zum Deployment der PHP WebApp auf einem **Shared Hosting Server** (Apache + PHP + MySQL).

## 📋 Voraussetzungen

- FTP/SFTP-Zugang zu deinem Shared Host
- MySQL-Datenbank
- PHP 8.2+ auf dem Server
- Zugriff auf phpMyAdmin oder MySQL-Befehle

## 🚀 Deployment-Prozess

### 1. Environment-Datei anpassen

Kopiere `.env.prod` zu `.env` und passe die Werte an:

```bash
copy .env.prod .env
```

Bearbeite `.env`:

```env
APP_NAME=Tierdokumentation
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=deine_datenbank
DB_USERNAME=dein_benutzername
DB_PASSWORD=dein_passwort

LOG_LEVEL=error
```

### 2. Production-Build erstellen

```bash
# Composer Dependencies installieren (ohne Dev-Dependencies)
composer install --no-dev --optimize-autoloader

# TailwindCSS bauen (minifiziert)
npm run build

# Node-Module sind im Production-Build NICHT nötig
```

### 3. Migrationen auf Server ausführen

Auf dem Server die Datenbank vorbereiten:

#### Option A: Via SSH

```bash
# Auf dem Server
php migrate.php
```

#### Option B: Via phpMyAdmin

Alternativ SQL-Dateien manuell in phpMyAdmin importieren.

### 4. Upload der Dateien

Lade folgende Ordner/Dateien per FTP hoch:

#### Zu uploaden:

```
📁 public/          → Alle Dateien in den Webroot
📁 vendor/          → Composer Dependencies
📁 app/             → Anwendungs-Code
📁 config/          → Konfiguration
📁 database/
   └── migrations/  → Migration-Dateien
📁 resources/
   └── views/       → Templates
📁 storage/
   └── logs/        → (leeres Verzeichnis)
📄 .env             → Environment-Konfiguration
📄 migrate.php      → (optional, für zukünftige Migrationen)
```

#### NICHT uploaden:

```
❌ tests/
❌ node_modules/
❌ .env.example
❌ .env.prod
❌ phpunit.xml
❌ phpstan.neon
❌ pint.json
❌ tailwind.config.js
❌ postcss.config.js
❌ composer.json
❌ composer.lock
❌ package.json
❌ package-lock.json
❌ public/style.css (nur die kompilierte Version)
```

### 5. Berechtigungen setzen

Wichtig für Logging:

```bash
chmod 755 storage/logs
chmod 644 storage/logs/*.log
```

### 6. Apache-Konfiguration prüfen

Stelle sicher, dass `.htaccess` aktiviert ist:

```apache
AllowOverride All
```

### 7. Verzeichnisstruktur auf dem Server

```
/home/benutzername/
└── public_html/
    ├── .htaccess
    ├── index.php
    ├── dist/
    │   └── style.css
    ├── ../
    │   └── app/
    │   └── config/
    │   └── database/
    │   └── resources/
    │   └── storage/
    │   └── vendor/
    │   └── .env
```

### 8. PHP-Version überprüfen

Im Webhosting-Panel:
- PHP-Version auf **8.2+** setzen
- Required Extensions aktivieren: `PDO`, `pdo_mysql`, `mbstring`, `openssl`

### 9. Erweiterte Konfiguration

#### Cron-Jobs (falls nötig)

```bash
# Beispiel: Täglich Log-Dateien rotieren
0 0 * * * find /path/to/storage/logs -name "*.log" -mtime +7 -delete
```

#### Error-Logging

In `.env`:

```env
LOG_CHANNEL=file
LOG_LEVEL=error
LOG_FILE=storage/logs/app.log
```

## 🔍 Testing nach Deployment

### 1. Startseite testen

Öffne: `https://deine-domain.de/`

### 2. Weitere Routen testen

- `https://deine-domain.de/about`
- `https://deine-domain.de/fakepage` (sollte 404 zeigen)

### 3. Datenbank-Verbindung prüfen

Log in die Datenbank eintragen und prüfen, ob Migrationen korrekt ausgeführt wurden.

### 4. Logs prüfen

```bash
tail -f storage/logs/app.log
```

## 🛠️ Troubleshooting

### Fehler: "Class not found"

✅ **Lösung:** `composer install --optimize-autoloader` erneut ausführen

### Fehler: "Page not found"

✅ **Lösung:** `.htaccess` prüfen, `AllowOverride All` aktivieren

### Fehler: "Permission denied" bei Logging

✅ **Lösung:** Berechtigungen setzen:
```bash
chmod 755 storage
chmod 755 storage/logs
chmod 644 storage/logs/*.log
```

### Fehler: "Database connection failed"

✅ **Lösung:** `.env` Werte prüfen, MySQL-Datenbank existiert

### CSS wird nicht geladen

✅ **Lösung:** `npm run build` erneut ausführen, `public/dist/style.css` hochladen

## 📊 Maintenance

### Regelmäßige Tasks

1. **Logs rotieren** (wöchentlich)
2. **Backups erstellen** (täglich)
3. **Composer Updates** (monatlich)
4. **Security Updates** (sofort)

### Updates installieren

```bash
# Auf dem lokalen Rechner
composer update
npm update

# Testen
php migrate.php
npm run build

# Dann auf Server uploaden
```

## 📞 Support

Bei Problemen:
1. Logs prüfen: `storage/logs/app.log`
2. PHP-Fehlerlog prüfen
3. Datenbank-Verbindung testen

---

**Viel Erfolg beim Deployment! 🚀**

