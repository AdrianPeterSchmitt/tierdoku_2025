# Aktueller Projekt-Status

**Stand:** 2025-02-04  
**Letzte Änderungen:** Standort-Filter, Kremation-Abschluss/Rückgängig, PDF-Layout für A7, Tierarten-Anzeige korrigiert

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

### 3. Standort-Filter für Kremation-Tabelle
- **Status:** ✅ Implementiert (2025-02-04)
- **Funktion:** Dropdown-Filter oberhalb der Kremation-Tabelle zum Filtern nach Standort
- **UI:** Rechts oben neben "Letzte Einträge"
- **Verhalten:**
  - "Alle Standorte" zeigt alle erlaubten Standorte
  - Auswahl eines Standorts filtert die Tabelle
  - Für Admins: Alle Standorte verfügbar
  - Für Non-Admins: Nur zugewiesene Standorte verfügbar
- **JavaScript:** `applyStandortFilter()` Funktion aktualisiert URL-Parameter
- **Controller:** `KremationController::index()` unterstützt `?standort=X` Parameter

### 4. Multi-Location-Support für Benutzer
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

### 5. Kremation-Abschluss und Rückgängig
- **Status:** ✅ Implementiert (2025-02-04)
- **Features:**
  - "Abschließen"-Button (✅) in der Kremation-Tabelle
    - Immer sichtbar (auch wenn Kremation bereits abgeschlossen)
    - Deaktiviert, wenn `einaescherungsdatum` gesetzt ist
    - Erste Position in den Aktions-Buttons
  - "Rückgängig machen"-Button (🔄) im Bearbeitungsformular
    - Nur sichtbar im Edit-Modus
    - Setzt `einaescherungsdatum` auf `null`
    - Rückt Status von "Abgeschlossen" auf "Offen"
- **Backend:**
  - `KremationService::updateFull()` - Unterstützt Leeren von `einaescherungsdatum`
  - `KremationService::update()` - Setzt `einaescherungsdatum` auf `null` wenn Wert leer

### 6. Tierarten-Anzeige korrigiert
- **Status:** ✅ Behoben (2025-02-04)
- **Problem:** Tierarten-Anzahlen (Vogel, Heimtier, Katze, Hund) zeigten alle 0
- **Ursache:** Eloquent BelongsToMany mit String-Primary-Key lud Pivot-Daten nicht korrekt
- **Lösung:**
  - Migration korrigiert: `kremation_id` explizit als `string('kremation_id', 20)` definiert`
  - `Kremation::tierarten()` - Explizite Definition von local/related keys (`vorgangs_id`/`tierart_id`)
  - View: Direkter Aufruf `$k->tierarten()->get()` statt Eager Loading für robusteres Laden
  - Mehrfache Fallback-Methoden für Pivot-Daten-Zugriff (`getAttribute`, Property, Array)

### 7. PDF-Layout für kleine Formate (A7)
- **Status:** ✅ Implementiert (2025-02-04)
- **Funktion:** Dynamische Anpassung des PDF-Layouts basierend auf Papiergröße
- **Konfiguration:** Via `.env` (`PDF_PAPER_SIZE`, `PDF_FONT_SIZE_*`, `PDF_QR_CODE_SIZE_MM`)
- **Features:**
  - Erkennt kleine Formate (A7, A6, A5) automatisch
  - Block-basiertes Layout statt Table-Layout für kleine Formate
  - Skalierte Schriftgrößen, Margins, Padding
  - QR-Code-Größe wird automatisch angepasst
- **Service:** `PDFLabelService::buildLabelHTML()` und `generateLabelWithQR()`
- **QR-Code-Einbettung:** Mehrere Strategien (Exact Match, Regex, Fallback)

### 8. Inline-Editing Pattern
- **Status:** ✅ Implementiert
- **Bereiche:** User, Herkunft, Standort
- **Features:**
  - Einheitliches Formular für Add/Edit
  - Alpine.js State Management (`isEditMode`, `formData`)
  - Custom Delete-Confirmation-Modals
  - Konsistente Button-Größen und Styling

### 9. Konfigurationsseite
- **Status:** ✅ Implementiert
- **Route:** `/config` (nur Admin)
- **Features:**
  - Verwaltung aller `.env` Einstellungen
  - QR-Code-Konfiguration (Größe, Margin, Encoding, Error Correction)
  - PDF-Label-Konfiguration (Papiergröße, Schriftgrößen, QR-Code-Parameter)
  - Database-Einstellungen (MySQL/SQLite)
  - Session-Einstellungen
  - Backup-Funktionalität vor Änderungen
  - Bedingte Anzeige: DB-Credentials nur bei MySQL

### 10. Installer
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
├── 2025_01_31_000000_create_tierdoku_tables.php (String vorgangs_id, String kremation_id)
├── 2025_02_01_000001_add_standort_to_herkunft.php
├── 2025_02_02_000001_create_user_standort_table.php
└── 2025_02_03_000001_change_vorgangs_id_to_string.php
```

