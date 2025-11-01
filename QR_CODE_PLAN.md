# 📱 QR-Code-System - Implementierungsplan

## 🎯 Ziel
Ein vollständiges QR-Code-System für die Tierkremations-Verwaltung, das es ermöglicht:
- QR-Codes für jede Kremation zu generieren
- QR-Codes zu scannen (Einzel- und Batch-Modus)
- QR-Codes in PDF-Labels zu integrieren
- Mobile Unterstützung für Scanner auf Tablets/Smartphones

---

## ✅ Implementiert (Stand: Vollständig)

### 1. Backend - QR-Code-Service
**Datei:** `app/Services/QRCodeService.php`

#### Funktionen:
- ✅ **generateForKremation()** - Generiert QR-Code als Base64-PNG
  - Parameter: `Kremation $kremation`, `int $size = 300`
  - Rückgabe: Base64-kodiertes PNG-Bild
  - Verwendet: Endroid QR Code Library

- ✅ **saveForKremation()** - Speichert QR-Code als Datei
  - Parameter: `Kremation $kremation`, `string $filepath`, `int $size = 300`
  - Rückgabe: `bool` (Erfolg)
  - Erstellt Verzeichnis falls nicht vorhanden

- ✅ **parseQRData()** - Parst QR-Code-Daten
  - Parameter: `string $qrData` (JSON-String)
  - Rückgabe: `array|null`
  - Validiert: Enthält `vorgangs_id`?

- ✅ **buildQRData()** - Baut QR-Datenstruktur
  - Erstellt JSON mit:
    - `vorgangs_id` - Vorgangs-ID
    - `standort` - Standort-Name
    - `eingangsdatum` - Format: Y-m-d
    - `gewicht` - Gewicht in kg
    - `timestamp` - ISO 8601 Format

#### Konfiguration:
- **Library:** `endroid/qr-code` (^6.0)
- **Encoding:** UTF-8
- **Error Correction:** High
- **Format:** PNG
- **Margin:** 10 Pixel

---

### 2. Backend - Controller-Methoden
**Datei:** `app/Controllers/KremationController.php`

#### Methoden:
- ✅ **showQRCode()** - Zeigt QR-Code-Seite
  - Route: `/kremation/{id}/qr`
  - HTTP: GET
  - Funktionalität:
    - Lädt Kremation nach ID
    - Prüft Berechtigungen (Standort-basiert)
    - Generiert QR-Code (400x400px)
    - Rendert View `kremation/qr-code`

- ✅ **scanQRCode()** - Zeigt Scanner-Seite
  - Route: `/kremation/scan`
  - HTTP: GET
  - Funktionalität:
    - Rendert View `kremation/scan`
    - HTML5-Kamera-Scanner

- ✅ **batchScanQRCode()** - Zeigt Batch-Scanner
  - Route: `/kremation/batch-scan`
  - HTTP: GET
  - Funktionalität:
    - Rendert View `kremation/batch-scan`
    - Mehrere Kremationen scannen
    - Bulk-Actions (Ansicht, Abschließen)

- ✅ **processScannedQR()** - Verarbeitet gescannten QR-Code
  - Route: `/kremation/scan/process`
  - HTTP: POST
  - Parameter: `qr_data` (POST)
  - Funktionalität:
    - Parst QR-Daten
    - Validiert QR-Daten
    - Lädt Kremation
    - Gibt JSON zurück:
      ```json
      {
        "success": true,
        "kremation": {
          "vorgangs_id": 123,
          "standort": "Laudenbach",
          "eingangsdatum": "01.02.2025",
          "gewicht": 15.5,
          "is_completed": false,
          "url": "/kremation?id=123"
        }
      }
      ```

---

### 3. Frontend - Views

#### ✅ **qr-code.php** - QR-Code-Anzeige
**Datei:** `resources/views/kremation/qr-code.php`

**Features:**
- QR-Code-Bild (64x64 Tailwind-Einheiten = ~256px)
- Vorgangs-ID (groß, zentriert)
- Kremations-Details (Standort, Eingangsdatum, Gewicht)
- Druckfunktion (Print-Button, CSS: `@media print`)
- Zurück-Button

**Styling:**
- Dark Theme
- Gradient für Vorgangs-ID
- Zentrierte Anordnung
- Print-Optimierung

