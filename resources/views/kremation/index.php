<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kremationen - Tierdokumentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen">

<div x-data="kremationApp()">
    <!-- Header -->
    <?php require $GLOBALS['viewBasePath'] . 'partials/nav.php'; ?>
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">
                        Tierkremationen
                    </h1>
                    <span class="px-3 py-1 bg-blue-500/20 text-blue-300 rounded-lg font-bold text-sm">
                        #<?= $nextNr ?>
                    </span>
                    <?php if (!$user->isAdmin()): ?>
                    <div class="px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-bold shadow-lg">
                        <?= htmlspecialchars($user->standort->name ?? 'Unbekannt') ?>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 py-6 space-y-6">
        
        <!-- Erfassungs-Formular -->
        <section class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                📝 Neue Kremation erfassen
            </h2>
            
            <form method="post" action="/kremation" class="space-y-6" id="kremation-form">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-9 gap-4">
                    <!-- Eingangsdatum -->
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Eingangsdatum *</label>
                        <input 
                            type="date" 
                            name="Eingangsdatum" 
                            value="<?= date('Y-m-d') ?>"
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>

                    <!-- Standort -->
                    <div class="lg:col-span-1">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Standort *</label>
                        <select 
                            name="Standort" 
                            x-model="standort"
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                            <option value="">-</option>
                            <?php foreach ($standorte as $s): ?>
                            <option value="<?= htmlspecialchars($s->name) ?>" <?= !$user->isAdmin() && $user->standort_id == $s->standort_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s->name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Herkunft -->
                    <div class="lg:col-span-2 xl:col-span-1">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Herkunft *</label>
                        <select 
                            name="Herkunft" 
                            x-model="herkunft"
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                            <option value="">-</option>
                            <?php foreach ($herkuenfte as $h): ?>
                            <option value="<?= htmlspecialchars($h->name) ?>"><?= htmlspecialchars($h->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tierarten -->
                    <?php foreach ($tierarten as $ta): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <?= htmlspecialchars($ta->bezeichnung) ?>
                        </label>
                        <div class="flex items-center gap-2">
                            <button 
                                type="button" 
                                @click="updateTierCount('<?= $ta->bezeichnung ?>', -1)"
                                class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition"
                            >-</button>
                            <input 
                                type="number" 
                                name="Anzahl_<?= $ta->bezeichnung ?>" 
                                :value="tierCounts['<?= $ta->bezeichnung ?>']"
                                min="0"
                                class="w-20 px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white text-center"
                                readonly
                            >
                            <button 
                                type="button" 
                                @click="updateTierCount('<?= $ta->bezeichnung ?>', 1)"
                                class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg transition"
                            >+</button>
                        </div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Gewicht -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Gewicht (kg) *</label>
                        <input 
                            type="text" 
                            name="Gewicht" 
                            placeholder="z.B. 6,50"
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-end">
                        <button 
                            type="submit"
                            class="w-full px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-lg transition shadow-lg"
                        >
                            Speichern
                        </button>
                    </div>
                </div>

                <!-- Gesamtzahl Anzeige -->
                <div class="text-sm text-gray-400">
                    Gesamt: <span class="font-bold" x-text="totalAnimals()"></span> Tier(e)
                </div>
            </form>
        </section>

        <!-- Tabelle (simplified for now) -->
        <section class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
            <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                📋 Letzte Einträge
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Vorgang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Eingang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Herkunft</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Gewicht</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Standort</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kremations as $k): ?>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/30">
                            <td class="px-4 py-3 font-mono font-bold text-blue-400">
                                #<?= $k->vorgangs_id ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?= $k->eingangsdatum->format('d.m.Y') ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?= htmlspecialchars($k->herkunft->name ?? '') ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-mono">
                                <?= number_format($k->gewicht, 2, ',', '') ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?php if ($k->einaescherungsdatum): ?>
                                    <span class="text-green-400">Abgeschlossen</span>
                                <?php else: ?>
                                    <span class="text-yellow-400">Offen</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?= htmlspecialchars($k->standort->name ?? '') ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex gap-1">
                                    <a href="/kremation/<?= $k->vorgangs_id ?>/qr" target="_blank" class="inline-block px-2 py-1 bg-green-500 hover:bg-green-600 text-white rounded transition text-xs">
                                        📱 QR
                                    </a>
                                    <a href="/kremation/<?= $k->vorgangs_id ?>/label" class="inline-block px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded transition text-xs">
                                        📄 PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (count($kremations) === 0): ?>
            <p class="text-center text-gray-400 py-8">Keine Kremationen gefunden.</p>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($currentPage > 1 || $hasMore): ?>
            <div class="mt-6 flex justify-center gap-2">
                <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg transition">
                    ← Zurück
                </a>
                <?php endif; ?>
                
                <span class="px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg">
                    Seite <?= $currentPage ?>
                </span>
                
                <?php if ($hasMore): ?>
                <a href="?page=<?= $currentPage + 1 ?>" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 rounded-lg transition">
                    Weiter →
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </section>
    </main>
</div>

<script>
function kremationApp() {
    return {
        showFilters: false,
        standort: localStorage.getItem('lastStandort') || '<?= !$user->isAdmin() ? htmlspecialchars($user->standort->name ?? '') : '' ?>',
        herkunft: localStorage.getItem('lastHerkunft') || '',
        tierCounts: {
            'Vogel': 0,
            'Heimtier': 0,
            'Katze': 0,
            'Hund': 0
        },
        
        init() {
            // Save standort/herkunft to localStorage on change
            this.$watch('standort', value => {
                if (value) localStorage.setItem('lastStandort', value);
            });
            
            this.$watch('herkunft', value => {
                if (value) localStorage.setItem('lastHerkunft', value);
            });
        },
        
        updateTierCount(tierart, delta) {
            this.tierCounts[tierart] = Math.max(0, (this.tierCounts[tierart] || 0) + delta);
        },
        
        totalAnimals() {
            return Object.values(this.tierCounts).reduce((sum, count) => sum + (count || 0), 0);
        }
    }
}
</script>

</body>
</html>

