<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-Code Scannen - Dokumentation der anonymen Tiere</title>
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
<div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-8 shadow-2xl w-full">
    <h2 class="text-2xl font-bold text-center mb-2">QR-Code Scannen (Schnell)</h2>
    <p class="text-center text-gray-400 mb-2">Scannen Sie das Etikett auf dem Beutel vor der Verbrennung</p>
    <p class="text-center text-sm text-green-400 mb-6">⚡ Automatischer Abschluss - Perfekt für einzelne Beutel</p>
    
    <div class="space-y-4">
    <!-- Scanner Area -->
    <div id="qr-reader" class="mb-4"></div>
    <div id="error-message" class="hidden mt-4 p-4 bg-red-900/20 border border-red-500/50 rounded-lg text-red-300"></div>
    <button id="start-camera-btn" class="mt-4 w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg transition">
        📷 Kamera starten
    </button>

        <!-- Result Display -->
        <div id="qr-result" class="hidden p-4 bg-gray-900 rounded-lg border border-gray-700"></div>

        <!-- Flash Message -->
        <div id="flash-message" class="mb-4 hidden"></div>

        <!-- Back Button -->
        <div class="text-center">
            <a href="/kremation" class="inline-block px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-lg transition">
                Zurück zur Übersicht
            </a>
        </div>
    </div>
</div>

<script>
let html5QrcodeScanner = null;

// Initialize scanner
document.addEventListener('DOMContentLoaded', function() {
    html5QrcodeScanner = new Html5Qrcode("qr-reader");
    
    // Button Event Listener
    document.getElementById('start-camera-btn').addEventListener('click', startScanning);
    
    // Automatisch starten (optional)
    // startScanning();
});

function startScanning() {
    const button = document.getElementById('start-camera-btn');
    const errorDiv = document.getElementById('error-message');
    
    if (button) {
        button.disabled = true;
        button.textContent = '⏳ Kamera wird gestartet...';
    }
    
    errorDiv?.classList.add('hidden');
    
    // Versuche zuerst environment (Rückkamera auf Handy), dann user (Front-Kamera/Webcam)
    const cameraConfigs = [
        { facingMode: "environment" },  // Rückkamera (Handy)
        { facingMode: "user" }           // Front-Kamera / Webcam (Desktop)
    ];
    
    let configIndex = 0;
    
    function tryStartCamera() {
        const config = cameraConfigs[configIndex];
        
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
            if (button) {
                button.classList.add('hidden');
            }
            if (errorDiv) {
                errorDiv.classList.add('hidden');
            }
        }).catch((err) => {
            // Fehler - versuche nächste Kamera
            configIndex++;
            
            if (configIndex < cameraConfigs.length) {
                // Versuche nächste Kamera-Konfiguration
                tryStartCamera();
            } else {
                // Alle Kameras versucht - zeige Fehler
                showCameraError(err);
            }
        });
    }
    
    tryStartCamera();
}

function showCameraError(error) {
    const button = document.getElementById('start-camera-btn');
    const errorDiv = document.getElementById('error-message');
    
    let errorText = 'Kamera konnte nicht gestartet werden. ';
    
    if (error.message) {
        errorText += error.message;
    } else {
        errorText += 'Bitte stellen Sie sicher, dass die Webcam verbunden ist und der Browser Zugriff hat.';
    }
    
    errorText += '<br><small>Hinweis: Kamera-Zugriff wird nur über HTTPS oder localhost gewährt.</small>';
    
    if (errorDiv) {
        errorDiv.innerHTML = errorText;
        errorDiv.classList.remove('hidden');
    }
    
    if (button) {
        button.disabled = false;
        button.textContent = '📷 Kamera erneut starten';
    }
}

function onScanSuccess(decodedText, decodedResult) {
    // Stop scanning
    html5QrcodeScanner.stop().then(() => {
        console.log('QR Code scanning stopped');
    }).catch(() => {
        // Ignore
    });

    // Process QR data
    processQRData(decodedText);
}

function onScanFailure(error) {
    // Ignore scan errors (kontinuierliches Scannen)
}

function processQRData(qrData) {
    const flashMsg = document.getElementById('flash-message');
    const resultDiv = document.getElementById('qr-result');

    // Parse QR data
    let data;
    try {
        data = JSON.parse(qrData);
    } catch (e) {
        flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
        flashMsg.textContent = 'Ungültige QR-Daten';
        flashMsg.classList.remove('hidden');
        restartScanner();
        return;
    }

    // Validate data
    if (!data.vorgangs_id) {
        flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
        flashMsg.textContent = 'QR-Code enthält keine Kremationsdaten';
        flashMsg.classList.remove('hidden');
        restartScanner();
        return;
    }

    // Send to server
    fetch('/kremation/scan/process', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'qr_data=' + encodeURIComponent(qrData)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Show success
            flashMsg.className = 'mb-4 p-4 rounded-lg border border-green-500/50 bg-green-900/20 text-green-300';
            flashMsg.textContent = 'QR-Code erfolgreich gelesen!';
            flashMsg.classList.remove('hidden');

            // Display kremation info
            const k = result.kremation;
            
            // Wenn Kremation noch offen ist, automatisch abschließen
            if (!k.is_completed) {
                // Automatisch abschließen
                completeKremation(k.vorgangs_id, k, resultDiv, flashMsg);
            } else {
                // Bereits abgeschlossen - nur anzeigen
                showKremationInfo(k, resultDiv, flashMsg);
                setTimeout(() => {
                    restartScanner();
                }, 3000);
            }
        } else {
            flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
            flashMsg.textContent = result.error || 'Fehler beim Verarbeiten des QR-Codes';
            flashMsg.classList.remove('hidden');
            restartScanner();
        }
    })
    .catch(error => {
        flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
        flashMsg.textContent = 'Fehler: ' + error.message;
        flashMsg.classList.remove('hidden');
        restartScanner();
    });
}

