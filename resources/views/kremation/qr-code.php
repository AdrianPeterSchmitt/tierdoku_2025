<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-Code - <?= htmlspecialchars($kremation->vorgangs_id) ?> - Tierdokumentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen flex items-center justify-center px-4">

<div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-8 shadow-2xl max-w-md w-full">
    <h2 class="text-2xl font-bold text-center mb-6">QR-Code</h2>
    
    <div class="space-y-4">
        <!-- Vorgangs-ID -->
        <div class="text-center">
            <p class="text-sm text-gray-400 mb-2">Vorgang Nr.</p>
            <p class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
                #<?= $kremation->vorgangs_id ?>
            </p>
        </div>

        <!-- QR Code Image -->
        <div class="flex justify-center bg-white p-4 rounded-lg">
            <img src="data:image/png;base64,<?= $qrBase64 ?>" alt="QR-Code" class="w-64 h-64">
        </div>

        <!-- Info -->
        <div class="space-y-2 text-center text-sm">
            <p class="text-gray-400">Standort: <span class="text-white font-semibold"><?= htmlspecialchars($kremation->standort->name ?? 'Unbekannt') ?></span></p>
            <p class="text-gray-400">Eingang: <span class="text-white font-semibold"><?= $kremation->eingangsdatum?->format('d.m.Y') ?></span></p>
            <p class="text-gray-400">Gewicht: <span class="text-white font-semibold"><?= number_format($kremation->gewicht, 2, ',', '.') ?> kg</span></p>
        </div>

        <!-- Buttons -->
        <div class="flex gap-2 no-print">
            <button onclick="window.print()" class="flex-1 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg transition">
                🖨️ Drucken
            </button>
            <a href="/kremation" class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-lg transition text-center">
                Zurück
            </a>
        </div>
    </div>
</div>

</body>
</html>