### Models
```
app/Models/
├── Kremation.php (vorgangs_id: string, nextVorgangsNummer() mit Prefix, tierarten() mit expliziten Keys)
├── Standort.php (getPrefix() Methode)
├── User.php (Multi-Location Support, standorte() BelongsToMany)
└── ...
```

### Controllers
```
app/Controllers/
├── KremationController.php (String-ID Support, Standort-Filter, getNextNumber() APIs)
├── HerkunftController.php (getByStandortName() API)
├── UserController.php (Multi-Location)
├── ConfigController.php (Konfigurationsseite)
└── ...
```

### Services
```
app/Services/
├── KremationService.php (String-ID Support, einaescherungsdatum null support)
├── AuditService.php (int|string recordId)
├── QRCodeService.php (Konfigurierbar)
└── PDFLabelService.php (Konfigurierbar, dynamisches Layout für A7)
```

### Views
```
resources/views/kremation/
├── index.php (Standort-Filter, Abschließen/Rückgängig, Tierarten-Anzeige)
└── ...
```

## 🔧 Code-Qualität

### PHPStan
- **Level:** 7
- **Status:** ✅ Keine Fehler
- **Letzte Prüfung:** 2025-02-04

### GitHub Actions
- **Status:** ✅ Läuft erfolgreich
- **Pipeline:** Tests, PHPStan, Pint
- **Memory Limit:** 512M für PHPStan

## 🗄️ Datenbank-Struktur

### Kremation
- `vorgangs_id`: VARCHAR(20) PRIMARY KEY (Format: LAU001, SCH002, etc.)
- `standort_id`: Foreign Key
- `herkunft_id`: Foreign Key
- `eingangsdatum`: DATE
- `gewicht`: DECIMAL(8,2)
- `einaescherungsdatum`: DATETIME (nullable)
- `created_by`: Foreign Key (users.id)
- `deleted_at`: DATETIME (nullable, Soft Delete)

### Kremation_Tiere (Pivot Table)
- `kremation_id`: VARCHAR(20) (String Foreign Key zu vorgangs_id)
- `tierart_id`: Foreign Key
- `anzahl`: INTEGER
- **Wichtig:** Explizite Definition von `kremation_id` als String mit Foreign Key Constraint

### User_Standort (Pivot Table)
- `user_id`: Foreign Key
- `standort_id`: Foreign Key
- `created_at`: DATETIME
- `updated_at`: DATETIME
- Primary Key: `(user_id, standort_id)`

### Users
- `default_standort_id`: Foreign Key (nullable)
- `standort_id`: Foreign Key (nullable, deprecated, für Backward Compatibility)

### Audit_Log
- `record_id`: VARCHAR(50) (unterstützt int und string)

## 🚀 Nächste Schritte / Offene Punkte

### Optional (nicht kritisch):
1. **Tests erweitern:**
   - Unit-Tests für Prefix-Generierung
   - Feature-Tests für Nummernkreis-System
   - API-Tests für Herkunft-Filterung
   - Tests für Standort-Filter
   - Tests für Kremation-Abschluss/Rückgängig

