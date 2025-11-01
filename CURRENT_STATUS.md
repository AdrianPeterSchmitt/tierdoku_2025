# Tierdokumentation - Aktueller Stand (2025)

## 📋 Projekt-Übersicht

Vollständige, produktionsreife Web-Anwendung für die Verwaltung von Tierkremationen für Animea Tierkrematorium.

**Status:** ✅ Produktionsreif | **Version:** 1.0.0 | **Letzte größere Aktualisierung:** 2025

---

## ✅ Vollständig implementierte Features

### 🔐 Authentifizierung & Autorisierung
- ✅ Login/Logout-System mit Session-Management
- ✅ 3 Rollen: Admin, Manager, Mitarbeiter
- ✅ Session Timeout (30 Minuten) mit Warnung
- ✅ Rate Limiting (5 Versuche pro 15 Minuten)
- ✅ Account Locking nach fehlgeschlagenen Versuchen
- ✅ Standort-basierte Berechtigungen

### 📊 Kern-Funktionalität
- ✅ **Kremations-CRUD**: Vollständiges Create, Read, Update, Delete
- ✅ **Inline-Editing**: Direkte Bearbeitung in Formularen (Kremationen, User, Herkunft, Standort)
- ✅ **Soft Deletes** mit Wiederherstellungs-Funktion
- ✅ **Bulk Operations**: Mehrere Kremationen gleichzeitig verwalten
- ✅ **Standort-Management**: 3 Standorte (Laudenbach, Usingen, Schwarzwald) mit Inline-Editing
- ✅ **Herkunft-Management**: Dynamisches Anlegen mit Inline-Editing
- ✅ **Tierart-Tracking**: Vogel, Heimtier, Katze, Hund
- ✅ **Benutzer-Verwaltung**: Inline-Editing, Admin-Schutz (Admin kann nicht gelöscht werden)

### 📱 QR-Code-System
- ✅ **QR-Code-Generierung**: Für jede Kremation (PNG/SVG Fallback)
- ✅ **QR-Code-Ansicht**: Mit Druckfunktion
- ✅ **Einzel-Scanner**: QR-Code Scanner für einzelne Beutel (automatischer Abschluss)
- ✅ **Batch-Scanner**: Mehrere Kremationen nacheinander scannen und zusammen abschließen
- ✅ **Kamera-Support**: HTML5-Kamera API (Webcam/Handy-Kamera)
- ✅ **Duplikat-Erkennung**: Automatische Erkennung bereits gescannter Kremationen
- ✅ **Mobile-Support**: Optimiert für Tablets und Smartphones

### 📄 PDF-Label-System
- ✅ **Professionelle PDF-Labels**: Mit allen Kremationsdaten
- ✅ **QR-Code integriert**: Im PDF eingebettet
- ✅ **Druckfertig**: A4-Format
- ✅ **Automatischer Download**: Nach Generierung

### 📈 Statistik-Dashboard
- ✅ **KPI-Karten**: Gesamt, Offen, Abgeschlossen, Ø Gewicht
- ✅ **Timeline-Chart**: Letzte 30 Tage
- ✅ **Verteilungs-Charts**: Nach Standort, Herkunft, Tierart
- ✅ **Status-Pie-Chart**: Offen vs. Abgeschlossen
- ✅ **Filter**: Nach Datum, Standort und Herkunft
- ✅ **24-Stunden-Format**: Alle Datum/Zeit-Felder

### 👥 Verwaltungs-Interfaces
- ✅ **User-Verwaltung**: Inline-Editing, Delete mit Bestätigungs-Modal, Admin-Schutz
- ✅ **Herkunft-Verwaltung**: Inline-Editing, Delete mit Bestätigungs-Modal
- ✅ **Standort-Verwaltung**: Inline-Editing, Delete mit Bestätigungs-Modal, Aktiv/Inaktiv Toggle
- ✅ **Kremation-Verwaltung**: Inline-Editing mit 24h-Datum/Zeit-Picker

### 🎨 UI/UX Features
- ✅ **Navigation**: Burger-Menü mit Icons auf allen Seiten
- ✅ **Custom Modals**: Styled Delete-Bestätigungs-Dialoge (ersetzt Browser confirm())
- ✅ **Konsistente Buttons**: Standard-Größe (w-[150px], px-8 py-3, font-semibold)
- ✅ **Responsive Design**: Desktop, Tablet, Mobile
- ✅ **Dark Theme**: Modernes UI-Design
- ✅ **Flash Messages**: Benutzerfeedback