---

#### ✅ **scan.php** - Einzel-Scanner
**Datei:** `resources/views/kremation/scan.php`

**Features:**
- HTML5-Kamera-Scanner (`html5-qrcode` Library)
- Live-Scanning (10 FPS)
- Kamera-Auswahl: Back-Kamera bevorzugt
- Scan-Box: 250x250 Pixel
- Auto-Stop nach erfolgreichem Scan
- Ergebnis-Anzeige:
  - Vorgangs-ID
  - Standort
  - Eingangsdatum
  - Gewicht
  - Status (Offen/Abgeschlossen)
  - Link zur Kremation
- Auto-Restart nach 3 Sekunden
- Flash-Messages (Erfolg/Fehler)

**JavaScript-Funktionen:**
- `onScanSuccess()` - Verarbeitet gescannten QR-Code
- `processQRData()` - Sendet Daten an Server
- `restartScanner()` - Startet Scanner neu

---

#### ✅ **batch-scan.php** - Batch-Scanner
**Datei:** `resources/views/kremation/batch-scan.php`

**Features:**
- HTML5-Kamera-Scanner (wie Einzel-Scanner)
- Scan-Liste: Alle gescannten Kremationen
- Duplikat-Erkennung: Verhindert mehrfaches Scannen
- Bulk-Actions:
  - **Ansicht-Modus**: Sammelt Kremationen zur Ansicht
  - **Abschließen-Modus**: Automatisches Abschließen aller gescannten Kremationen
- Scan-Counter: Anzahl gescannter Kremationen
- Liste-Verwaltung:
  - Entfernen einzelner Einträge
  - Liste leeren
- Batch-Abschluss: Alle Kremationen mit einem Klick abschließen
- Status-Feedback: Erfolg/Fehler pro Kremation

**JavaScript-Funktionen:**
- `addToScannedList()` - Fügt Kremation zur Liste hinzu
- `updateScannedList()` - Aktualisiert Liste im DOM
- `removeFromList()` - Entfernt Kremation aus Liste
- `processAllKremations()` - Verarbeitet alle gescannten Kremationen
- `completeAllKremations()` - Schließt alle Kremationen ab
- `showSuccess()`, `showError()`, `showWarning()` - Status-Messages

---

### 4. Routing
**Datei:** `config/routes.php`

#### ✅ Implementierte Routen:
```php
// QR-Code anzeigen
'/kremation/{id}/qr' => [KremationController::class, 'showQRCode'],

// Scanner-Seiten
'/kremation/scan' => [KremationController::class, 'scanQRCode'],
'/kremation/batch-scan' => [KremationController::class, 'batchScanQRCode'],

// QR-Code verarbeiten
'/kremation/scan/process' => [KremationController::class, 'processScannedQR'],
```

---

### 5. UI-Integration

#### ✅ Navigation
**Datei:** `resources/views/partials/nav.php`
- Link: "QR-Scanner" im Hauptmenü
- Route: `/kremation/scan`

#### ✅ Kremation-Übersicht
**Datei:** `resources/views/kremation/index.php`
- QR-Code-Button für jede Kremation
- Link: `/kremation/{id}/qr`
- Ziel: Neues Fenster (`target="_blank"`)
- Icon: 📱 QR

---

### 6. PDF-Integration
**Datei:** `app/Services/PDFLabelService.php`

#### ✅ generateLabelWithQR()
- Parameter: `Kremation $kremation`, `string $qrCodeBase64`
- Funktionalität:
  - Ersetzt QR-Placeholder im HTML
  - Fügt Base64-Bild ein (60mm x 60mm)
  - Generiert PDF mit QR-Code

#### QR-Code im PDF:
- Größe: 60mm x 60mm
- Position: Unten im Label
- Rahmen: 2px schwarz
- Hintergrund: Weiß

---

### 7. Dependency Injection
**Datei:** `config/container.php`

#### ✅ Service-Registrierung:
```php
$container->singleton(QRCodeService::class, function ($container) {
    return new QRCodeService();
});
```

#### ✅ Controller-Injection:
```php
$container->singleton(KremationController::class, function ($container) {
    return new KremationController(
        // ... andere Services ...
        $container->get(QRCodeService::class),
    );
});
```

