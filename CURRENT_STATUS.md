# Aktueller Projekt-Status

**Stand:** 2025-02-03  
**Letzte Änderungen:** Standort-spezifische Nummernkreise, Dynamische Herkunft-Filterung, Multi-Location-Support

## ✅ Implementierte Features

### 1. Standort-spezifische Nummernkreise mit Prefix
- **Status:** ✅ Implementiert & Migriert
- **Format:** `{PREFIX}{NUMMER}` (z.B. LAU001, SCH002)
- **Prefix:** Erste 3 Buchstaben des Standort-Namens (Großbuchstaben)
- **Nummer:** 3-stellige fortlaufende Nummer pro Standort (001, 002, 003...)
- **Migration:** `2025_02_03_000001_change_vorgangs_id_to_string.php`
- **Model-Änderungen:**
  - `Standort::getPrefix()` - Prefix-Generierung
  - `Kremation::nextVorgangsNummer()` - Generiert String-Format mit Prefix
  - `Kremation::scopeSearch()` - Unterstützt String-IDs
- **Datenbank:**
  - `vorgangs_id`: VARCHAR(20) (String Primary Key)
  - `kremation_tiere.kremation_id`: VARCHAR(20)
  - `audit_log.record_id`: VARCHAR(50)
- **Initial-Migration:** Aktualisiert für neue Installationen (String-Format direkt)

### 2. Dynamische Herkunft-Filterung
- **Status:** ✅ Implementiert
- **Funktion:** Herkunft-Dropdown wird dynamisch basierend auf ausgewähltem Standort gefiltert
- **API-Route:** `GET /api/herkunft/by-standort/{standortName}`
- **Controller:** `HerkunftController::getByStandortName()`
- **Frontend:** Alpine.js `loadHerkunfteForStandort()` Funktion
- **Features:**
  - Automatisches Laden beim Standort-Wechsel
  - Lade-Anzeige während API-Call
  - Deaktiviert, wenn kein Standort ausgewählt

### 3. Multi-Location-Support für Benutzer
- **Status:** ✅ Implementiert & Migriert
- **Migration:** `2025_02_02_000001_create_user_standort_table.php`
- **Features:**
  - User können mehreren Standorten zugeordnet werden (Many-to-Many)
  - Checkbox-Gruppe in User-Formular für Standort-Auswahl
  - Default-Standort für non-Admin User
  - Automatisches Setzen des Default-Standorts bei Kremation-Erstellung
  - Last-Selected-Standort wird in localStorage gespeichert
- **Model-Änderungen:**
  - `User::standorte()` - BelongsToMany Relation
  - `User::getDefaultStandortId()` - Default-Standort-Logik
  - `User::hasStandort()` - Zugriffsprüfung
  - `User::getAllowedStandortIds()` - Liste erlaubter Standorte

### 4. Inline-Editing Pattern
- **Status:** ✅ Implementiert
- **Bereiche:** User, Herkunft, Standort
- **Features:**
  - Einheitliches Formular für Add/Edit
  - Alpine.js State Management (`isEditMode`, `formData`)
  - Custom Delete-Confirmation-Modals
  - Konsistente Button-Größen und Styling

### 5. Konfigurationsseite
- **Status:** ✅ Implementiert
- **Route:** `/config` (nur Admin)
- **Features:**
  - Verwaltung aller `.env` Einstellungen
  - QR-Code-Konfiguration (Größe, Margin, Encoding, Error Correction)
  - PDF-Label-Konfiguration (Papiergröße, Schriftgrößen, QR-Code-Parameter)
  - Database-Einstellungen
  - Session-Einstellungen
  - Backup-Funktionalität vor Änderungen

### 6. Installer
- **Status:** ✅ Implementiert
- **Datei:** `public/install.php`
- **Features:**
  - System-Checks (PHP-Version, Extensions, Permissions)
  - `.env` Erstellung mit Formular
  - Datenbank-Connection-Test
  - Automatische Migration-Ausführung
  - Erfolgs-Instructions

## 📁 Wichtige Dateien & Struktur

### Migrations
```
database/migrations/
├── 2025_01_30_000000_create_users_table.php
├── 2025_01_31_000000_create_tierdoku_tables.php (aktualisiert: String vorgangs_id)
├── 2025_02_01_000001_add_standort_to_herkunft.php
├── 2025_02_02_000001_create_user_standort_table.php
└── 2025_02_03_000001_change_vorgangs_id_to_string.php
```

### Models
```
app/Models/
├── Kremation.php (vorgangs_id: string, nextVorgangsNummer() mit Prefix)
├── Standort.php (getPrefix() Methode)
├── User.php (Multi-Location Support)
└── ...
```

