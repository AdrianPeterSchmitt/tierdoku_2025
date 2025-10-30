<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiken - Tierdokumentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen">

<?php require $GLOBALS['viewBasePath'] . 'partials/nav.php'; ?>

<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
    
    <!-- Filter -->
    <section class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
        <h2 class="text-xl font-bold mb-4">Filter</h2>
        <form method="GET" action="/statistics" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Von</label>
                <input type="date" name="from" value="<?= htmlspecialchars($dateFrom) ?>" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Bis</label>
                <input type="date" name="to" value="<?= htmlspecialchars($dateTo) ?>" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
            </div>
            <?php if ($user->isAdmin()): ?>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Standort</label>
                <select name="standort_id" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
                    <option value="">Alle</option>
                    <?php foreach ($standorte as $s): ?>
                    <option value="<?= $s->standort_id ?>" <?= $currentStandort == $s->standort_id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s->name) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg transition">
                    Filtern
                </button>
            </div>
        </form>
    </section>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/20 backdrop-blur border border-blue-500/50 rounded-2xl p-6 shadow-2xl">
            <h3 class="text-gray-300 text-sm mb-2">Gesamt Kremationen</h3>
            <p class="text-4xl font-bold text-blue-400"><?= $stats['totalCount'] ?></p>
        </div>
        <div class="bg-gradient-to-br from-yellow-500/20 to-yellow-600/20 backdrop-blur border border-yellow-500/50 rounded-2xl p-6 shadow-2xl">
            <h3 class="text-gray-300 text-sm mb-2">Offen</h3>
            <p class="text-4xl font-bold text-yellow-400"><?= $stats['openCount'] ?></p>
        </div>
        <div class="bg-gradient-to-br from-green-500/20 to-green-600/20 backdrop-blur border border-green-500/50 rounded-2xl p-6 shadow-2xl">
            <h3 class="text-gray-300 text-sm mb-2">Abgeschlossen</h3>
            <p class="text-4xl font-bold text-green-400"><?= $stats['completedCount'] ?></p>
        </div>
        <div class="bg-gradient-to-br from-purple-500/20 to-purple-600/20 backdrop-blur border border-purple-500/50 rounded-2xl p-6 shadow-2xl">
            <h3 class="text-gray-300 text-sm mb-2">Ø Gewicht</h3>
            <p class="text-4xl font-bold text-purple-400"><?= number_format($stats['averageWeight'], 2, ',', '.') ?></p>
            <p class="text-sm text-gray-400 mt-1">kg (Gesamt: <?= number_format($stats['totalWeight'], 2, ',', '.') ?> kg)</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Timeline Chart -->
        <section class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-4">Timeline (letzte 30 Tage)</h2>
            <canvas id="timelineChart"></canvas>
        </section>

        <!-- Tierarten Chart -->
        <section class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-4">Verteilung nach Tierart</h2>
            <canvas id="tierartChart"></canvas>
        </section>

        <!-- Standort Chart -->
        <?php if (count($stats['byStandort']) > 0): ?>
        <section class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-4">Verteilung nach Standort</h2>
            <canvas id="standortChart"></canvas>
        </section>
        <?php endif; ?>

        <!-- Herkunft Chart -->
        <?php if (count($stats['byHerkunft']) > 0): ?>
        <section class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-4">Top 10 Herkunft</h2>
            <canvas id="herkunftChart"></canvas>
        </section>
        <?php endif; ?>
    </div>

    <!-- Status Pie Chart -->
    <?php if ($stats['totalCount'] > 0): ?>
    <section class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
        <h2 class="text-xl font-bold mb-4">Status-Verteilung</h2>
        <div class="max-w-md mx-auto">
            <canvas id="statusChart"></canvas>
        </div>
    </section>
    <?php endif; ?>

</div>

<script>
// Dark theme colors
const darkTheme = {
    background: 'rgba(31, 41, 55, 0.5)',
    text: '#e5e7eb',
    grid: 'rgba(107, 114, 128, 0.3)'
};

// Timeline Chart
const timelineData = <?= json_encode($stats['timeline']) ?>;
const timelineCtx = document.getElementById('timelineChart');
if (timelineCtx) {
    new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: timelineData.map(d => d.date),
            datasets: [{
                label: 'Kremationen',
                data: timelineData.map(d => d.count),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: darkTheme.text },
                    grid: { color: darkTheme.grid }
                },
                x: {
                    ticks: { color: darkTheme.text, maxRotation: 45, minRotation: 45 },
                    grid: { color: darkTheme.grid }
                }
            }
        }
    });
}

// Tierart Chart
const tierartData = <?= json_encode($stats['byTierart']) ?>;
const tierartCtx = document.getElementById('tierartChart');
if (tierartCtx && tierartData.length > 0) {
    new Chart(tierartCtx, {
        type: 'bar',
        data: {
            labels: tierartData.map(d => d.name),
            datasets: [{
                label: 'Anzahl',
                data: tierartData.map(d => d.count),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(167, 139, 250, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(251, 191, 36, 0.8)',
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: darkTheme.text },
                    grid: { color: darkTheme.grid }
                },
                x: {
                    ticks: { color: darkTheme.text },
                    grid: { color: darkTheme.grid }
                }
            }
        }
    });
}

// Standort Chart
const standortData = <?= json_encode($stats['byStandort']) ?>;
const standortCtx = document.getElementById('standortChart');
if (standortCtx && standortData.length > 0) {
    new Chart(standortCtx, {
        type: 'doughnut',
        data: {
            labels: standortData.map(d => d.name),
            datasets: [{
                data: standortData.map(d => d.count),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(167, 139, 250, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: darkTheme.text, padding: 15 }
                }
            }
        }
    });
}

// Herkunft Chart
const herkunftData = <?= json_encode($stats['byHerkunft']) ?>;
const herkunftCtx = document.getElementById('herkunftChart');
if (herkunftCtx && herkunftData.length > 0) {
    new Chart(herkunftCtx, {
        type: 'bar',
        data: {
            labels: herkunftData.map(d => d.name),
            datasets: [{
                label: 'Anzahl',
                data: herkunftData.map(d => d.count),
                backgroundColor: 'rgba(139, 92, 246, 0.8)',
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    ticks: { color: darkTheme.text },
                    grid: { color: darkTheme.grid }
                },
                x: {
                    beginAtZero: true,
                    ticks: { color: darkTheme.text },
                    grid: { color: darkTheme.grid }
                }
            }
        }
    });
}

// Status Chart
const statusCtx = document.getElementById('statusChart');
if (statusCtx) {
    const openCount = <?= $stats['openCount'] ?>;
    const completedCount = <?= $stats['completedCount'] ?>;
    
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Offen', 'Abgeschlossen'],
            datasets: [{
                data: [openCount, completedCount],
                backgroundColor: [
                    'rgba(251, 191, 36, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: darkTheme.text, padding: 15 }
                }
            }
        }
    });
}
</script>

</body>
</html>