---

## 🛠️ Technischer Stack

### Backend
- **PHP** 8.2+
- **FastRoute** - High-Performance Routing
- **Illuminate/Eloquent** - ORM mit Migrations
- **Monolog** - PSR-3 Logging
- **Respect/Validation** - Input-Validierung
- **Dompdf** - PDF-Generierung
- **Endroid QR Code** - QR-Code-Erstellung (v6.0.9)

### Frontend
- **TailwindCSS** - Utility-First CSS Framework
- **Alpine.js** 3.x - Leichtgewichtiges JavaScript Framework
- **Chart.js** - Diagramme & Visualisierungen
- **HTML5-QRCode** - QR-Code-Scanner

### Development Tools
- **PHPStan** Level 7 - Statische Code-Analyse
- **PHPUnit** - Unit & Feature Tests
- **Laravel Pint** - Code-Formatierung

---

## 📂 Wichtige Dateien & Verzeichnisse

### Controllers
- `app/Controllers/KremationController.php` - Haupt-Controller für Kremationen (inkl. QR/Scan/Complete)
- `app/Controllers/UserController.php` - User-Verwaltung mit Inline-Editing
- `app/Controllers/HerkunftController.php` - Herkunft-Verwaltung mit Inline-Editing
- `app/Controllers/StandortController.php` - Standort-Verwaltung mit Inline-Editing
- `app/Controllers/StatisticsController.php` - Statistik-Dashboard mit Filterung
- `app/Controllers/AuthController.php` - Authentifizierung

### Services
- `app/Services/QRCodeService.php` - QR-Code-Generierung (PNG/SVG Fallback)
- `app/Services/PDFLabelService.php` - PDF-Label-Generierung
- `app/Services/KremationService.php` - Business-Logik für Kremationen (inkl. Complete-Funktion)
- `app/Services/AuthService.php` - Authentifizierung & Session-Management
- `app/Services/AuditService.php` - Audit-Logging
- `app/Services/NotificationService.php` - Benachrichtigungen

### Views
- `resources/views/kremation/index.php` - Haupt-Kremations-Verwaltung (Inline-Editing, 24h-Datetime)
- `resources/views/kremation/scan.php` - Einzel-Scanner (automatischer Abschluss)
- `resources/views/kremation/batch-scan.php` - Batch-Scanner (mehrere Kremationen)
- `resources/views/kremation/qr-code.php` - QR-Code-Anzeige
- `resources/views/users/index.php` - User-Verwaltung (Inline-Editing, Delete-Modal)
- `resources/views/herkunft/index.php` - Herkunft-Verwaltung (Inline-Editing, Delete-Modal)
- `resources/views/standort/index.php` - Standort-Verwaltung (Inline-Editing, Delete-Modal, Toggle)
- `resources/views/statistics/index.php` - Statistik-Dashboard (Filter: Datum, Standort, Herkunft)
- `resources/views/partials/nav.php` - Navigation (Burger-Menü mit Icons)

### Models
- `app/Models/Kremation.php` - Haupt-Model mit Relationships
- `app/Models/User.php` - User-Model (mit @property string $name)
- `app/Models/Herkunft.php` - Herkunft-Model
- `app/Models/Standort.php` - Standort-Model

### Routing
- `config/routes.php` - Alle Routes (inkl. `/standort/{id}/edit`, `/herkunft/{id}/edit`, `/users/{id}/edit`)
- `public/index.php` - Entry Point (protected routes check)

---

## 🔑 Wichtige Implementierungsdetails

### Inline-Editing Pattern
Alle Verwaltungs-Interfaces (User, Herkunft, Standort, Kremation) nutzen das gleiche Inline-Editing-Pattern:
1. Formular wechselt zwischen "Hinzufügen" und "Bearbeiten" Modus
2. `edit{Entity}(id)` Funktion lädt Daten via API (`/entity/{id}/edit`)
3. Daten werden ins Formular geladen, `isEditMode = true`
4. Formular scrollt automatisch in den Viewport
5. `handleSubmit()` erkennt Modus und verwendet passende Route (POST /entity vs. POST /entity/{id})

### Delete-Modals
- Custom styled Delete-Bestätigungs-Modals (ersetzt `confirm()`)
- Konsistente Button-Größen (`w-[150px]`, `px-8 py-3`, `font-semibold`)
- Warnungen bei eingeschränkten Löschungen (Admin, Verwendungen)
- Server-seitige Validierung verhindert fehlerhafte Löschungen

