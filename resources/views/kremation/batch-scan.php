<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch QR-Scan - Dokumentation der anonymen Tiere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        #qr-reader {
            width: 100%;
            max-width: 600px;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen">

<?php require $GLOBALS['viewBasePath'] . 'partials/nav.php'; ?>

<div class="w-full px-4 py-8">
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-center mb-2">QR-Code Batch-Scan</h2>
        <p class="text-center text-gray-400">Scannen Sie mehrere Kremationen nacheinander</p>
    </div>

    <!-- Scanner -->
    <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 mb-6">
        <div id="qr-reader" class="mb-4"></div>
        <div id="scan-status" class="text-center text-sm text-gray-400">
            Bereit zum Scannen...
        </div>
        <div id="error-message" class="hidden mt-4 p-4 bg-red-900/20 border border-red-500/50 rounded-lg text-red-300"></div>
        <button id="start-camera-btn" class="mt-4 w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg transition">
            📷 Kamera starten
        </button>
    </div>

    <!-- Info -->
    <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/20 backdrop-blur border border-blue-500/50 rounded-2xl p-6 mb-6">
        <h3 class="text-lg font-bold mb-2 text-blue-400">📦 Batch-Modus</h3>
        <p class="text-sm text-gray-300 mb-3">
            Scannen Sie mehrere Beutel-Etiketten nacheinander. Alle gescannten Kremationen werden in der Liste gesammelt und können zusammen abgeschlossen werden.
        </p>
        <div class="flex items-center gap-2 text-sm text-gray-400">
            <span>✅ Duplikate werden automatisch erkannt</span>
            <span>✅ Scanner läuft kontinuierlich weiter</span>
            <span>✅ Liste kann jederzeit geleert werden</span>
        </div>
    </div>

    <!-- Gescannte Liste -->
    <div id="scanned-list" class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Gescannte Kremationen (<span id="scan-count" class="text-blue-400">0</span>)</h3>
            <button id="process-all" class="px-6 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg transition hidden">
                ✓ Alle abarbeiten
            </button>
        </div>
        <div id="scanned-items" class="space-y-2">
            <p class="text-gray-500 text-center py-8">Noch keine Kremationen gescannt</p>
        </div>
    </div>

    <!-- Aktionen -->
    <div class="mt-6 flex gap-2">
        <button id="clear-list" class="flex-1 px-4 py-2 bg-red-500/20 hover:bg-red-500/30 text-red-300 font-bold rounded-lg transition">
            Liste leeren
        </button>
        <a href="/kremation" class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-lg text-center transition">
            Zurück
        </a>
    </div>
</div>

<script>
let scannedKremations = [];
let html5QrcodeScanner = null;
let isProcessing = false;

// Scanner initialisieren
function initScanner() {
    html5QrcodeScanner = new Html5Qrcode("qr-reader");
    
    // Button Event Listener
    document.getElementById('start-camera-btn').addEventListener('click', startScanning);
    
    // Automatisch starten (optional - falls Browser es erlaubt)
    // startScanning();
}

function startScanning() {
    const button = document.getElementById('start-camera-btn');
    const errorDiv = document.getElementById('error-message');
    const statusDiv = document.getElementById('scan-status');
    
    // Verstecke Fehlermeldung
    if (errorDiv) errorDiv.classList.add('hidden');
    
    // Update Status
    if (statusDiv) {
        statusDiv.textContent = 'Kamera wird gestartet...';
        statusDiv.className = 'text-center text-sm text-yellow-400';
    }
    if (button) {
        button.disabled = true;
        button.textContent = '⏳ Wird geladen...';
    }
    
    // Prüfe ob HTML5Qrcode verfügbar ist
    if (typeof Html5Qrcode === 'undefined') {
        showCameraError({
            name: 'LibraryError',
            message: 'HTML5-QRCode Library wurde nicht geladen. Bitte die Seite neu laden.'
        });
        return;
    }
    
    // Prüfe ob Scanner initialisiert ist
    if (!html5QrcodeScanner) {
        try {
            html5QrcodeScanner = new Html5Qrcode("qr-reader");
        } catch (err) {
            showCameraError({
                name: 'InitializationError',
                message: 'Scanner konnte nicht initialisiert werden: ' + err.message
            });
            return;
        }
    }
    
    // Versuche zuerst user (Webcam/Desktop), dann environment (Rückkamera/Handy)
    const cameraConfigs = [
        { facingMode: "user" },           // Front-Kamera / Webcam (Desktop) - sollte zuerst versucht werden
        { facingMode: "environment" }    // Rückkamera (Handy)
    ];
    
    let configIndex = 0;
    let lastError = null;
    
    function tryStartCamera() {
        const config = cameraConfigs[configIndex];
        
        console.log('Versuche Kamera zu starten mit Config:', config);
        
        html5QrcodeScanner.start(
            config,
            { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            },
            onScanSuccess,
            onScanFailure
        ).then(() => {
            // Erfolg - Kamera gestartet
            console.log('Kamera erfolgreich gestartet');
            if (statusDiv) {
                statusDiv.textContent = 'Kamera aktiv - Bereit zum Scannen';
                statusDiv.className = 'text-center text-sm text-green-400';
            }
            if (button) button.classList.add('hidden');
            if (errorDiv) errorDiv.classList.add('hidden');
        }).catch((err) => {
            // Fehler - speichere Fehler
            console.error('Kamera-Start fehlgeschlagen:', err);
            lastError = err;
            
            // Versuche nächste Kamera
            configIndex++;
            
            if (configIndex < cameraConfigs.length) {
                console.log('Versuche nächste Kamera-Konfiguration...');
                tryStartCamera();
            } else {
                // Alle Kameras versucht - zeige Fehler
                console.error('Alle Kamera-Konfigurationen fehlgeschlagen');
                showCameraError(lastError || new Error('Kamera konnte nicht gestartet werden'));
            }
        });
    }
    
    tryStartCamera();
}

