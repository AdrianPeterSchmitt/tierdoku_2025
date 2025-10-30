# Deployment auf Shared Hosting (Apache + PHP)

## Voraussetzungen
- PHP-Version: 8.2 oder höher (mit `pdo_mysql` bzw. `pdo_sqlite` für Dev)
- Apache mit `mod_rewrite`
- MySQL (Prod) oder SQLite (nur lokal)

## Build-Artefakt erstellen (lokal)
1. Abhängigkeiten installieren:
   ```bash
   composer install
   npm install
   npm run build
   ```
2. Artefakt erzeugen:
   ```powershell
   powershell -ExecutionPolicy Bypass -File deploy/build-artifact.ps1
   ```
   Ergebnis: `build/tierdokumentation_release.zip`

## Upload auf den Server
- Zip entpacken in das Zielverzeichnis (z. B. `/htdocs/tierdoku`)
- DocumentRoot MUSS auf `public/` zeigen
- Falls keine Anpassung möglich: Projekt in Unterordner legen und per `.htaccess` (liegt in `public/`) URL-Rewriting aktivieren

## Server-Konfiguration
- `.env` auf dem Server erstellen (nie committen)
  ```env
  APP_ENV=production
  APP_DEBUG=false
  DB_CONNECTION=mysql
  DB_HOST=localhost
  DB_PORT=3306
  DB_DATABASE=<db>
  DB_USERNAME=<user>
  DB_PASSWORD=<pass>
  SESSION_SECURE=false  # falls kein HTTPS, sonst true
  ```
- Schreibrechte: `storage/` (Logs) und ggf. `database/` (nur bei SQLite) müssen beschreibbar sein

## Migrationen (einmalig pro Release)
Auf Shared-Servern meist per temporärer CLI oder kleiner Admin-Route ausführen. Empfohlen: kurzzeitig `migrate.php` über geschützte Route triggern (danach wieder deaktivieren) oder Migration lokal durchführen und DB dumpen/importieren.

## Sicherheit
- `APP_DEBUG=false`
- `public/.htaccess` aktiv (Rewrite + Schutz vor versteckten Dateien)
- Starke Passwörter, regelmäßige Updates (Composer)
- Logs prüfen: `storage/logs/app.log`

## Updates
- Neues Release-Zip hochladen, vorhandene Dateien überschreiben (außer `.env` und ggf. `database/*.sqlite`)
- DB-Migrationen ausführen


