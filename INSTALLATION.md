# 🚀 PHP WebApp - Entwicklungs-Umgebung Installation

Diese Anleitung zeigt dir, wie du alle benötigten Programme für die Entwicklung installierst.

## 📋 Benötigte Programme

1. **PHP 8.2+** - PHP Runtime
2. **Composer** - PHP Dependency Manager  
3. **Node.js** (LTS) - JavaScript Runtime
4. **npm** - Node Package Manager (kommt mit Node.js)

## 🔧 Schritt-für-Schritt Installation

### 1️⃣ PHP 8.2 Installation

#### Download:
- Besuche: https://windows.php.net/download/
- **Wähle**: `VS16 x64 Non Thread Safe` (empfohlen)
- ZIP-Datei herunterladen (z.B. `php-8.2.x-Win32-vs16-x64.zip`)

#### Installation:
1. Entpacke die ZIP-Datei nach `C:\php`
2. Kopiere `php.ini-development` zu `php.ini`
3. Öffne `php.ini` und aktiviere (entferne `;`):
   ```
   extension=pdo_mysql
   extension=pdo_sqlite
   extension=mbstring
   extension=curl
   extension=openssl
   extension=fileinfo
   ```

#### PATH hinzufügen:
1. Drücke `Win + R` → tippe `sysdm.cpl` → Enter
2. Tab **"Erweitert"** → **"Umgebungsvariablen"** 
3. Unter **"Systemvariablen"** → **"Path"** auswählen → **"Bearbeiten"**
4. **"Neu"** klicken → `C:\php` eintragen → **"OK"**

#### Überprüfung:
```powershell
php -v
# Sollte ausgeben: PHP 8.2.x ...
```

---

### 2️⃣ Composer Installation

#### Download:
- Besuche: https://getcomposer.org/download/
- Klicke auf **"Composer-Setup.exe"** herunterladen

#### Installation:
1. Führe `Composer-Setup.exe` aus
2. **Wichtig**: Aktiviere **"Add to PATH"** während der Installation
3. Wähle PHP aus (sollte automatisch `C:\php\php.exe` erkennen)

#### Überprüfung:
```powershell
composer -V
# Sollte ausgeben: Composer version ...
```

---

### 3️⃣ Node.js Installation

#### Download:
- Besuche: https://nodejs.org/
- **LTS Version** herunterladen (z.B. `node-v20.x.x-x64.msi`)

#### Installation:
1. Führe den Installer aus
2. Standard-Optionen beibehalten
3. **Wichtig**: Package wird automatisch zum PATH hinzugefügt

#### Überprüfung:
```powershell
node -v
# Sollte ausgeben: v20.x.x ...

npm -v  
# Sollte ausgeben: 10.x.x ...
```

---

## ✅ Installation Verifizieren

Öffne eine **NEUE** PowerShell oder CMD und führe aus:

```powershell
cd C:\Cursor_Projekte\Tierdokumentation

php -v
composer -V
node -v
npm -v
```

**Alle Befehle sollten Versionen ausgeben.** ✅

---

## 🎯 Projekt-Setup

Nach erfolgreicher Installation aller Programme:

### 1. Dependencies installieren

```powershell
cd C:\Cursor_Projekte\Tierdokumentation

# PHP Dependencies
composer install

# Node.js Dependencies
npm install
```

### 2. TailwindCSS bauen

```powershell
npm run build
```

### 3. Datenbank-Migrationen ausführen

```powershell
php migrate.php
```

### 4. Lokalen Server starten

```powershell
php -S localhost:8000 -t public
```

### 5. Im Browser öffnen

- **Homepage**: http://localhost:8000
- **About-Seite**: http://localhost:8000/about

---

## 🛠️ Troubleshooting

### "PHP nicht gefunden"
- **Problem**: PATH wurde nicht korrekt gesetzt
- **Lösung**: PowerShell/CMD neu starten, oder PATH manuell prüfen

### "Composer nicht gefunden"
- **Problem**: Installer hat PATH nicht aktualisiert
- **Lösung**: `C:\Users\DEIN_NAME\AppData\Roaming\Composer\vendor\bin` zum PATH hinzufügen

### "npm nicht gefunden"
- **Problem**: Node.js Installation fehlgeschlagen
- **Lösung**: Node.js als Administrator neu installieren

### "Composer install" Fehler
- **Problem**: PHP Extensions nicht aktiviert
- **Lösung**: `php.ini` öffnen und benötigte Extensions aktivieren (siehe oben)

### "npm install" Fehler
- **Problem**: Node.js Version zu alt
- **Lösung**: Aktuelle LTS Version von nodejs.org installieren

---

## 📚 Nützliche Links

- **PHP Download**: https://windows.php.net/download/
- **Composer**: https://getcomposer.org/download/
- **Node.js**: https://nodejs.org/
- **PHP Extensions List**: https://www.php.net/manual/en/extensions.mysql.php

---

## 🎉 Fertig!

Sobald alle Programme installiert sind, kannst du mit der Entwicklung beginnen!

**Next Steps:**
- Lies `README.md` für Projekt-Übersicht
- Lies `cursor.md` für Projekt-Struktur
- Lies `deploy/README.md` für Deployment

**Viel Erfolg! 🚀**


