# Quick Start Guide

## Installation der Entwicklungs-Tools

Die folgenden Tools werden für die Entwicklung benötigt:

### Benötigte Programme:
1. **PHP 8.2+** 
2. **Composer**
3. **Node.js** (LTS)
4. **npm** (kommt mit Node.js)

### Installation:

```powershell
# 1. Installiere PHP
# Besuche: https://windows.php.net/download/
# Download: VS16 x64 Non Thread Safe
# Entpacke nach: C:\php
# Füge C:\php zum PATH hinzu

# 2. Installiere Composer
# Besuche: https://getcomposer.org/download/
# Download: Composer-Setup.exe
# Während Installation: "Add to PATH" aktivieren

# 3. Installiere Node.js
# Besuche: https://nodejs.org/
# Download: LTS Version
# Installer starten
```

### Überprüfung:

```powershell
.\check-installation.ps1
```

**Alle Programme sollten grün (OK) anzeigen.**

### Detaillierte Anleitung:

Für detaillierte Installationsanleitungen siehe: **INSTALLATION.md**

---

## Projekt-Setup

Nach erfolgreicher Installation:

```powershell
# 1. Dependencies installieren
composer install
npm install

# 2. TailwindCSS bauen
npm run build

# 3. Datenbank-Migrationen
php migrate.php

# 4. Server starten
php -S localhost:8000 -t public
```

### Im Browser öffnen:

- **Home**: http://localhost:8000
- **About**: http://localhost:8000/about

---

## Nützliche Befehle

```powershell
# Code formatieren
vendor/bin/pint

# Static Analysis
vendor/bin/phpstan analyse app

# Tests ausführen
vendor/bin/phpunit

# TailwindCSS watch (automatisch rebuild)
npm run watch
```

---

## Hilfe

Bei Problemen:
1. Führe `.\check-installation.ps1` aus
2. Lies `INSTALLATION.md` für detaillierte Anleitung
3. Lies `README.md` für Projekt-Informationen

