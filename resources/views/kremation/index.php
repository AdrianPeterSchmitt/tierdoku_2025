<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kremationen - Dokumentation der anonymen Tiere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        
        /* Date/DateTime picker icon color - match text color */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
        
        /* Firefox date picker icon */
        input[type="date"]::-moz-calendar-picker-indicator,
        input[type="datetime-local"]::-moz-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
        }
        
        /* Time input 24-hour format styling */
        input[type="time"] {
            font-variant-numeric: tabular-nums;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen">

<div x-data="kremationApp()">
    <!-- Header -->
    <?php require $GLOBALS['viewBasePath'] . 'partials/nav.php'; ?>
        <div class="w-full px-4 py-4">
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

    <main class="w-full px-4 py-6 space-y-6">
        
        <!-- Erfassungs-Formular -->
        <section class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
            <div class="mb-6">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <span x-show="!isEditMode">📝 Neue Kremation erfassen</span>
                    <span x-show="isEditMode">✏️ Kremation bearbeiten</span>
                </h2>
            </div>
            
            <form method="post" action="/kremation" class="space-y-6" id="kremation-form">
                <input type="hidden" name="vorgangs_id" x-model="editingKremationId">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Eingangsdatum -->
                    <div>
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
                    <div>
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
                    <div class="md:col-span-2 lg:col-span-2">
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
                                max="99"
                                class="w-14 px-2 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white text-center"
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
                </div>
                
                <!-- Gesamtzahl Anzeige -->
                <div class="text-sm text-gray-400 mt-2 mb-4">
                    Gesamt: <span class="font-bold" x-text="totalAnimals()"></span> Tier(e)
                </div>
                
                <!-- Einäscherungsdatum (nur im Edit-Modus) - Außerhalb des Grids für besseres Layout -->
                <div x-show="isEditMode" class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Kremation (Datum & Uhrzeit)</label>
                    <div class="flex flex-col sm:flex-row gap-3 max-w-2xl">
                        <div class="flex-1">
                            <input 
                                type="date"
                                id="einaescherungsdatum-date"
                                class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                            >
                        </div>
                        <div class="flex items-center gap-2">
                            <input 
                                type="number"
                                id="einaescherungsdatum-hour"
                                min="0"
                                max="23"
                                placeholder="HH"
                                class="w-16 sm:w-20 px-2 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white text-center font-mono text-lg"
                                oninput="updateTimeFromInputs()"
                                style="min-width: 64px;"
                            >
                            <span class="text-gray-400 font-bold text-xl">:</span>
                            <input 
                                type="number"
                                id="einaescherungsdatum-minute"
                                min="0"
                                max="59"
                                placeholder="MM"
                                class="w-16 sm:w-20 px-2 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white text-center font-mono text-lg"
                                oninput="updateTimeFromInputs()"
                                style="min-width: 64px;"
                            >
                        </div>
                    </div>
                    <input 
                        type="hidden"
                        name="Einaescherungsdatum" 
                        id="einaescherungsdatum-hidden"
                    >
                </div>

                <!-- Submit Button - Am Ende des Formulars -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-700/50">
                    <button 
                        type="button"
                        x-show="isEditMode"
                        @click="resetForm()"
                        class="px-8 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition shadow disabled:opacity-50 disabled:cursor-not-allowed w-[150px]"
                    >
                        Abbrechen
                    </button>
                    <button 
                        type="submit"
                        :disabled="saving"
                        class="px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed w-[150px]"
                    >
                        <span x-show="!saving && !isEditMode">Speichern</span>
                        <span x-show="!saving && isEditMode">Aktualisieren</span>
                        <span x-show="saving">Wird gespeichert...</span>
                    </button>
                </div>
                
                <!-- Success/Error Message -->
                <div x-show="message" 
                     x-transition
                     :class="messageType === 'success' ? 'bg-green-500/20 border-green-500 text-green-300' : 'bg-red-500/20 border-red-500 text-red-300'"
                     class="px-4 py-3 rounded-lg border mt-4">
                    <span x-text="message"></span>
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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Standort</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-400 uppercase w-12">V</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-400 uppercase w-12">He</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-400 uppercase w-12">K</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-400 uppercase w-12">Hu</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Gewicht</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Kremation</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kremations as $k): ?>
                        <tr class="border-b border-gray-800 hover:bg-gray-800/30" data-vorgang-id="<?= $k->vorgangs_id ?>">
                            <td class="px-4 py-3 font-mono font-bold text-blue-400">
                                #<?= $k->vorgangs_id ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?= $k->eingangsdatum->format('d.m.Y') ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?= htmlspecialchars($k->herkunft->name ?? '') ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?= htmlspecialchars($k->standort->name ?? '') ?>
                            </td>
                            <?php
                            // Create map of tierarten for easy lookup
                            $tierartenMap = [];
                            foreach ($k->tierarten as $tierart) {
                                $tierartenMap[$tierart->bezeichnung] = $tierart->pivot->anzahl ?? 0;
                            }
                            ?>
                            <td class="px-2 py-3 text-sm text-center font-mono w-12">
                                <?= $tierartenMap['Vogel'] ?? 0 ?>
                            </td>
                            <td class="px-2 py-3 text-sm text-center font-mono w-12">
                                <?= $tierartenMap['Heimtier'] ?? 0 ?>
                            </td>
                            <td class="px-2 py-3 text-sm text-center font-mono w-12">
                                <?= $tierartenMap['Katze'] ?? 0 ?>
                            </td>
                            <td class="px-2 py-3 text-sm text-center font-mono w-12">
                                <?= $tierartenMap['Hund'] ?? 0 ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-right font-mono">
                                <?= number_format($k->gewicht, 2, ',', '') ?> kg
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?php if ($k->einaescherungsdatum): ?>
                                    <span class="text-green-400">Abgeschlossen</span>
                                <?php else: ?>
                                    <span class="text-yellow-400">Offen</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <?php if ($k->einaescherungsdatum): ?>
                                    <?= $k->einaescherungsdatum->format('d.m.Y H:i') ?> Uhr
                                <?php else: ?>
                                    <span class="text-gray-500">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex gap-1">
                                    <button 
                                        @click="loadKremationForEdit(<?= $k->vorgangs_id ?>)"
                                        class="inline-block px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded transition text-xs"
                                    >
                                        ✏️ Bearbeiten
                                    </button>
                                    <?php if (!$k->einaescherungsdatum): ?>
                                    <button 
                                        @click="completeKremation(<?= $k->vorgangs_id ?>)"
                                        class="inline-block px-2 py-1 bg-purple-500 hover:bg-purple-600 text-white rounded transition text-xs"
                                        title="Kremation abschließen"
                                    >
                                        ✅ Abschließen
                                    </button>
                                    <?php endif; ?>
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
        
        saving: false,
        message: '',
        messageType: 'success',
        isEditMode: false,
        editingKremationId: null,
        lastUpdate: null,
        pollingInterval: null,
        isPolling: false,
        newUpdatesCount: 0,
        
        init() {
            // Ensure edit mode is reset on page load
            this.isEditMode = false;
            this.editingKremationId = null;
            
            // Save standort/herkunft to localStorage on change
            this.$watch('standort', value => {
                if (value) localStorage.setItem('lastStandort', value);
            });
            
            this.$watch('herkunft', value => {
                if (value) localStorage.setItem('lastHerkunft', value);
            });
            
            // Handle form submission
            const form = document.getElementById('kremation-form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    const self = this;
                    self.handleSubmit(form);
                });
            }
            
            // Initialize last update timestamp
            this.lastUpdate = new Date().toISOString().slice(0, 19).replace('T', ' ');
            
            // Start polling for updates
            this.startPolling();
            
            // Handle page visibility changes
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.stopPolling();
                } else {
                    this.startPolling();
                    // Immediate check when tab becomes visible
                    this.checkForUpdates();
                }
            });
        },
        
        async handleSubmit(form) {
            this.saving = true;
            this.message = '';
            
            try {
                // Update readonly input values before submitting
                Object.keys(this.tierCounts).forEach(tierart => {
                    const input = form.querySelector(`input[name="Anzahl_${tierart}"]`);
                    if (input) {
                        input.value = this.tierCounts[tierart] || 0;
                    }
                });
                
                // Aktualisiere verstecktes Feld für FormData
                updateTimeFromInputs();
                
                const formData = new FormData(form);
                
                // Use POST for both create and update (PHP handles FormData better with POST)
                // We'll use the route parameter to distinguish
                const url = this.isEditMode 
                    ? `/kremation/${this.editingKremationId}/update-full` 
                    : '/kremation';
                
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.message = data.message;
                    this.messageType = 'success';
                    
                    // Reset form
                    this.resetForm();
                    form.reset();
                    
                    // Update lastUpdate and check for updates
                    this.lastUpdate = new Date().toISOString().slice(0, 19).replace('T', ' ');
                    this.checkForUpdates();
                    
                    // Reload page after a short delay to show updated entry
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.message = data.error || 'Fehler beim Speichern';
                    this.messageType = 'error';
                    this.saving = false;
                }
            } catch (error) {
                this.message = 'Ein Fehler ist aufgetreten: ' + error.message;
                this.messageType = 'error';
                this.saving = false;
            }
        },
        
        updateTierCount(tierart, delta) {
            const current = this.tierCounts[tierart] || 0;
            const newValue = current + delta;
            this.tierCounts[tierart] = Math.max(0, Math.min(99, newValue));
        },
        
        totalAnimals() {
            return Object.values(this.tierCounts).reduce((sum, count) => sum + (count || 0), 0);
        },
        
        async loadKremationForEdit(id) {
            try {
                // Extract data from table row
                const row = document.querySelector(`tr[data-vorgang-id="${id}"]`);
                if (!row) {
                    throw new Error('Datensatz nicht gefunden');
                }
                
                const cells = row.querySelectorAll('td');
                if (cells.length < 12) {
                    throw new Error('Ungültige Tabellenstruktur');
                }
                
                // Extract data from cells
                const eingangsdatum = cells[1].textContent.trim(); // Format: dd.mm.yyyy
                const herkunft = cells[2].textContent.trim();
                const standort = cells[3].textContent.trim();
                const vogel = parseInt(cells[4].textContent.trim()) || 0;
                const heimtier = parseInt(cells[5].textContent.trim()) || 0;
                const katze = parseInt(cells[6].textContent.trim()) || 0;
                const hund = parseInt(cells[7].textContent.trim()) || 0;
                const gewicht = cells[8].textContent.trim().replace(' kg', '').replace(',', '.');
                const kremation = cells[10].textContent.trim(); // May contain date or "-"
                
                // Convert date format from dd.mm.yyyy to yyyy-mm-dd
                const dateParts = eingangsdatum.split('.');
                const eingangsdatumFormatted = dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];
                
                // Populate form
                const form = document.getElementById('kremation-form');
                form.querySelector('input[name="Eingangsdatum"]').value = eingangsdatumFormatted;
                form.querySelector('select[name="Standort"]').value = standort;
                form.querySelector('select[name="Herkunft"]').value = herkunft;
                form.querySelector('input[name="Gewicht"]').value = gewicht.replace('.', ',');
                
                // Set tier counts
                this.tierCounts = {
                    'Vogel': vogel,
                    'Heimtier': heimtier,
                    'Katze': katze,
                    'Hund': hund
                };
                
                // Set Einäscherungsdatum if available (Date + Hour/Minute Inputs)
                const dateInput = document.getElementById('einaescherungsdatum-date');
                const hourInput = document.getElementById('einaescherungsdatum-hour');
                const minuteInput = document.getElementById('einaescherungsdatum-minute');
                const hiddenInput = document.getElementById('einaescherungsdatum-hidden');
                
                if (dateInput && hourInput && minuteInput && hiddenInput) {
                    if (kremation !== '-' && kremation) {
                        // Parse format: dd.mm.yyyy HH:mm Uhr -> yyyy-mm-dd, HH und mm
                        const kremationMatch = kremation.match(/(\d{2})\.(\d{2})\.(\d{4})\s+(\d{2}):(\d{2})/);
                        if (kremationMatch) {
                            const [, day, month, year, hour, minute] = kremationMatch;
                            
                            // Setze Date und Time separat
                            dateInput.value = `${year}-${month}-${day}`;
                            hourInput.value = hour.padStart(2, '0');
                            minuteInput.value = minute.padStart(2, '0');
                            hiddenInput.value = `${year}-${month}-${day}T${hour.padStart(2, '0')}:${minute.padStart(2, '0')}`;
                        }
                    } else {
                        // Clear Einäscherungsdatum if not set
                        dateInput.value = '';
                        hourInput.value = '';
                        minuteInput.value = '';
                        hiddenInput.value = '';
                    }
                }
                
                // Set edit mode
                this.isEditMode = true;
                this.editingKremationId = id;
                
                // Initialisiere nach kurzer Verzögerung
                setTimeout(() => {
                    updateTimeFromInputs();
                }, 100);
                
                // Stop polling while editing
                this.stopPolling();
                
                // Update Alpine.js models
                this.standort = standort;
                this.herkunft = herkunft;
                
                // Scroll to form
                document.querySelector('#kremation-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (error) {
                this.message = 'Fehler beim Laden: ' + error.message;
                this.messageType = 'error';
            }
        },
        
        resetForm() {
            const form = document.getElementById('kremation-form');
            form.reset();
            
            this.isEditMode = false;
            this.editingKremationId = null;
            
            // Resume polling when edit mode is exited
            if (!document.hidden) {
                this.startPolling();
            }
            
            // Reset tier counts
            this.tierCounts = {
                'Vogel': 0,
                'Heimtier': 0,
                'Katze': 0,
                'Hund': 0
            };
            
            // Reset Alpine.js models
            this.standort = localStorage.getItem('lastStandort') || '<?= !$user->isAdmin() ? htmlspecialchars($user->standort->name ?? '') : '' ?>';
            this.herkunft = localStorage.getItem('lastHerkunft') || '';
            
            // Set default date
            const today = new Date().toISOString().split('T')[0];
            form.querySelector('input[name="Eingangsdatum"]').value = today;
            
            // Clear Einäscherungsdatum (Date + Hour/Minute Inputs)
            const dateInput = document.getElementById('einaescherungsdatum-date');
            const hourInput = document.getElementById('einaescherungsdatum-hour');
            const minuteInput = document.getElementById('einaescherungsdatum-minute');
            const hiddenInput = document.getElementById('einaescherungsdatum-hidden');
            
            if (dateInput) dateInput.value = '';
            if (hourInput) hourInput.value = '';
            if (minuteInput) minuteInput.value = '';
            if (hiddenInput) hiddenInput.value = '';
            
            // Clear message
            this.message = '';
        },
        
        async completeKremation(id) {
            try {
                const response = await fetch('/kremation/complete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ vorgang: id })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.message = `Kremation #${id} erfolgreich abgeschlossen.`;
                    this.messageType = 'success';
                    
                    // Update lastUpdate timestamp and check for updates
                    this.lastUpdate = data.date || new Date().toISOString().slice(0, 19).replace('T', ' ');
                    this.checkForUpdates();
                    
                    // Reload page after a short delay to show updated entry
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    this.message = data.error || 'Fehler beim Abschließen';
                    this.messageType = 'error';
                }
            } catch (error) {
                this.message = 'Ein Fehler ist aufgetreten: ' + error.message;
                this.messageType = 'error';
            }
        },
        
        startPolling() {
            // Don't start if already polling or in edit mode
            if (this.isPolling || this.isEditMode) {
                return;
            }
            
            this.isPolling = true;
            
            // Start polling every 30 seconds
            this.pollingInterval = setInterval(() => {
                if (!this.isEditMode && !document.hidden) {
                    this.checkForUpdates();
                }
            }, 30000);
        },
        
        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
            this.isPolling = false;
        },
        
        async checkForUpdates() {
            // Don't check if in edit mode or tab is hidden
            if (this.isEditMode || document.hidden) {
                return;
            }
            
            try {
                const url = this.lastUpdate 
                    ? `/kremation/updates?since=${encodeURIComponent(this.lastUpdate)}`
                    : '/kremation/updates';
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (data.success && data.count > 0) {
                    // Update lastUpdate timestamp
                    this.lastUpdate = data.lastUpdate;
                    
                    // Process updates
                    await this.processUpdates(data.updates);
                } else if (data.success) {
                    // No updates, but update timestamp anyway
                    this.lastUpdate = data.lastUpdate;
                }
            } catch (error) {
                // Silently fail - don't interrupt user
                console.error('Update check failed:', error);
            }
        },
        
        async processUpdates(updates) {
            const tbody = document.querySelector('tbody');
            if (!tbody) return;
            
            const existingIds = new Set(
                Array.from(tbody.querySelectorAll('tr[data-vorgang-id]'))
                    .map(row => parseInt(row.getAttribute('data-vorgang-id')))
            );
            
            let newCount = 0;
            
            for (const update of updates) {
                const vorgangId = update.vorgangs_id;
                const existingRow = tbody.querySelector(`tr[data-vorgang-id="${vorgangId}"]`);
                
                if (existingRow) {
                    // Update existing row
                    this.updateTableRow(existingRow, update);
                } else {
                    // Add new row at the beginning
                    const newRow = this.createTableRow(update);
                    tbody.insertBefore(newRow, tbody.firstChild);
                    newCount++;
                }
            }
            
            // Update counter if there are new entries
            if (newCount > 0) {
                this.newUpdatesCount += newCount;
                // Show subtle notification
                this.showUpdateNotification(newCount);
            }
        },
        
        createTableRow(kremation) {
            const row = document.createElement('tr');
            row.className = 'border-b border-gray-800 hover:bg-gray-800/30';
            row.setAttribute('data-vorgang-id', kremation.vorgangs_id);
            
            // Format dates
            const eingangsdatumParts = kremation.eingangsdatum.split('-');
            const eingangsdatum = `${eingangsdatumParts[2]}.${eingangsdatumParts[1]}.${eingangsdatumParts[0]}`;
            
            let kremationDate = '-';
            if (kremation.einaescherungsdatum) {
                const kremParts = kremation.einaescherungsdatum.split(' ');
                const kremDateParts = kremParts[0].split('-');
                const kremTime = kremParts[1] ? kremParts[1].substring(0, 5) : '00:00';
                kremationDate = `${kremDateParts[2]}.${kremDateParts[1]}.${kremDateParts[0]} ${kremTime} Uhr`;
            }
            
            // Create row content
            const vorgangCell = document.createElement('td');
            vorgangCell.className = 'px-4 py-3 font-mono font-bold text-blue-400';
            vorgangCell.textContent = '#' + kremation.vorgangs_id;
            
            const eingangCell = document.createElement('td');
            eingangCell.className = 'px-4 py-3 text-sm';
            eingangCell.textContent = eingangsdatum;
            
            const herkunftCell = document.createElement('td');
            herkunftCell.className = 'px-4 py-3 text-sm';
            herkunftCell.textContent = kremation.herkunft;
            
            const standortCell = document.createElement('td');
            standortCell.className = 'px-4 py-3 text-sm';
            standortCell.textContent = kremation.standort;
            
            const vogelCell = document.createElement('td');
            vogelCell.className = 'px-2 py-3 text-sm text-center font-mono w-12';
            vogelCell.textContent = kremation.vogel;
            
            const heimtierCell = document.createElement('td');
            heimtierCell.className = 'px-2 py-3 text-sm text-center font-mono w-12';
            heimtierCell.textContent = kremation.heimtier;
            
            const katzeCell = document.createElement('td');
            katzeCell.className = 'px-2 py-3 text-sm text-center font-mono w-12';
            katzeCell.textContent = kremation.katze;
            
            const hundCell = document.createElement('td');
            hundCell.className = 'px-2 py-3 text-sm text-center font-mono w-12';
            hundCell.textContent = kremation.hund;
            
            const gewichtCell = document.createElement('td');
            gewichtCell.className = 'px-4 py-3 text-sm text-right font-mono';
            gewichtCell.textContent = kremation.gewicht.toFixed(2).replace('.', ',') + ' kg';
            
            const statusCell = document.createElement('td');
            statusCell.className = 'px-4 py-3 text-sm';
            const statusSpan = document.createElement('span');
            statusSpan.className = kremation.status === 'Abgeschlossen' ? 'text-green-400' : 'text-yellow-400';
            statusSpan.textContent = kremation.status;
            statusCell.appendChild(statusSpan);
            
            const kremationCell = document.createElement('td');
            kremationCell.className = 'px-4 py-3 text-sm';
            if (kremation.kremation === '-') {
                const kremationSpan = document.createElement('span');
                kremationSpan.className = 'text-gray-500';
                kremationSpan.textContent = '-';
                kremationCell.appendChild(kremationSpan);
            } else {
                kremationCell.textContent = kremationDate;
            }
            
            const aktionenCell = document.createElement('td');
            aktionenCell.className = 'px-4 py-3 text-sm';
            const aktionenDiv = document.createElement('div');
            aktionenDiv.className = 'flex gap-1';
            
            // Bearbeiten Button
            const bearbeitenBtn = document.createElement('button');
            bearbeitenBtn.className = 'inline-block px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded transition text-xs';
            bearbeitenBtn.textContent = '✏️ Bearbeiten';
            bearbeitenBtn.onclick = (function(app, id) {
                return function() {
                    app.loadKremationForEdit(id);
                };
            })(this, kremation.vorgangs_id);
            aktionenDiv.appendChild(bearbeitenBtn);
            
            // Abschließen Button (nur wenn Offen)
            if (kremation.status === 'Offen') {
                const abschliessenBtn = document.createElement('button');
                abschliessenBtn.className = 'inline-block px-2 py-1 bg-purple-500 hover:bg-purple-600 text-white rounded transition text-xs';
                abschliessenBtn.title = 'Kremation abschließen';
                abschliessenBtn.textContent = '✅ Abschließen';
                abschliessenBtn.onclick = (function(app, id) {
                    return function() {
                        app.completeKremation(id);
                    };
                })(this, kremation.vorgangs_id);
                aktionenDiv.appendChild(abschliessenBtn);
            }
            
            // QR Link
            const qrLink = document.createElement('a');
            qrLink.href = `/kremation/${kremation.vorgangs_id}/qr`;
            qrLink.target = '_blank';
            qrLink.className = 'inline-block px-2 py-1 bg-green-500 hover:bg-green-600 text-white rounded transition text-xs';
            qrLink.textContent = '📱 QR';
            aktionenDiv.appendChild(qrLink);
            
            // PDF Link
            const pdfLink = document.createElement('a');
            pdfLink.href = `/kremation/${kremation.vorgangs_id}/label`;
            pdfLink.className = 'inline-block px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded transition text-xs';
            pdfLink.textContent = '📄 PDF';
            aktionenDiv.appendChild(pdfLink);
            
            aktionenCell.appendChild(aktionenDiv);
            
            // Append all cells to row
            row.appendChild(vorgangCell);
            row.appendChild(eingangCell);
            row.appendChild(herkunftCell);
            row.appendChild(standortCell);
            row.appendChild(vogelCell);
            row.appendChild(heimtierCell);
            row.appendChild(katzeCell);
            row.appendChild(hundCell);
            row.appendChild(gewichtCell);
            row.appendChild(statusCell);
            row.appendChild(kremationCell);
            row.appendChild(aktionenCell);
            
            return row;
        },
        
        updateTableRow(row, kremation) {
            const cells = row.querySelectorAll('td');
            if (cells.length < 12) return;
            
            // Format Eingangsdatum
            const eingangsdatumParts = kremation.eingangsdatum.split('-');
            const eingangsdatum = `${eingangsdatumParts[2]}.${eingangsdatumParts[1]}.${eingangsdatumParts[0]}`;
            
            // Update Eingangsdatum
            cells[1].textContent = eingangsdatum;
            
            // Update Herkunft
            cells[2].textContent = kremation.herkunft;
            
            // Update Standort
            cells[3].textContent = kremation.standort;
            
            // Update Tierarten
            cells[4].textContent = kremation.vogel;
            cells[5].textContent = kremation.heimtier;
            cells[6].textContent = kremation.katze;
            cells[7].textContent = kremation.hund;
            
            // Update Gewicht
            cells[8].textContent = kremation.gewicht.toFixed(2).replace('.', ',') + ' kg';
            
            // Update Status
            cells[9].innerHTML = '';
            const statusSpan = document.createElement('span');
            statusSpan.className = kremation.status === 'Abgeschlossen' ? 'text-green-400' : 'text-yellow-400';
            statusSpan.textContent = kremation.status;
            cells[9].appendChild(statusSpan);
            
            // Update Kremation
            cells[10].innerHTML = '';
            if (kremation.kremation === '-') {
                const kremationSpan = document.createElement('span');
                kremationSpan.className = 'text-gray-500';
                kremationSpan.textContent = '-';
                cells[10].appendChild(kremationSpan);
            } else {
                // Format kremation date
                let kremationDate = '-';
                if (kremation.einaescherungsdatum) {
                    const kremParts = kremation.einaescherungsdatum.split(' ');
                    const kremDateParts = kremParts[0].split('-');
                    const kremTime = kremParts[1] ? kremParts[1].substring(0, 5) : '00:00';
                    kremationDate = `${kremDateParts[2]}.${kremDateParts[1]}.${kremDateParts[0]} ${kremTime} Uhr`;
                }
                cells[10].textContent = kremationDate;
            }
            
            // Update Aktionen (especially the "Abschließen" button visibility)
            const aktionenDiv = cells[11].querySelector('div.flex');
            if (aktionenDiv) {
                const abschliessenBtn = aktionenDiv.querySelector('button[title="Kremation abschließen"]');
                
                if (kremation.status === 'Offen' && !abschliessenBtn) {
                    // Add "Abschließen" button if status changed to "Offen"
                    const bearbeitenBtn = aktionenDiv.querySelector('button[class*="bg-blue-500"]');
                    if (bearbeitenBtn) {
                        const newAbschliessenBtn = document.createElement('button');
                        newAbschliessenBtn.className = 'inline-block px-2 py-1 bg-purple-500 hover:bg-purple-600 text-white rounded transition text-xs';
                        newAbschliessenBtn.title = 'Kremation abschließen';
                        newAbschliessenBtn.textContent = '✅ Abschließen';
                        const self = this;
                        const vorgangId = kremation.vorgangs_id;
                        newAbschliessenBtn.onclick = function() {
                            self.completeKremation(vorgangId);
                        };
                        bearbeitenBtn.after(newAbschliessenBtn);
                    }
                } else if (kremation.status === 'Abgeschlossen' && abschliessenBtn) {
                    // Remove "Abschließen" button if status changed to "Abgeschlossen"
                    abschliessenBtn.remove();
                }
            }
        },
        
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
        
        showUpdateNotification(count) {
            // Create or update notification badge
            let badge = document.getElementById('update-badge');
            if (!badge) {
                badge = document.createElement('div');
                badge.id = 'update-badge';
                badge.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
                document.body.appendChild(badge);
            }
            
            badge.textContent = `${count} neue Einträge`;
            badge.classList.remove('hidden');
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                badge.classList.add('hidden');
                this.newUpdatesCount = 0;
            }, 5000);
        }
    }
}