### Controllers
```
app/Controllers/
├── KremationController.php (String-ID Support)
├── HerkunftController.php (getByStandortName() API)
├── UserController.php (Multi-Location)
├── ConfigController.php (Konfigurationsseite)
└── ...
```

### Services
```
app/Services/
├── KremationService.php (String-ID Support)
├── AuditService.php (int|string recordId)
├── QRCodeService.php (Konfigurierbar)
└── PDFLabelService.php (Konfigurierbar)
```

## 🔧 Code-Qualität

### PHPStan
- **Level:** 7
- **Status:** ⚠️ 15 Fehler (meist Type-Hints, nicht kritisch)
- **Kritische Fehler:** ✅ Behoben
  - `AuditService::log()` - `int|string $recordId`
  - `KremationService::restore()` - `int|string $id`
  - `ConfigController::readEnvFile()` - file() Rückgabewert-Prüfung

### Verbleibende PHPStan-Warnungen (nicht kritisch):
- Scope-Methoden Return-Types (Eloquent Builder vs Query Builder)
- Array-Iterable Types (getAllowedStandortIds())
- Standorte() Relation Return-Type

## 🗄️ Datenbank-Struktur

### Kremation
- `vorgangs_id`: VARCHAR(20) PRIMARY KEY (Format: LAU001, SCH002, etc.)
- `standort_id`: Foreign Key
- `herkunft_id`: Foreign Key
- ...

### User_Standort (Pivot Table)
- `user_id`: Foreign Key
- `standort_id`: Foreign Key
- `default_standort_id`: Foreign Key (nullable)

### Audit_Log
- `record_id`: VARCHAR(50) (unterstützt int und string)

## 🚀 Nächste Schritte / Offene Punkte

### Optional (nicht kritisch):
1. **PHPStan-Warnungen beheben:**
   - PHPDoc für Scope-Methoden verbessern
   - Array-Types spezifizieren (`@return array<int>`)
   - Relation Return-Types korrigieren

2. **Tests erweitern:**
   - Unit-Tests für Prefix-Generierung
   - Feature-Tests für Nummernkreis-System
   - API-Tests für Herkunft-Filterung

3. **Dokumentation:**
   - API-Dokumentation für neue Endpoints
   - Migration-Guide für bestehende Installationen

## 📝 Wichtige Hinweise für Weiterarbeit

### Prefix-System
- Prefix wird aus ersten 3 Buchstaben des Standort-Namens generiert
- Zu kurz? → Wird mit 'X' aufgefüllt
- Maximale Anzahl pro Standort: 999 (dann kann auf 4-stellig erweitert werden)

### Migration-Reihenfolge
1. Bestehende Installationen: Migration `2025_02_03_000001` wird automatisch erkannt und übersprungen wenn bereits migriert
2. Neue Installationen: Verwenden direkt String-Format aus Initial-Migration

### String-IDs vs Integer-IDs
- `vorgangs_id` ist jetzt immer String (Format: LAU001)
- `AuditService::log()` akzeptiert `int|string` für `recordId`
- Alle Controller-Methoden verwenden String für `vorgangs_id`

### Multi-Location-Logik
- Non-Admin User: Können nur ihre zugewiesenen Standorte sehen/verwenden
- Admin User: Sehen alle Standorte
- Default-Standort: Wird automatisch beim ersten Kremation-Erstellen gesetzt
- Last-Selected: Wird in localStorage gespeichert und beim nächsten Besuch vorausgewählt

### Herkunft-Filterung
- API-Endpoint: `/api/herkunft/by-standort/{standortName}`
- Authentifizierung: Erforderlich (geschützte Route)
- Zugriffsprüfung: User muss Zugriff auf den Standort haben
- Automatisches Laden: Beim Standort-Wechsel im Kremation-Formular

## 🔐 Sicherheit

- Alle API-Routes sind geschützt (authentifiziert)
- Zugriffsprüfung für Standorte (non-Admin User)
- CSRF-Schutz für Formulare
- Input-Validierung in Services

## 🎨 UI/UX

- Inline-Editing: User, Herkunft, Standort
- Custom Modals: Delete-Confirmations (keine Browser-alerts)
- Konsistente Button-Größen und Styling
- Dynamische Formular-Elemente (Herkunft-Dropdown)
- Loading-States für API-Calls

## 📊 Migration-Status

- ✅ Alle Migrations ausgeführt
- ✅ Bestehende Daten migriert (LAU001, LAU002, USI001, etc.)
- ✅ Neue Installationen verwenden direkt String-Format

---

**Für Fragen oder Probleme:** Siehe README.md oder CONTRIBUTING.md

