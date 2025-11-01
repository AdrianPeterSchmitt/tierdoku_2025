<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User-Verwaltung - Dokumentation der anonymen Tiere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen">

<?php require $GLOBALS['viewBasePath'] . 'partials/nav.php'; ?>

<div class="w-full px-4 py-6 space-y-6" x-data="userApp()">
    
    <!-- Add/Edit User Form -->
    <section class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
        <div class="mb-6">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <span x-show="!isEditMode">➕ Neuen Benutzer hinzufügen</span>
                <span x-show="isEditMode">✏️ Benutzer bearbeiten</span>
            </h2>
        </div>
        
        <!-- Flash Message -->
        <div id="flash-message" class="mb-4 hidden"></div>

        <form id="user-form" method="post" @submit.prevent="handleSubmit" class="space-y-4">
            <input type="hidden" name="user_id" :value="currentEditId">
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Benutzername *</label>
                    <input 
                        type="text" 
                        name="username" 
                        x-model="formData.username"
                        :required="!isEditMode"
                        class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="formData.name"
                        class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">E-Mail *</label>
                    <input 
                        type="email" 
                        name="email" 
                        x-model="formData.email"
                        :required="!isEditMode"
                        class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        <span x-show="!isEditMode">Passwort *</span>
                        <span x-show="isEditMode">Neues Passwort (leer = unverändert)</span>
                    </label>
                    <input 
                        type="password" 
                        name="password" 
                        x-model="formData.password"
                        :required="!isEditMode"
                        class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Rolle *</label>
                    <select 
                        name="role" 
                        x-model="formData.role"
                        :required="!isEditMode"
                        class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                    >
                        <option value="">- Auswählen -</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="mitarbeiter">Mitarbeiter</option>
                    </select>
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Standorte *</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2 mt-2">
                        <?php foreach ($standorte as $s): ?>
                        <label class="flex items-center gap-2 px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg hover:bg-gray-800 cursor-pointer">
                            <input 
                                type="checkbox" 
                                :value="<?= $s->standort_id ?>"
                                x-model="formData.standort_ids"
                                class="w-4 h-4 text-blue-600 bg-gray-700 border-gray-600 rounded focus:ring-blue-500"
                            >
                            <span class="text-white text-sm"><?= htmlspecialchars($s->name) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Non-Admin Benutzer müssen mindestens einen Standort haben</p>
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
                    <span x-show="!saving && !isEditMode">Speichern</span>
                    <span x-show="!saving && isEditMode">Aktualisieren</span>
                    <span x-show="saving">Wird gespeichert...</span>
                </button>
            </div>
        </form>
    </section>

    <!-- Users Table -->
    <section class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
        <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
            👥 Benutzer-Übersicht
        </h2>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Username</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Rolle</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Standorte</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr class="border-b border-gray-800 hover:bg-gray-800/30">
                        <td class="px-4 py-3 font-mono font-bold text-blue-400"><?= htmlspecialchars($u->username) ?></td>
                        <td class="px-4 py-3 text-sm"><?= htmlspecialchars($u->name) ?></td>
                        <td class="px-4 py-3 text-sm"><?= htmlspecialchars($u->email) ?></td>
                        <td class="px-4 py-3 text-sm">
                            <?php if ($u->role === 'admin'): ?>
                                <span class="px-2 py-1 bg-red-500/20 text-red-300 rounded text-xs">Admin</span>
                            <?php elseif ($u->role === 'manager'): ?>
                                <span class="px-2 py-1 bg-yellow-500/20 text-yellow-300 rounded text-xs">Manager</span>
                            <?php else: ?>
                                <span class="px-2 py-1 bg-blue-500/20 text-blue-300 rounded text-xs">Mitarbeiter</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <?php
                            $standorteNames = $u->standorte->pluck('name')->toArray();
                        echo htmlspecialchars(implode(', ', $standorteNames) ?: '-');
                        ?>
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex gap-1">
                                <button type="button" @click="editUser(<?= $u->id ?>)" class="px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs">
                                    ✏️
                                </button>
                                <button type="button" @click="openDeleteConfirm(<?= $u->id ?>, '<?= htmlspecialchars($u->username) ?>', '<?= $u->role ?>')" class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteConfirm" x-transition class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" style="display: none;" @click.self="closeDeleteConfirm()">
        <div class="bg-gray-800 rounded-2xl p-6 max-w-md w-full mx-4 shadow-2xl">
            <h2 class="text-2xl font-bold mb-4 text-red-400">⚠️ Benutzer löschen?</h2>
            <div x-show="deleteConfirmRole === 'admin'" class="mb-4 p-3 bg-red-900/20 border border-red-500/50 rounded-lg text-red-300">
                ⚠️ Admin-Benutzer können nicht gelöscht werden!
            </div>
            <p class="text-gray-300 mb-6">
                Möchten Sie den Benutzer <span class="font-bold" x-text="deleteConfirmUsername"></span> wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.
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
                    @click="deleteUser()" 
                    :disabled="deleteConfirmRole === 'admin'"
                    class="px-8 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed w-[150px]"
                >
                    Löschen
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function userApp() {
    return {
        isEditMode: false,
        saving: false,
        currentEditId: null,
        formData: {
            username: '',
            name: '',
            email: '',
            password: '',
            role: '',
            standort_ids: [],
        },
        
        init() {
            console.log('userApp initialized');
        },
        
        async editUser(userId) {
            try {
                // Get user data from server
                const response = await fetch(`/users/${userId}/edit`);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (!data.success || !data.user) {
                    alert('Benutzer nicht gefunden');
                    return;
                }
                
                const user = data.user;
                this.formData = {
                    username: user.username || '',
                    name: user.name || '',
                    email: user.email || '',
                    password: '',
                    role: user.role || '',
                    standort_ids: user.standort_ids || [],
                };
                this.currentEditId = userId;
                this.isEditMode = true;
                
                // Scroll to form
                document.getElementById('user-form').scrollIntoView({ behavior: 'smooth', block: 'start' });
            } catch (error) {
                console.error('Error loading user:', error);
                alert('Fehler beim Laden des Benutzers: ' + error.message);
            }
        },
        
        resetForm() {
            this.isEditMode = false;
            this.currentEditId = null;
            this.formData = {
                username: '',
                name: '',
                email: '',
                password: '',
                role: '',
                standort_ids: [],
            };
            document.getElementById('user-form').reset();
        },
        
        async handleSubmit(event) {
            this.saving = true;
            const form = event.target;
            const flashMsg = document.getElementById('flash-message');
            
            const formData = new FormData(form);
            
            // Add standort_ids as array
            formData.delete('standort_ids');
            if (this.formData.standort_ids && Array.isArray(this.formData.standort_ids)) {
                this.formData.standort_ids.forEach(id => {
                    formData.append('standort_ids[]', id);
                });
            }
            
            const url = this.isEditMode ? `/users/${this.currentEditId}` : '/users';
            
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    flashMsg.className = 'mb-4 p-4 rounded-lg border border-green-500/50 bg-green-900/20 text-green-300';
                    flashMsg.textContent = data.message;
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
        
        deleteConfirmUserId: null,
        deleteConfirmUsername: '',
        deleteConfirmRole: '',
        showDeleteConfirm: false,
        
        openDeleteConfirm(userId, username, role) {
            this.deleteConfirmUserId = userId;
            this.deleteConfirmUsername = username || '';
            this.deleteConfirmRole = role || '';
            this.showDeleteConfirm = true;
        },
        
        closeDeleteConfirm() {
            this.showDeleteConfirm = false;
            this.deleteConfirmUserId = null;
            this.deleteConfirmUsername = '';
            this.deleteConfirmRole = '';
        },
        
        async deleteUser() {
            if (!this.deleteConfirmUserId) {
                return;
            }
            
            const userId = this.deleteConfirmUserId;
            const flashMsg = document.getElementById('flash-message');
            
            try {
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                
                const response = await fetch(`/users/${userId}/delete`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    flashMsg.className = 'mb-4 p-4 rounded-lg border border-green-500/50 bg-green-900/20 text-green-300';
                    flashMsg.textContent = data.message || 'Benutzer erfolgreich gelöscht.';
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

// Handle user form submit - integrated into Alpine.js component
// Submit handler moved to form @submit.prevent in Alpine.js
</script>

<style>
[x-cloak] { display: none !important; }
</style>

</body>
</html>
