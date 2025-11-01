<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Standort-Verwaltung - Dokumentation der anonymen Tiere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen">
    <?php require $GLOBALS['viewBasePath'] . 'partials/nav.php'; ?>
    <div class="w-full px-4 py-8" x-data="standortApp()">
        <h1 class="text-3xl font-bold text-white mb-6">Standort-Verwaltung</h1>

        <!-- Add/Edit Standort Form -->
        <?php if ($user && ($user->isAdmin() || $user->isManager())): ?>
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 mb-6 shadow-2xl">
            <div class="mb-6">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <span x-show="!isEditMode">➕ Neuen Standort hinzufügen</span>
                    <span x-show="isEditMode">✏️ Standort bearbeiten</span>
                </h2>
            </div>
            
            <!-- Flash Message -->
            <div id="flash" class="mb-4 hidden"></div>
            
            <form id="standort-form" @submit.prevent="handleSubmit" class="space-y-4">
                <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="standort_id" :value="currentEditId">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
                        <input 
                            type="text" 
                            name="name"
                            x-model="formData.name" 
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="aktiv"
                                x-model="formData.aktiv" 
                                class="w-4 h-4 bg-gray-900 border border-gray-700 rounded text-blue-600 focus:ring-blue-500"
                            >
                            <span class="text-sm font-medium text-gray-300">Aktiv</span>
                        </label>
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
                        <th class="text-center py-3 px-4 text-gray-400">Status</th>
                        <th class="text-right py-3 px-4 text-gray-400">Verwendungen</th>
                        <?php if ($user && ($user->isAdmin() || $user->isManager())): ?>
                        <th class="py-3 px-4 text-gray-400 text-right">Aktionen</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($standorte as $s): ?>
                    <tr class="border-b border-gray-800 hover:bg-gray-800/30">
                        <td class="py-3 px-4 text-white"><?= htmlspecialchars($s->name) ?></td>
                        <td class="py-3 px-4 text-center">
                            <?php if ($s->aktiv): ?>
                                <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded text-xs">Aktiv</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-gray-500/20 text-gray-400 rounded text-xs">Inaktiv</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 px-4 text-right text-gray-300">
                            <?= $s->verwendungen_count ?? 0 ?>
                            <?php if ($s->verwendungen_count > 0): ?>
                                <span class="text-xs text-gray-500 ml-1">
                                    (<?= $s->kremations_count ?? 0 ?> Krem., <?= $s->users_count ?? 0 ?> Benutzer, <?= $s->herkunft_count ?? 0 ?> Herk.)
                                </span>
                            <?php endif; ?>
                        </td>
                        <?php if ($user && ($user->isAdmin() || $user->isManager())): ?>
                        <td class="py-3 px-4 text-right">
                            <div class="flex gap-1 justify-end">
                                <button 
                                    type="button"
                                    @click="editStandort(<?= $s->standort_id ?>)"
                                    class="px-2 py-1 bg-blue-500 hover:bg-blue-600 rounded text-white text-xs"
                                >
                                    ✏️
                                </button>
                                <button 
                                    type="button"
                                    @click="toggleAktiv(<?= $s->standort_id ?>, <?= $s->aktiv ? 'true' : 'false' ?>)" 
                                    class="px-2 py-1 bg-yellow-500 hover:bg-yellow-600 rounded text-white text-xs" 
                                    title="<?= $s->aktiv ? 'Deaktivieren' : 'Aktivieren' ?>"
                                >
                                    <?= $s->aktiv ? '⏸️' : '▶️' ?>
                                </button>
                                <button 
                                    type="button"
                                    @click="openDeleteConfirm(<?= $s->standort_id ?>, '<?= htmlspecialchars($s->name) ?>', <?= (int)($s->verwendungen_count ?? 0) ?>)"
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
        <div x-show="showDeleteConfirm" x-transition x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" style="display: none;" @click.self="closeDeleteConfirm()">
            <div class="bg-gray-800 rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
                <h2 class="text-2xl font-bold mb-4 text-red-400">⚠️ Standort löschen?</h2>
                <div x-show="deleteConfirmUsage > 0" class="mb-4 p-3 bg-red-900/20 border border-red-500/50 rounded-lg text-red-300">
                    ⚠️ Dieser Standort wird noch verwendet (<span x-text="deleteConfirmUsage"></span> Verwendungen) und kann nicht gelöscht werden!
                </div>
                <p class="text-gray-300 mb-6">
                    Möchten Sie den Standort <span class="font-bold" x-text="deleteConfirmName"></span> wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.
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
                        @click="deleteStandort()" 
                        :disabled="deleteConfirmUsage > 0"
                        class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed w-[150px]"
                    >
                        Löschen
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
function standortApp() {
    return {
        isEditMode: false,
        saving: false,
        currentEditId: null,
        formData: {
            name: '',
            aktiv: true
        },
        deleteConfirmId: null,
        deleteConfirmName: '',
        deleteConfirmUsage: 0,
        showDeleteConfirm: false,
        
        async editStandort(id) {
            try {
                const response = await fetch(`/standort/${id}/edit`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (!data.success || !data.standort) {
                    alert('Standort nicht gefunden');
                    return;
                }
                
                const standort = data.standort;
                this.formData = {
                    name: standort.name || '',
                    aktiv: standort.aktiv ?? true,
                };
                this.currentEditId = id;
                this.isEditMode = true;
                
                // Scroll to form
                document.getElementById('standort-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (error) {
                console.error('Error loading standort:', error);
                alert('Fehler beim Laden des Standorts: ' + error.message);
            }
        },
        
        resetForm() {
            this.isEditMode = false;
            this.currentEditId = null;
            this.formData = {
                name: '',
                aktiv: true
            };
            document.getElementById('standort-form').reset();
        },
        
        async handleSubmit(event) {
            this.saving = true;
            const form = event.target;
            const flashMsg = document.getElementById('flash');
            
            try {
                const formData = new FormData(form);
                
                const url = this.isEditMode 
                    ? `/standort/${this.currentEditId}`
                    : '/standort';
                
                const method = this.isEditMode ? 'POST' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                    return;
                } else {
                    flashMsg.textContent = data.error || 'Fehler beim Speichern';
                    flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
                    flashMsg.classList.remove('hidden');
                }
            } catch (error) {
                flashMsg.textContent = 'Fehler: ' + error.message;
                flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
                flashMsg.classList.remove('hidden');
            } finally {
                this.saving = false;
            }
        },
        
        openDeleteConfirm(id, name, usage) {
            this.deleteConfirmId = id;
            this.deleteConfirmName = name;
            this.deleteConfirmUsage = usage;
            this.showDeleteConfirm = true;
        },
        
        closeDeleteConfirm() {
            this.showDeleteConfirm = false;
            this.deleteConfirmId = null;
            this.deleteConfirmName = '';
            this.deleteConfirmUsage = 0;
        },
        
        async deleteStandort() {
            if (!this.deleteConfirmId) return;
            
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', '<?= csrf_token() ?>');
            
            try {
                const response = await fetch(`/standort/${this.deleteConfirmId}/delete`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Fehler beim Löschen');
                    this.closeDeleteConfirm();
                }
            } catch (error) {
                alert('Fehler: ' + error.message);
                this.closeDeleteConfirm();
            }
        },
        
        async toggleAktiv(id, currentAktiv) {
            const newAktiv = !(currentAktiv === 'true' || currentAktiv === true);
            const formData = new FormData();
            formData.append('aktiv', newAktiv ? '1' : '0');
            formData.append('_token', '<?= csrf_token() ?>');
            
            try {
                const response = await fetch(`/standort/${id}`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Fehler beim Ändern');
                }
            } catch (error) {
                alert('Fehler: ' + error.message);
            }
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>

</body>
</html>