function completeKremation(vorgangsId, kremation, resultDiv, flashMsg) {
    // Zeige Info während des Abschlusses
    resultDiv.innerHTML = `
        <div class="space-y-2">
            <h3 class="text-lg font-bold">Kremation gefunden</h3>
            <p><strong>Vorgang Nr.:</strong> #${vorgangsId}</p>
            <p><strong>Standort:</strong> ${kremation.standort}</p>
            <p class="text-yellow-400">⏳ Wird abgeschlossen...</p>
        </div>
    `;
    resultDiv.classList.remove('hidden');
    
    // Abschluss-Request senden
    fetch('/kremation/complete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ vorgang: vorgangsId })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            // Erfolg - Kremation abgeschlossen
            flashMsg.className = 'mb-4 p-4 rounded-lg border border-green-500/50 bg-green-900/20 text-green-300';
            flashMsg.textContent = '✅ Kremation #' + vorgangsId + ' wurde erfolgreich abgeschlossen!';
            flashMsg.classList.remove('hidden');
            
            // Zeige Erfolgs-Info
            resultDiv.innerHTML = `
                <div class="space-y-2">
                    <h3 class="text-lg font-bold text-green-400">✅ Kremation abgeschlossen</h3>
                    <p><strong>Vorgang Nr.:</strong> #${vorgangsId}</p>
                    <p><strong>Standort:</strong> ${kremation.standort}</p>
                    <p><strong>Eingangsdatum:</strong> ${kremation.eingangsdatum}</p>
                    <p><strong>Gewicht:</strong> ${kremation.gewicht} kg</p>
                    <p><strong>Einaescherungsdatum:</strong> ${result.date}</p>
                    <p class="text-sm text-gray-400 mt-4">Scanner startet automatisch neu...</p>
                </div>
            `;
            
            // Restart scanner after 2 seconds
            setTimeout(() => {
                restartScanner();
            }, 2000);
        } else {
            // Fehler beim Abschluss
            flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
            flashMsg.textContent = 'Fehler beim Abschluss: ' + (result.error || 'Unbekannter Fehler');
            flashMsg.classList.remove('hidden');
            
            // Zeige Info mit Fehler
            showKremationInfo(kremation, resultDiv, flashMsg);
            setTimeout(() => {
                restartScanner();
            }, 3000);
        }
    })
    .catch(error => {
        flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
        flashMsg.textContent = 'Fehler: ' + error.message;
        flashMsg.classList.remove('hidden');
        showKremationInfo(kremation, resultDiv, flashMsg);
        setTimeout(() => {
            restartScanner();
        }, 3000);
    });
}

function showKremationInfo(kremation, resultDiv, flashMsg) {
    resultDiv.innerHTML = `
        <div class="space-y-2">
            <h3 class="text-lg font-bold">Kremation gefunden</h3>
            <p><strong>Vorgang Nr.:</strong> #${kremation.vorgangs_id}</p>
            <p><strong>Standort:</strong> ${kremation.standort}</p>
            <p><strong>Eingangsdatum:</strong> ${kremation.eingangsdatum}</p>
            <p><strong>Gewicht:</strong> ${kremation.gewicht} kg</p>
            <p><strong>Status:</strong> ${kremation.is_completed ? '✅ Abgeschlossen' : '⏳ Offen'}</p>
            <div class="mt-4">
                <a href="${kremation.url}" class="inline-block px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg transition">
                    Zur Kremation
                </a>
            </div>
        </div>
    `;
    resultDiv.classList.remove('hidden');
}

function restartScanner() {
    setTimeout(() => {
        document.getElementById('qr-result')?.classList.add('hidden');
        document.getElementById('flash-message')?.classList.add('hidden');
        
        if (html5QrcodeScanner) {
            // Verwende user (Webcam) als Standard beim Restart
            html5QrcodeScanner.start(
                { facingMode: "user" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                },
                onScanSuccess,
                onScanFailure
            ).catch((err) => {
                // Falls user nicht funktioniert, versuche environment
                html5QrcodeScanner.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 },
                        aspectRatio: 1.0
                    },
                    onScanSuccess,
                    onScanFailure
                ).catch((err2) => {
                    console.error('Kamera konnte nicht neu gestartet werden:', err2);
                });
            });
        }
    }, 1000);
}
</script>

</div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>

</body>
</html>


