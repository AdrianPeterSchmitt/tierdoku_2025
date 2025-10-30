<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-Code Scannen - Tierdokumentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen flex items-center justify-center px-4">

<div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-8 shadow-2xl max-w-2xl w-full">
    <h2 class="text-2xl font-bold text-center mb-6">QR-Code Scannen</h2>
    
    <div class="space-y-4">
        <!-- Scanner Area -->
        <div id="qr-reader" class="mb-4"></div>

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
        // Ignore scan errors
    }

    // Start scanning with back camera
    html5QrcodeScanner.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: { width: 250, height: 250 }
        },
        onScanSuccess,
        onScanFailure
    );
});

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
            resultDiv.innerHTML = `
                <div class="space-y-2">
                    <h3 class="text-lg font-bold">Kremation gefunden</h3>
                    <p><strong>Vorgang Nr.:</strong> #${k.vorgangs_id}</p>
                    <p><strong>Standort:</strong> ${k.standort}</p>
                    <p><strong>Eingangsdatum:</strong> ${k.eingangsdatum}</p>
                    <p><strong>Gewicht:</strong> ${k.gewicht} kg</p>
                    <p><strong>Status:</strong> ${k.is_completed ? '✅ Abgeschlossen' : '⏳ Offen'}</p>
                    <div class="mt-4">
                        <a href="${k.url}" class="inline-block px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg transition">
                            Zur Kremation
                        </a>
                    </div>
                </div>
            `;
            resultDiv.classList.remove('hidden');

            // Restart scanner after 3 seconds
            setTimeout(() => {
                restartScanner();
            }, 3000);
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

function restartScanner() {
    setTimeout(() => {
        document.getElementById('qr-result').classList.add('hidden');
        document.getElementById('flash-message').classList.add('hidden');
        
        if (html5QrcodeScanner) {
            html5QrcodeScanner.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                onScanSuccess,
                onScanFailure
            );
        }
    }, 1000);
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
    // Ignore scan errors
}
</script>

</body>
</html>