function showCameraError(error) {
    const button = document.getElementById('start-camera-btn');
    const errorDiv = document.getElementById('error-message');
    const statusDiv = document.getElementById('scan-status');
    
    statusDiv.textContent = 'Kamera-Fehler';
    statusDiv.className = 'text-center text-sm text-red-400';
    
    let errorText = 'Kamera konnte nicht gestartet werden.<br>';
    
    // Detaillierte Fehlermeldung
    if (error) {
        console.error('Kamera-Fehler:', error);
        
        if (error.message) {
            errorText += '<strong>Fehler:</strong> ' + error.message + '<br>';
        }
        
        if (error.name) {
            errorText += '<strong>Typ:</strong> ' + error.name + '<br>';
        }
    } else {
        errorText += 'Keine Fehlerdetails verfügbar.<br>';
    }
    
    // Prüfe Browser-Features
    errorText += '<br><strong>Diagnose:</strong><br>';
    
    if (!navigator.mediaDevices) {
        errorText += '❌ MediaDevices API nicht verfügbar<br>';
    } else {
        errorText += '✅ MediaDevices API verfügbar<br>';
    }
    
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        errorText += '❌ getUserMedia nicht verfügbar<br>';
    } else {
        errorText += '✅ getUserMedia verfügbar<br>';
    }
    
    // Prüfe URL
    const isLocalhost = window.location.hostname === 'localhost' || 
                       window.location.hostname === '127.0.0.1' ||
                       window.location.hostname === '';
    const isHttps = window.location.protocol === 'https:';
    
    if (isLocalhost || isHttps) {
        errorText += '✅ URL erlaubt Kamera-Zugriff (localhost oder HTTPS)<br>';
    } else {
        errorText += '❌ URL erlaubt keinen Kamera-Zugriff (nur localhost oder HTTPS)<br>';
        errorText += '<small>Die URL ist: ' + window.location.href + '</small><br>';
    }
    
    errorText += '<br><small>Hinweise:<br>';
    errorText += '- Stellen Sie sicher, dass die Webcam verbunden ist<br>';
    errorText += '- Erlauben Sie den Browser den Zugriff auf die Kamera<br>';
    errorText += '- Prüfen Sie, ob andere Anwendungen die Kamera verwenden<br>';
    errorText += '- Verwenden Sie Chrome, Firefox oder Edge (moderne Browser)<br>';
    errorText += '</small>';
    
    errorDiv.innerHTML = errorText;
    errorDiv.classList.remove('hidden');
    
    button.disabled = false;
    button.textContent = '📷 Kamera erneut starten';
}

function onScanSuccess(decodedText) {
    if (isProcessing) return;
    isProcessing = true;
    
    // Vibrieren bei Erfolg
    if (navigator.vibrate) {
        navigator.vibrate(200);
    }
    
    // QR-Daten parsen
    let data;
    try {
        data = JSON.parse(decodedText);
    } catch (e) {
        showError('Ungültige QR-Daten');
        isProcessing = false;
        return;
    }
    
    // Prüfen ob bereits gescannt
    if (scannedKremations.find(k => k.vorgangs_id === data.vorgangs_id)) {
        showWarning('Kremation bereits gescannt');
        isProcessing = false;
        return;
    }
    
    // Kremation vom Server holen
    fetch('/kremation/scan/process', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'qr_data=' + encodeURIComponent(decodedText)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            addToScannedList(result.kremation);
            showSuccess('✓ Kremation #' + result.kremation.vorgangs_id + ' hinzugefügt');
            
            // Im Batch-Modus weiterscannen
            setTimeout(() => {
                isProcessing = false;
            }, 500);
        } else {
            showError(result.error);
            isProcessing = false;
        }
    })
    .catch(error => {
        showError('Fehler: ' + error.message);
        isProcessing = false;
    });
}