### Datum/Zeit-Handling
- **24-Stunden-Format**: Separates Date- und Time-Input (kein datetime-local)
- Kombination aus `type="date"`, `type="number"` (Stunde), `type="number"` (Minute)
- JavaScript kombiniert Werte in verstecktes `Einaescherungsdatum` Feld
- Layout-Optimierung verhindert abgeschnittene Zahlen

### QR-Code-System
- **GD-Extension Fallback**: Wenn GD nicht verfügbar, wird SVG statt PNG verwendet
- `QRCodeService->getLastMimeType()` gibt MIME-Type zurück
- PDF-Labels unterstützen beide Formate (PNG/SVG)

### Navigation
- Burger-Menü mit Icons auf allen Seiten
- Alpine.js für Toggle-Funktionalität
- `x-cloak` verhindert Flackern beim Laden

---

## 🔧 Aktuelle Konfiguration

### Routes
- `GET /standort/{id}/edit` - Standort-Daten für Inline-Editing
- `GET /herkunft/{id}/edit` - Herkunft-Daten für Inline-Editing
- `GET /users/{id}/edit` - User-Daten für Inline-Editing
- `POST /kremation/complete` - Kremation abschließen (setzt einaescherungsdatum)

### Protected Routes
Routes, die Authentifizierung erfordern:
- `/kremation`, `/kremation/*`
- `/herkunft`, `/herkunft/*`
- `/standort`, `/standort/*`
- `/users`, `/users/*`
- `/statistics`, `/notifications/*`

### Datenbank-Schema
Haupttabellen:
- `users` - Benutzer mit Rollen (name, username, email, role, standort_id)
- `standort` - Standorte (name, aktiv)
- `herkunft` - Herkunftsorte (name, standort_id)
- `kremation` - Haupt-Kremations-Tabelle (inkl. einaescherungsdatum für Abschluss)
- `kremation_tiere` - Pivot für Tierarten
- `audit_log` - Audit-Trail
- `notifications` - Benachrichtigungen

---

## 🚀 Setup & Installation

### Dependencies
```bash
composer install
npm install
```

### Environment
```bash
cp .env.example .env
# Bearbeite .env für lokale Entwicklung
```

### Datenbank
```bash
php migrate.php
php seed.php tierdoku
```

### Assets
```bash
npm run build
```

### Server starten
```bash
php -S localhost:8000 -t public
```

### Login
- **URL**: http://localhost:8000/login
- **Username**: admin
- **Password**: admin123

---

## 📊 Code-Qualität

### PHPStan
```bash
php -d memory_limit=512M vendor/bin/phpstan analyse
```
**Status:** ✅ Keine Fehler (Level 7)

### Test-Suite
```bash
composer test
```

---

## 🔄 Workflow für Weiterentwicklung

### 1. Entwicklung
- Änderungen lokal testen
- PHPStan ausführen: `php -d memory_limit=512M vendor/bin/phpstan analyse`
- Tests ausführen: `composer test`

### 2. Commits
- Sinnvolle Commit-Messages verwenden
- Änderungen nach Feature gruppieren

### 3. Push
```bash
git add -A
git commit -m "Feature: Beschreibung"
git push
```

---

## 📝 Nächste Schritte (Optionale Erweiterungen)

### Mögliche Features
- [ ] E-Mail-Benachrichtigungen
- [ ] REST API für externe Integrationen
- [ ] Erweiterte Reports
- [ ] Multi-Language Support (i18n)
- [ ] Advanced Search mit Filtern
- [ ] Export-Funktionen erweitern (Excel, etc.)

### UI-Verbesserungen
- [ ] Keyboard-Shortcuts
- [ ] Drag & Drop für Datei-Uploads
- [ ] Dark/Light Theme Toggle

---

## 🐛 Bekannte Einschränkungen

1. **QR-Code GD-Extension**: Wenn GD nicht verfügbar, wird SVG verwendet (funktioniert aber)
2. **Kamera-Support**: Funktioniert am besten auf mobilen Geräten, Desktop-Webcams funktionieren auch
3. **DateTime-Picker**: Native HTML5-Inputs für bessere Browser-Kompatibilität

---

## 📞 Support

Bei Fragen zur Weiterentwicklung:
- Code-Struktur in `cursor.md`
- API-Dokumentation in `README.md`
- Installation in `INSTALLATION.md`

---

**Stand:** 2025 | **Version:** 1.0.0 | **Status:** ✅ Produktionsreif

