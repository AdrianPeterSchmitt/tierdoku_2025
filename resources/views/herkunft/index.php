<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herkunft-Verwaltung - Dokumentation der anonymen Tiere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen">
    <?php require $GLOBALS['viewBasePath'] . 'partials/nav.php'; ?>
    <div class="w-full px-4 py-8" x-data="herkunftApp()">
        <h1 class="text-3xl font-bold text-white mb-6">Herkunft-Verwaltung</h1>

        <!-- Filter / Standortwahl -->
        <div class="bg-gray-800/50 backdrop-blur border border-gray-700/50 rounded-2xl p-4 mb-6">
            <form method="GET" action="/herkunft" class="flex items-end gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Standort</label>
                    <select name="standort_id" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
                        <?php foreach ($standorte as $s): ?>
                        <option value="<?= $s->standort_id ?>" <?= $currentStandortId == $s->standort_id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s->name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg">Anzeigen</button>
            </form>
        </div>

        <!-- Add/Edit Herkunft Form -->
        <?php if ($user && ($user->isAdmin() || $user->isManager())): ?>
        <div class="bg-gray-800/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 mb-6">
            <div class="mb-6">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <span x-show="!isEditMode">➕ Neue Herkunft hinzufügen</span>
                    <span x-show="isEditMode">✏️ Herkunft bearbeiten</span>
                </h2>
            </div>
            
            <!-- Flash Message -->
            <div id="flash" class="mb-4 hidden"></div>
            
            <form id="herkunft-form" @submit.prevent="handleSubmit" class="space-y-4">
                <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="herkunft_id" :value="currentEditId">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
                        <input 
                            type="text" 
                            name="name"
                            x-model="formData.name" 
                            :required="!isEditMode"
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Standort *</label>
                        <select 
                            name="standort_id"
                            x-model="formData.standort_id" 
                            :required="!isEditMode"
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                            <option value="">- Auswählen -</option>
                            <?php foreach ($standorte as $s): ?>
                            <option value="<?= $s->standort_id ?>" <?= $currentStandortId == $s->standort_id ? 'selected' : '' ?>><?= htmlspecialchars($s->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

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
                        <span x-show="!saving && !isEditMode">Hinzufügen</span>
                        <span x-show="!saving && isEditMode">Aktualisieren</span>
                        <span x-show="saving">Wird gespeichert...</span>
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="bg-gray-800/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="text-left py-3 px-4 text-gray-400">Name</th>
                        <th class="text-left py-3 px-4 text-gray-400">Standort</th>
                        <th class="text-right py-3 px-4 text-gray-400">Verwendungen</th>
                        <?php if ($user && ($user->isAdmin() || $user->isManager())): ?>
                        <th class="py-3 px-4 text-gray-400 text-right">Aktionen</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($herkuenfte as $h): ?>
                    <tr class="border-b border-gray-800 hover:bg-gray-800/30">
                        <td class="py-3 px-4 text-white"><?= htmlspecialchars($h->name) ?></td>
                        <td class="py-3 px-4 text-gray-300"><?= htmlspecialchars($h->standort->name ?? '-') ?></td>
                        <td class="py-3 px-4 text-right text-gray-300"><?= $h->verwendungen_count ?></td>
                        <?php if ($user && ($user->isAdmin() || $user->isManager())): ?>
                        <td class="py-3 px-4 text-right">
                            <div class="flex gap-1 justify-end">
                                <button 
                                    type="button"
                                    @click="editHerkunft(<?= $h->herkunft_id ?>)" 
                                    class="px-2 py-1 bg-blue-500 hover:bg-blue-600 rounded text-white text-xs"
                                >
                                    ✏️
                                </button>
                                <button 
                                    type="button"
                                    @click="openDeleteConfirm(<?= $h->herkunft_id ?>)" 
                                    class="px-2 py-1 bg-red-500 hover:bg-red-600 rounded text-white text-xs"
                                >
                                    🗑️
                                </button>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>


        <!-- Delete Confirmation Modal -->
        <div x-show="showDeleteConfirm" x-transition class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" style="display: none;" @click.self="closeDeleteConfirm()">
            <div class="bg-gray-800 rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
                <h2 class="text-2xl font-bold mb-4 text-red-400">⚠️ Herkunft löschen?</h2>
                <p class="text-gray-300 mb-6">
                    Möchten Sie diese Herkunft wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.
                </p>
                <div class="flex gap-3 justify-end">
                    <button 
                        type="button" 
                        @click="closeDeleteConfirm()" 
                        class="px-8 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition shadow disabled:opacity-50 disabled:cursor-not-allowed w-[150px]"
                    >
                        Abbrechen
                    </button>
                    <button 
                        type="button" 
                        @click="deleteHerkunft()" 
                        class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed w-[150px]"
                    >
                        Löschen
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
function herkunftApp() {
    return {
        isEditMode: false,
        saving: false,
        currentEditId: null,
        formData: {
            name: '',
            standort_id: '<?= (int) ($currentStandortId ?? 0) ?>'
        },
        deleteConfirmId: null,
        showDeleteConfirm: false,
        
        async editHerkunft(id) {
            try {
                const response = await fetch(`/herkunft/${id}/edit`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (!data.success || !data.herkunft) {
                    alert('Herkunft nicht gefunden');
                    return;
                }
                
                const herkunft = data.herkunft;
                this.formData = {
                    name: herkunft.name || '',
                    standort_id: herkunft.standort_id || '<?= (int) ($currentStandortId ?? 0) ?>',
                };
                this.currentEditId = id;
                this.isEditMode = true;
                
                // Scroll to form
                document.getElementById('herkunft-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (error) {
                console.error('Error loading herkunft:', error);
                alert('Fehler beim Laden der Herkunft: ' + error.message);
            }
        },
        
        resetForm() {
            this.isEditMode = false;
            this.currentEditId = null;
            this.formData = {
                name: '',
                standort_id: '<?= (int) ($currentStandortId ?? 0) ?>'
            };
            document.getElementById('herkunft-form').reset();
        },
        
        async handleSubmit(event) {
            this.saving = true;
            const form = event.target;
            const flashMsg = document.getElementById('flash');
            
            const formData = new FormData(form);
            const url = this.isEditMode ? `/herkunft/${this.currentEditId}` : '/herkunft';
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    flashMsg.className = 'mb-4 p-4 rounded-lg border border-green-500/50 bg-green-900/20 text-green-300';
                    flashMsg.textContent = this.isEditMode ? 'Herkunft erfolgreich aktualisiert.' : 'Herkunft erfolgreich hinzugefügt.';
                    flashMsg.classList.remove('hidden');
                    
                    this.resetForm();
                    
                    // Reload after 1 second
                    setTimeout(() => location.reload(), 1000);
                } else {
                    flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
                    flashMsg.textContent = data.error || 'Fehler';
                    flashMsg.classList.remove('hidden');
                    this.saving = false;
                }
            } catch (error) {
                flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
                flashMsg.textContent = 'Fehler: ' + error.message;
                flashMsg.classList.remove('hidden');
                this.saving = false;
            }
        },
        
        openDeleteConfirm(id) {
            this.deleteConfirmId = id;
            this.showDeleteConfirm = true;
        },
        
        closeDeleteConfirm() {
            this.showDeleteConfirm = false;
            this.deleteConfirmId = null;
        },
        
        async deleteHerkunft() {
            if (!this.deleteConfirmId) return;
            
            const id = this.deleteConfirmId;
            const flashMsg = document.getElementById('flash');
            
            try {
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                
                const response = await fetch(`/herkunft/${id}/delete`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    flashMsg.className = 'mb-4 p-4 rounded-lg border border-green-500/50 bg-green-900/20 text-green-300';
                    flashMsg.textContent = data.message || 'Herkunft erfolgreich gelöscht.';
                    flashMsg.classList.remove('hidden');
                    
                    this.closeDeleteConfirm();
                    
                    // Reload after 1 second
                    setTimeout(() => location.reload(), 1000);
                } else {
                    flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
                    flashMsg.textContent = data.error || 'Fehler beim Löschen';
                    flashMsg.classList.remove('hidden');
                    this.closeDeleteConfirm();
                }
            } catch (error) {
                console.error('Delete error:', error);
                flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
                flashMsg.textContent = 'Fehler: ' + error.message;
                flashMsg.classList.remove('hidden');
                this.closeDeleteConfirm();
            }
        }
    }
}
</script>

</body>
</html>