---

## 📋 QR-Code-Datenstruktur

### JSON-Format:
```json
{
  "vorgangs_id": 123,
  "standort": "Laudenbach",
  "eingangsdatum": "2025-02-01",
  "gewicht": 15.5,
  "timestamp": "2025-02-01T10:30:00+01:00"
}
```

### Encoding:
- Format: JSON
- Encoding: UTF-8
- Error Correction: High (30%)
- Margin: 10 Pixel

---

## 🔒 Sicherheit & Berechtigungen

### Implementiert:
- ✅ **Standort-basierte Berechtigungen**
  - Admin: Kann alle QR-Codes anzeigen
  - Manager/Mitarbeiter: Nur eigene Standorte

- ✅ **Validierung**
  - QR-Daten werden geparst und validiert
  - `vorgangs_id` wird überprüft
  - Kremation-Existenz wird geprüft

- ✅ **Error Handling**
  - Fehlerhafte QR-Daten werden abgefangen
  - Nicht gefundene Kremationen werden gemeldet
  - User-freundliche Fehlermeldungen

---

## 📱 Mobile Support

### Implementiert:
- ✅ **Responsive Design**
  - TailwindCSS Utility Classes
  - Mobile-first Approach

- ✅ **HTML5-Kamera**
  - Verwendet `html5-qrcode` Library
  - Automatische Kamera-Auswahl (Back-Kamera bevorzugt)
  - Touch-Gesten unterstützt

- ✅ **Mobile-Optimierungen**
  - Scan-Box: 250x250 Pixel (optimal für Mobile)
  - FPS: 10 (Balance zwischen Performance und Genauigkeit)
  - Vibrations-Feedback (bei Batch-Scan)

---

## 🧪 Testing (Optional)

### Noch nicht implementiert:
- ❌ Unit Tests für QRCodeService
- ❌ Feature Tests für Scanner-Views
- ❌ Integration Tests für QR-Flow

### Empfohlene Tests:
```php
// Unit Test: QRCodeService
public function testGenerateQRCode()
public function testParseQRData()
public function testBuildQRData()

// Feature Test: QR-Code-Anzeige
public function testShowQRCode()
public function testScanQRCode()

// Integration Test: Scan-Flow
public function testProcessScannedQR()
```

---

## 🚀 Zukünftige Erweiterungen (Optional)

### Mögliche Features:
1. **QR-Code-Download** - QR-Code als PNG herunterladen
2. **QR-Code-Größen** - Verschiedene Größen wählbar (Klein, Mittel, Groß)
3. **QR-Code-Stil** - Farbige QR-Codes, Logo-Integration
4. **QR-Code-Historie** - Logging wann QR-Codes gescannt wurden
5. **Offline-Scanner** - Progressive Web App für Offline-Nutzung
6. **Barcode-Support** - Zusätzlich zu QR-Codes auch Barcodes
7. **QR-Code-Bulk-Generierung** - Mehrere QR-Codes auf einmal generieren

---

## 📊 Implementierungs-Status

| Feature | Status | Priorität |
|---------|--------|-----------|
| QR-Code-Generierung | ✅ Fertig | Hoch |
| QR-Code-Anzeige | ✅ Fertig | Hoch |
| Einzel-Scanner | ✅ Fertig | Hoch |
| Batch-Scanner | ✅ Fertig | Mittel |
| PDF-Integration | ✅ Fertig | Hoch |
| Mobile Support | ✅ Fertig | Hoch |
| Berechtigungen | ✅ Fertig | Hoch |
| Error Handling | ✅ Fertig | Mittel |
| Tests | ❌ Offen | Niedrig |

---

## 🎯 Zusammenfassung

**Status:** ✅ **VOLLSTÄNDIG IMPLEMENTIERT**

Alle geplanten Features des QR-Code-Systems sind implementiert und funktionsfähig:
- ✅ Backend-Service komplett
- ✅ Alle Controller-Methoden vorhanden
- ✅ Alle Views implementiert
- ✅ Routing konfiguriert
- ✅ UI-Integration vorhanden
- ✅ PDF-Integration funktioniert
- ✅ Mobile Support gegeben

**Das QR-Code-System ist produktionsbereit!** 🚀