function onScanFailure(error) {
    // Ignore scan errors
}

function addToScannedList(kremation) {
    scannedKremations.push(kremation);
    updateScannedList();
}

function updateScannedList() {
    const container = document.getElementById('scanned-items');
    const count = document.getElementById('scan-count');
    count.textContent = scannedKremations.length;
    
    if (scannedKremations.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">Noch keine Kremationen gescannt</p>';
        document.getElementById('process-all').classList.add('hidden');
        return;
    }
    
    container.innerHTML = scannedKremations.map((k, index) => `
        <div class="flex items-center justify-between p-4 bg-gray-900/50 rounded-lg border border-gray-700 hover:bg-gray-900/70 transition">
            <div class="flex-1">
                <p class="font-bold text-lg">#${k.vorgangs_id}</p>
                <p class="text-sm text-gray-400">${k.standort} • ${k.gewicht} kg • ${k.is_completed ? '✅ Abgeschlossen' : '⏳ Offen'}</p>
            </div>
            <button onclick="removeFromList(${index})" class="px-3 py-1 bg-red-500/20 hover:bg-red-500/30 text-red-300 rounded-lg text-sm font-medium transition">
                Entfernen
            </button>
        </div>
    `).join('');
    
    document.getElementById('process-all').classList.remove('hidden');
}

function removeFromList(index) {
    scannedKremations.splice(index, 1);
    updateScannedList();
}

// Event Listeners
document.getElementById('clear-list').addEventListener('click', () => {
    if (confirm('Wirklich alle gescannten Kremationen aus der Liste entfernen?')) {
        scannedKremations = [];
        updateScannedList();
    }
});

document.getElementById('process-all').addEventListener('click', processAllKremations);

function processAllKremations() {
    if (scannedKremations.length === 0) {
        alert('Keine Kremationen zum Abschließen vorhanden.');
        return;
    }
    
    // Zähle nur offene Kremationen
    const openCount = scannedKremations.filter(k => !k.is_completed).length;
    
    if (openCount === 0) {
        alert('Alle Kremationen sind bereits abgeschlossen.');
        return;
    }
    
    if (!confirm(`${openCount} Kremation${openCount > 1 ? 'en' : ''} abschließen?\n\nAlle Kremationen erhalten das aktuelle Datum und Uhrzeit als Einäscherungsdatum.`)) {
        return;
    }
    
    completeAllKremations();
}

async function completeAllKremations() {
    const button = document.getElementById('process-all');
    button.disabled = true;
    button.textContent = 'Wird verarbeitet...';
    button.classList.add('opacity-50', 'cursor-not-allowed');
    
    let completed = 0;
    let failed = 0;
    
    for (const kremation of scannedKremations) {
        if (kremation.is_completed) {
            continue; // Bereits abgeschlossen
        }
        
        try {
            const response = await fetch('/kremation/complete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ vorgang: kremation.vorgangs_id })
            });
            
            const result = await response.json();
            if (result.success) {
                completed++;
            } else {
                failed++;
            }
        } catch (error) {
            failed++;
        }
    }
    
    const message = `Abgeschlossen: ${completed}\nFehler: ${failed}\n\n${failed === 0 ? '✅ Alle Kremationen wurden erfolgreich abgeschlossen!' : '⚠️ Einige Kremationen konnten nicht abgeschlossen werden.'}`;
    alert(message);
    
    // Liste leeren
    scannedKremations = [];
    updateScannedList();
    
    button.disabled = false;
    button.textContent = '✓ Alle abarbeiten';
    button.classList.remove('opacity-50', 'cursor-not-allowed');
}

// Hilfsfunktionen
function showSuccess(message) {
    updateStatus(message, 'text-green-400');
}

function showError(message) {
    updateStatus(message, 'text-red-400');
}

function showWarning(message) {
    updateStatus(message, 'text-yellow-400');
}

function updateStatus(message, className) {
    const status = document.getElementById('scan-status');
    status.textContent = message;
    status.className = 'text-center text-sm ' + className;
    setTimeout(() => {
        status.textContent = 'Bereit zum Scannen...';
        status.className = 'text-center text-sm text-gray-400';
    }, 2000);
}

// Init
document.addEventListener('DOMContentLoaded', initScanner);
</script>

</div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>

</body>
</html>


