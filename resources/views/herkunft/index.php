<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herkunft-Verwaltung - Tierdokumentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen">
    <?php require $GLOBALS['viewBasePath'] . 'partials/nav.php'; ?>
    <div class="max-w-4xl mx-auto p-8" x-data="herkunftApp()">
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

        <!-- Neue Herkunft -->
        <?php if ($user && ($user->isAdmin() || $user->isManager())): ?>
        <div class="bg-gray-800/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 mb-6">
            <form id="add-herkunft-form" @submit.prevent="createHerkunft" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="hidden" name="_token" value="<?= csrf_token() ?>">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Name *</label>
                    <input type="text" x-model="form.name" required class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Standort *</label>
                    <select x-model="form.standort_id" required class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
                        <?php foreach ($standorte as $s): ?>
                        <option value="<?= $s->standort_id ?>" <?= $currentStandortId == $s->standort_id ? 'selected' : '' ?>><?= htmlspecialchars($s->name) ?></option>
                        <?php endforeach; ?>
                    </select>
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
                            <button @click="startEdit(<?= $h->herkunft_id ?>, '<?= htmlspecialchars($h->name) ?>', <?= (int) $h->standort_id ?>)" class="px-2 py-1 bg-blue-500 hover:bg-blue-600 rounded text-white text-xs">✏️</button>
                            <?php if ((int)$h->verwendungen_count === 0): ?>
                            <button @click="removeHerkunft(<?= $h->herkunft_id ?>)" class="px-2 py-1 bg-red-500 hover:bg-red-600 rounded text-white text-xs">🗑️</button>
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
function herkunftApp() {
    return {
        form: {
            name: '',
            standort_id: '<?= (int) ($currentStandortId ?? 0) ?>'
        },
        edit: null,
        async createHerkunft() {
            const fd = this.toFormData(this.form);
            fd.append('_token', '<?= csrf_token() ?>');
            const res = await fetch('/herkunft', { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) { location.reload(); return; }
            this.showError(data.error || 'Fehler beim Anlegen');
        },
        async removeHerkunft(id) {
            if (!confirm('Herkunft wirklich löschen?')) return;
            const fd = new FormData();
            fd.append('_method', 'DELETE');
            const res = await fetch(`/herkunft/${id}/delete`, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) { location.reload(); return; }
            alert(data.error || 'Fehler beim Löschen');
        },
        startEdit(id, name, standortId) {
            this.edit = { id, name, standort_id: String(standortId || '') };
            const newName = prompt('Neuer Name:', name);
            if (newName === null) return;
            this.edit.name = newName.trim();
            if (!this.edit.name) return;
            this.saveEdit();
        },
        async saveEdit() {
            const fd = this.toFormData(this.edit);
            fd.append('_token', '<?= csrf_token() ?>');
            const res = await fetch(`/herkunft/${this.edit.id}`, { method: 'POST', body: fd });
            const data = await res.json();
            if (data.success) { location.reload(); return; }
            this.showError(data.error || 'Fehler beim Speichern');
        },
        toFormData(obj) {
            const fd = new FormData();
            Object.entries(obj).forEach(([k,v]) => fd.append(k, v));
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

