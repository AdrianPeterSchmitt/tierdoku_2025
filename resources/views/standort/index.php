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

        <!-- Neue Standort -->
        <?php if ($user && ($user->isAdmin() || $user->isManager())): ?>
        <div class="bg-gray-800/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 mb-6">
            <form id="add-standort-form" @submit.prevent="createStandort" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
                    <input type="text" x-model="form.name" required class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="form.aktiv" class="w-4 h-4 bg-gray-900 border border-gray-700 rounded text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-300">Aktiv</span>
                    </label>
                </div>
                <div class="md:col-span-3 text-right">
                    <button type="submit" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg">Hinzufügen</button>
                </div>
            </form>
            <div id="flash" class="mt-3 text-sm text-red-300 hidden"></div>
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
                            <button @click="startEdit(<?= $s->standort_id ?>, '<?= htmlspecialchars($s->name) ?>', <?= $s->aktiv ? 'true' : 'false' ?>)" class="px-2 py-1 bg-blue-500 hover:bg-blue-600 rounded text-white text-xs">✏️</button>
                            <button @click="toggleAktiv(<?= $s->standort_id ?>, <?= $s->aktiv ? 'true' : 'false' ?>)" class="px-2 py-1 bg-yellow-500 hover:bg-yellow-600 rounded text-white text-xs" title="<?= $s->aktiv ? 'Deaktivieren' : 'Aktivieren' ?>">
                                <?= $s->aktiv ? '⏸️' : '▶️' ?>
                            </button>
                            <?php if ((int)($s->verwendungen_count ?? 0) === 0): ?>
                            <button @click="removeStandort(<?= $s->standort_id ?>)" class="px-2 py-1 bg-red-500 hover:bg-red-600 rounded text-white text-xs">🗑️</button>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <a href="/kremation" class="text-blue-400 hover:text-blue-300">← Zurück zu Kremationen</a>
        </div>
    </div>

<script>
function standortApp() {
    return {
        form: {
            name: '',
            aktiv: true
        },
        edit: null,
        async createStandort() {
            const fd = this.toFormData(this.form);
            fd.append('_token', '<?= csrf_token() ?>');
            const res = await fetch('/standort', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) { location.reload(); return; }
            this.showError(data.error || 'Fehler beim Anlegen');
        },
        async removeStandort(id) {
            if (!confirm('Standort wirklich löschen?')) return;
            const fd = new FormData();
            fd.append('_method', 'DELETE');
            fd.append('_token', '<?= csrf_token() ?>');
            const res = await fetch(`/standort/${id}/delete`, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) { location.reload(); return; }
            alert(data.error || 'Fehler beim Löschen');
        },
        startEdit(id, name, aktiv) {
            this.edit = { id, name, aktiv: aktiv === 'true' || aktiv === true };
            const newName = prompt('Neuer Name:', name);
            if (newName === null) return;
            this.edit.name = newName.trim();
            if (!this.edit.name) return;
            this.saveEdit();
        },
        async saveEdit() {
            const fd = this.toFormData(this.edit);
            fd.append('_token', '<?= csrf_token() ?>');
            const res = await fetch(`/standort/${this.edit.id}`, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) { location.reload(); return; }
            this.showError(data.error || 'Fehler beim Speichern');
        },
        async toggleAktiv(id, currentAktiv) {
            const newAktiv = !(currentAktiv === 'true' || currentAktiv === true);
            const fd = new FormData();
            fd.append('aktiv', newAktiv ? '1' : '0');
            fd.append('_token', '<?= csrf_token() ?>');
            const res = await fetch(`/standort/${id}`, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) { location.reload(); return; }
            alert(data.error || 'Fehler beim Ändern');
        },
        toFormData(obj) {
            const fd = new FormData();
            Object.entries(obj).forEach(([k,v]) => {
                if (typeof v === 'boolean') {
                    fd.append(k, v ? '1' : '0');
                } else {
                    fd.append(k, v);
                }
            });
            return fd;
        },
        showError(msg) {
            const el = document.getElementById('flash');
            el.textContent = msg;
            el.classList.remove('hidden');
        }
    }
}
</script>

</body>
</html>