// Moderne DateTime-Picker-Lösung: Date + separate Hour/Minute Inputs (24-Stunden-Format garantiert)
function updateTimeFromInputs() {
    const dateInput = document.getElementById('einaescherungsdatum-date');
    const hourInput = document.getElementById('einaescherungsdatum-hour');
    const minuteInput = document.getElementById('einaescherungsdatum-minute');
    const hiddenInput = document.getElementById('einaescherungsdatum-hidden');
    
    if (dateInput && hourInput && minuteInput && hiddenInput) {
        const date = dateInput.value;
        let hour = parseInt(hourInput.value) || 0;
        let minute = parseInt(minuteInput.value) || 0;
        
        // Stelle sicher, dass Werte im gültigen Bereich sind
        if (hour < 0) hour = 0;
        if (hour > 23) hour = 23;
        if (minute < 0) minute = 0;
        if (minute > 59) minute = 59;
        
        // Aktualisiere Input-Werte falls sie außerhalb des Bereichs waren
        hourInput.value = hour.toString().padStart(2, '0');
        minuteInput.value = minute.toString().padStart(2, '0');
        
        if (date) {
            const time = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
            hiddenInput.value = `${date}T${time}`;
        } else {
            hiddenInput.value = '';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('einaescherungsdatum-date');
    const hourInput = document.getElementById('einaescherungsdatum-hour');
    const minuteInput = document.getElementById('einaescherungsdatum-minute');
    
    // Event Listener für Date Input
    if (dateInput) {
        dateInput.addEventListener('change', updateTimeFromInputs);
    }
    
    // Event Listener für Hour und Minute Inputs
    if (hourInput) {
        hourInput.addEventListener('change', updateTimeFromInputs);
        hourInput.addEventListener('input', updateTimeFromInputs);
    }
    
    if (minuteInput) {
        minuteInput.addEventListener('change', updateTimeFromInputs);
        minuteInput.addEventListener('input', updateTimeFromInputs);
    }
    
    // Datepicker beim Klick auf das gesamte Eingabefeld öffnen
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        input.addEventListener('click', function() {
            if (typeof this.showPicker === 'function') {
                try {
                    this.showPicker();
                } catch (e) {
                    this.focus();
                }
            } else {
                this.focus();
            }
        });
    });
    
    // Beobachte DOM-Änderungen, um updateTimeFromInputs aufzurufen wenn nötig
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            // Wenn das DateTime-Feld sichtbar wird, aktualisiere
            const hourInput = document.getElementById('einaescherungsdatum-hour');
            const minuteInput = document.getElementById('einaescherungsdatum-minute');
            if (hourInput && minuteInput && hourInput.offsetParent !== null) {
                updateTimeFromInputs();
            }
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'style', 'x-show']
    });
});
</script>

</body>
</html>

