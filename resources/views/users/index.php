<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User-Verwaltung - Tierdokumentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen">

<?php require $GLOBALS['viewBasePath'] . 'partials/nav.php'; ?>

<div class="max-w-7xl mx-auto px-4 py-6 space-y-6" x-data="userApp()">
    
    <!-- Add User Form -->
    <section class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
        <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
            ➕ Neuen Benutzer hinzufügen
        </h2>
        
        <!-- Flash Message -->
        <div id="flash-message" class="mb-4 hidden"></div>

        <form id="user-form" method="post" action="/users" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Benutzername *</label>
                    <input type="text" name="username" required class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                    <input type="text" name="name" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">E-Mail *</label>
                    <input type="email" name="email" required class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Passwort *</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Rolle *</label>
                    <select name="role" required class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white">
                        <option value="">- Auswählen -</option>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="mitarbeiter">Mitarbeiter</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Standort *</label>
                    <select name="standort_id" required class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white">
                        <option value="">- Auswählen -</option>
                        <?php foreach ($standorte as $s): ?>
                        <option value="<?= $s->standort_id ?>"><?= htmlspecialchars($s->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-lg transition shadow-lg">
                    Speichern
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase">Standort</th>
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
                        <td class="px-4 py-3 text-sm"><?= htmlspecialchars($u->standort->name ?? '-') ?></td>
                        <td class="px-4 py-3 text-sm">
                            <div class="flex gap-1">
                                <button @click="editUser(<?= $u->id ?>)" class="px-2 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs">
                                    ✏️
                                </button>
                                <?php if ($u->id !== $user->id): ?>
                                <button @click="deleteUser(<?= $u->id ?>)" class="px-2 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs">
                                    🗑️
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- Edit Modal -->
<div x-show="showEditModal" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click="showEditModal = false">
    <div class="bg-gray-800 rounded-2xl p-6 max-w-md w-full mx-4" @click.stop>
        <h2 class="text-2xl font-bold mb-4">Benutzer bearbeiten</h2>
        
        <div id="flash-message-modal" class="mb-4 hidden"></div>

        <form id="edit-user-form" @submit.prevent="saveUser" class="space-y-4">
            <input type="hidden" name="username" :value="editUserData.username">
            <input type="hidden" name="name" :value="editUserData.name">
            <input type="hidden" name="email" :value="editUserData.email">
            <input type="hidden" name="password" :value="editUserData.password">
            <input type="hidden" name="role" :value="editUserData.role">
            <input type="hidden" name="standort_id" :value="editUserData.standort_id">
            
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Benutzername</label>
                <input type="text" x-model="editUserData.username" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                <input type="text" x-model="editUserData.name" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">E-Mail</label>
                <input type="email" x-model="editUserData.email" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Neues Passwort (leer = unverändert)</label>
                <input type="password" x-model="editUserData.password" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Rolle</label>
                <select x-model="editUserData.role" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="mitarbeiter">Mitarbeiter</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Standort</label>
                <select x-model="editUserData.standort_id" class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
                    <?php foreach ($standorte as $s): ?>
                    <option value="<?= $s->standort_id ?>"><?= htmlspecialchars($s->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-bold rounded-lg">
                    Speichern
                </button>
                <button type="button" @click="showEditModal = false" class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-lg">
                    Abbrechen
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function userApp() {
    return {
        showEditModal: false,
        editUserData: {},
        currentEditId: null,
        
        async editUser(userId) {
            // Get user data from server
            const user = <?= json_encode($users->toArray()) ?>.find(u => u.id === userId);
            
            if (!user) {
                alert('Benutzer nicht gefunden');
                return;
            }
            
            this.editUserData = {
                username: user.username || '',
                name: user.name || '',
                email: user.email || '',
                password: '',
                role: user.role || 'mitarbeiter',
                standort_id: user.standort_id || '',
            };
            this.currentEditId = userId;
            this.showEditModal = true;
        },
        
        async saveUser() {
            const flashMsg = document.getElementById('flash-message-modal');
            const form = document.getElementById('edit-user-form');
            
            const formData = new FormData(form);
            
            try {
                const response = await fetch(`/users/${this.currentEditId}`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload(); // Reload page on success
                } else {
                    flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
                    flashMsg.textContent = data.error || 'Fehler';
                    flashMsg.classList.remove('hidden');
                }
            } catch (error) {
                flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
                flashMsg.textContent = 'Fehler: ' + error.message;
                flashMsg.classList.remove('hidden');
            }
        },
        
        async deleteUser(userId) {
            if (!confirm('Benutzer wirklich löschen?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('_method', 'DELETE');
                
                const response = await fetch(`/users/${userId}/delete`, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Fehler beim Löschen');
                }
            } catch (error) {
                alert('Fehler: ' + error.message);
            }
        }
    }
}

// Handle user form submit
document.getElementById('user-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const flashMsg = document.getElementById('flash-message');
    
    try {
        const response = await fetch('/users', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            flashMsg.className = 'mb-4 p-4 rounded-lg border border-green-500/50 bg-green-900/20 text-green-300';
            flashMsg.textContent = data.message;
            flashMsg.classList.remove('hidden');
            
            // Reset form
            e.target.reset();
            
            // Reload after 1 second
            setTimeout(() => location.reload(), 1000);
        } else {
            flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
            flashMsg.textContent = data.error || 'Fehler';
            flashMsg.classList.remove('hidden');
        }
    } catch (error) {
        flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
        flashMsg.textContent = 'Fehler: ' + error.message;
        flashMsg.classList.remove('hidden');
    }
});
</script>

<style>
[x-cloak] { display: none !important; }
</style>

</body>
</html>