2. **Dokumentation:**
   - API-Dokumentation für neue Endpoints
   - Migration-Guide für bestehende Installationen

3. **UI-Verbesserungen:**
   - Weitere Filter-Optionen (Status, Datum, Herkunft)
   - Bulk-Aktionen für Kremationen

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
- `kremation_tiere.kremation_id` ist String und muss explizit mit Foreign Key definiert sein

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

### Standort-Filter
- URL-Parameter: `?standort=X` (X = standort_id)
- Filterlogik:
  - Wenn Standort ausgewählt: Filtert nach diesem Standort (nur wenn User Zugriff hat)
  - Wenn kein Filter: Zeigt alle erlaubten Standorte (`forAllowedStandorte`)
- JavaScript: `applyStandortFilter(standortId)` aktualisiert URL und lädt Seite neu

### Kremation-Abschluss
- **Abschließen:** Setzt `einaescherungsdatum` auf aktuelle Datum/Zeit
- **Rückgängig:** Setzt `einaescherungsdatum` auf `null`
- Button ist immer sichtbar, aber deaktiviert wenn bereits abgeschlossen
- "Rückgängig machen"-Button nur im Bearbeitungsformular sichtbar

### Tierarten-Relationship
- **Wichtig:** Direkter Aufruf `$k->tierarten()->get()` in der View für robusteres Laden
- Pivot-Daten-Zugriff: Mehrere Fallback-Methoden (`getAttribute`, Property, Array)
- Foreign Key: `kremation_id` (String) → `vorgangs_id` (String)
- Local Key: `vorgangs_id`, Related Key: `tierart_id`

### PDF-Layout
- Erkennt automatisch kleine Formate (A7, A6, A5)
- Block-basiertes Layout für kleine Formate (vertikale Anordnung)
- Table-basiertes Layout für größere Formate (A4)
- QR-Code-Einbettung: Mehrere Strategien für robustes Einfügen

## 🔐 Sicherheit

- Alle API-Routes sind geschützt (authentifiziert)
- Zugriffsprüfung für Standorte (non-Admin User)
- CSRF-Schutz für Formulare
- Input-Validierung in Services

## 🎨 UI/UX

- Inline-Editing: User, Herkunft, Standort
- Custom Modals: Delete-Confirmations (keine Browser-alerts)
- Konsistente Button-Größen und Styling (w-10 h-10)
- Dynamische Formular-Elemente (Herkunft-Dropdown, Next-Number-Display)
- Loading-States für API-Calls
- Standort-Filter oberhalb der Tabelle
- Action-Buttons: Nur Icons, einheitliche Größe
- Abschließen-Button: Immer sichtbar, deaktiviert wenn abgeschlossen

## 📊 Migration-Status

- ✅ Alle Migrations ausgeführt
- ✅ Bestehende Daten migriert (LAU001, LAU002, USI001, etc.)
- ✅ Neue Installationen verwenden direkt String-Format
- ✅ `kremation_tiere` Pivot-Tabelle korrigiert (String kremation_id)

## 🔄 Letzte Änderungen (2025-02-04)

1. **Standort-Filter für Kremation-Tabelle hinzugefügt**
   - Dropdown-Filter oberhalb der Tabelle
   - Filtert nach ausgewähltem Standort
   - Unterstützt URL-Parameter `?standort=X`

2. **Kremation-Abschluss und Rückgängig**
   - Abschließen-Button (✅) immer sichtbar, deaktiviert wenn abgeschlossen
   - Rückgängig machen-Button (🔄) im Edit-Formular
   - Backend unterstützt `null` für `einaescherungsdatum`

3. **Tierarten-Anzeige korrigiert**
   - Pivot-Daten werden jetzt korrekt geladen
   - Explizite Definition von local/related keys in Relationship
   - Direkter Aufruf `tierarten()->get()` in View

4. **PDF-Layout für A7 verbessert**
   - Block-basiertes Layout für kleine Formate
   - QR-Code-Einbettung robuster gemacht
   - Dynamische Anpassung aller Größen

---

**Für Fragen oder Probleme:** Siehe README.md oder CONTRIBUTING.md
