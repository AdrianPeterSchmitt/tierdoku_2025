<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfiguration - Dokumentation der anonymen Tiere</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen">
    <?php require $GLOBALS['viewBasePath'] . 'partials/nav.php'; ?>
    
    <div class="w-full px-4 py-8" x-data="configApp()">
        <h1 class="text-3xl font-bold text-white mb-6">⚙️ Konfiguration</h1>

        <!-- Success/Error Messages -->
        <div id="flash-message" class="mb-6 hidden"></div>

        <!-- Configuration Form -->
        <form id="config-form" @submit.prevent="handleSubmit" class="space-y-8">
            <input type="hidden" name="_token" value="<?= csrf_token() ?>">

            <!-- Anwendungs-Einstellungen -->
            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    📱 Anwendungs-Einstellungen
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">App Name *</label>
                        <input 
                            type="text" 
                            name="APP_NAME"
                            x-model="formData.APP_NAME" 
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Environment *</label>
                        <select 
                            name="APP_ENV"
                            x-model="formData.APP_ENV" 
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                            <option value="production">Production</option>
                            <option value="local">Local</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Zeitzone *</label>
                        <input 
                            type="text" 
                            name="APP_TIMEZONE"
                            x-model="formData.APP_TIMEZONE" 
                            required
                            placeholder="Europe/Berlin"
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                        <p class="text-xs text-gray-400 mt-1">z.B. Europe/Berlin, America/New_York, UTC</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Log Level *</label>
                        <select 
                            name="LOG_LEVEL"
                            x-model="formData.LOG_LEVEL" 
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                            <option value="error">Error</option>
                            <option value="warning">Warning</option>
                            <option value="info">Info</option>
                            <option value="debug">Debug</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="APP_DEBUG"
                                x-model="formData.APP_DEBUG" 
                                class="w-4 h-4 bg-gray-900 border border-gray-700 rounded text-blue-600 focus:ring-blue-500"
                            >
                            <span class="text-sm font-medium text-gray-300">Debug-Modus aktivieren (APP_DEBUG)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Datenbank-Einstellungen -->
            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    💾 Datenbank-Einstellungen
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">DB Connection *</label>
                        <select 
                            name="DB_CONNECTION"
                            x-model="formData.DB_CONNECTION" 
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                            <option value="mysql">MySQL</option>
                            <option value="sqlite">SQLite</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">DB Host *</label>
                        <input 
                            type="text" 
                            name="DB_HOST"
                            x-model="formData.DB_HOST" 
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">DB Port *</label>
                        <input 
                            type="number" 
                            name="DB_PORT"
                            x-model="formData.DB_PORT" 
                            required
                            min="1"
                            max="65535"
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">DB Database *</label>
                        <input 
                            type="text" 
                            name="DB_DATABASE"
                            x-model="formData.DB_DATABASE" 
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">DB Username *</label>
                        <input 
                            type="text" 
                            name="DB_USERNAME"
                            x-model="formData.DB_USERNAME" 
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">DB Password *</label>
                        <div class="relative">
                            <input 
                                :type="showPassword ? 'text' : 'password'"
                                name="DB_PASSWORD"
                                x-model="formData.DB_PASSWORD" 
                                required
                                class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                            >
                            <button 
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white"
                            >
                                <span x-show="!showPassword">👁️</span>
                                <span x-show="showPassword">🙈</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session-Einstellungen -->
            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    🔒 Session-Einstellungen
                </h2>
                
                <div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="SESSION_SECURE"
                            x-model="formData.SESSION_SECURE" 
                            class="w-4 h-4 bg-gray-900 border border-gray-700 rounded text-blue-600 focus:ring-blue-500"
                        >
                        <span class="text-sm font-medium text-gray-300">HTTPS verwenden (SESSION_SECURE)</span>
                        <span class="text-xs text-gray-500">Nur aktivieren wenn HTTPS verfügbar</span>
                    </label>
                </div>
            </div>

            <!-- QR-Code-Einstellungen -->
            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    📱 QR-Code-Einstellungen
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            QR-Code Größe (Pixel)
                            <span class="text-xs text-gray-500 ml-1">(100-1000)</span>
                        </label>
                        <input 
                            type="number" 
                            name="QR_CODE_SIZE"
                            x-model="formData.QR_CODE_SIZE" 
                            required
                            min="100"
                            max="1000"
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            QR-Code Margin (Pixel)
                            <span class="text-xs text-gray-500 ml-1">(0-50)</span>
                        </label>
                        <input 
                            type="number" 
                            name="QR_CODE_MARGIN"
                            x-model="formData.QR_CODE_MARGIN" 
                            required
                            min="0"
                            max="50"
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Fehlerkorrektur-Level *</label>
                        <select 
                            name="QR_CODE_ERROR_CORRECTION"
                            x-model="formData.QR_CODE_ERROR_CORRECTION" 
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="Quartile">Quartile</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Encoding *</label>
                        <input 
                            type="text" 
                            name="QR_CODE_ENCODING"
                            x-model="formData.QR_CODE_ENCODING" 
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                </div>
            </div>

            <!-- PDF Label / Etikettendrucker-Einstellungen -->
            <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-6 shadow-2xl">
                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                    🖨️ PDF Label / Etikettendrucker-Einstellungen
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Papierformat *</label>
                        <input 
                            type="text" 
                            name="PDF_PAPER_SIZE"
                            x-model="formData.PDF_PAPER_SIZE" 
                            required
                            placeholder="a4"
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                        <p class="text-xs text-gray-400 mt-1">z.B. a4, a3, letter</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Ausrichtung *</label>
                        <select 
                            name="PDF_PAPER_ORIENTATION"
                            x-model="formData.PDF_PAPER_ORIENTATION" 
                            required
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                            <option value="portrait">Portrait</option>
                            <option value="landscape">Landscape</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            QR-Code Größe (mm)
                            <span class="text-xs text-gray-500 ml-1">(10-200)</span>
                        </label>
                        <input 
                            type="number" 
                            name="PDF_QR_CODE_SIZE_MM"
                            x-model="formData.PDF_QR_CODE_SIZE_MM" 
                            required
                            min="10"
                            max="200"
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            QR-Code Padding (mm)
                            <span class="text-xs text-gray-500 ml-1">(0-20)</span>
                        </label>
                        <input 
                            type="number" 
                            name="PDF_QR_CODE_PADDING_MM"
                            x-model="formData.PDF_QR_CODE_PADDING_MM" 
                            required
                            min="0"
                            max="20"
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Basis-Schriftgröße</label>
                        <input 
                            type="text" 
                            name="PDF_FONT_SIZE_BASE"
                            x-model="formData.PDF_FONT_SIZE_BASE" 
                            placeholder="14pt"
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Header-Schriftgröße</label>
                        <input 
                            type="text" 
                            name="PDF_FONT_SIZE_HEADER"
                            x-model="formData.PDF_FONT_SIZE_HEADER" 
                            placeholder="36pt"
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Label-Randbreite</label>
                        <input 
                            type="text" 
                            name="PDF_LABEL_BORDER_WIDTH"
                            x-model="formData.PDF_LABEL_BORDER_WIDTH" 
                            placeholder="3px"
                            class="w-full px-3 py-2 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        >
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-700/50">
                <button 
                    type="submit" 
                    :disabled="saving"
                    class="px-8 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold rounded-lg transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed w-[150px]"
                >
                    <span x-show="!saving">Speichern</span>
                    <span x-show="saving">Wird gespeichert...</span>
                </button>
            </div>
        </form>
    </div>

<script>
function configApp() {
    return {
        saving: false,
        showPassword: false,
        formData: {
            APP_NAME: '<?= htmlspecialchars($config['APP_NAME'] ?? 'Tierdokumentation') ?>',
            APP_ENV: '<?= htmlspecialchars($config['APP_ENV'] ?? 'production') ?>',
            APP_DEBUG: <?= ($config['APP_DEBUG'] ?? 'false') === 'true' ? 'true' : 'false' ?>,
            APP_TIMEZONE: '<?= htmlspecialchars($config['APP_TIMEZONE'] ?? 'Europe/Berlin') ?>',
            DB_CONNECTION: '<?= htmlspecialchars($config['DB_CONNECTION'] ?? 'mysql') ?>',
            DB_HOST: '<?= htmlspecialchars($config['DB_HOST'] ?? 'localhost') ?>',
            DB_PORT: '<?= htmlspecialchars($config['DB_PORT'] ?? '3306') ?>',
            DB_DATABASE: '<?= htmlspecialchars($config['DB_DATABASE'] ?? '') ?>',
            DB_USERNAME: '<?= htmlspecialchars($config['DB_USERNAME'] ?? '') ?>',
            DB_PASSWORD: '<?= htmlspecialchars($config['DB_PASSWORD'] ?? '') ?>',
            SESSION_SECURE: <?= ($config['SESSION_SECURE'] ?? 'false') === 'true' ? 'true' : 'false' ?>,
            LOG_LEVEL: '<?= htmlspecialchars($config['LOG_LEVEL'] ?? 'error') ?>',
            QR_CODE_SIZE: '<?= htmlspecialchars($config['QR_CODE_SIZE'] ?? '300') ?>',
            QR_CODE_MARGIN: '<?= htmlspecialchars($config['QR_CODE_MARGIN'] ?? '10') ?>',
            QR_CODE_ERROR_CORRECTION: '<?= htmlspecialchars($config['QR_CODE_ERROR_CORRECTION'] ?? 'High') ?>',
            QR_CODE_ENCODING: '<?= htmlspecialchars($config['QR_CODE_ENCODING'] ?? 'UTF-8') ?>',
            PDF_PAPER_SIZE: '<?= htmlspecialchars($config['PDF_PAPER_SIZE'] ?? 'a4') ?>',
            PDF_PAPER_ORIENTATION: '<?= htmlspecialchars($config['PDF_PAPER_ORIENTATION'] ?? 'portrait') ?>',
            PDF_QR_CODE_SIZE_MM: '<?= htmlspecialchars($config['PDF_QR_CODE_SIZE_MM'] ?? '60') ?>',
            PDF_QR_CODE_PADDING_MM: '<?= htmlspecialchars($config['PDF_QR_CODE_PADDING_MM'] ?? '5') ?>',
            PDF_FONT_SIZE_BASE: '<?= htmlspecialchars($config['PDF_FONT_SIZE_BASE'] ?? '14pt') ?>',
            PDF_FONT_SIZE_HEADER: '<?= htmlspecialchars($config['PDF_FONT_SIZE_HEADER'] ?? '36pt') ?>',
            PDF_LABEL_BORDER_WIDTH: '<?= htmlspecialchars($config['PDF_LABEL_BORDER_WIDTH'] ?? '3px') ?>',
        },
        
        async handleSubmit(event) {
            this.saving = true;
            const form = event.target;
            const flashMsg = document.getElementById('flash-message');
            
            try {
                const formData = new FormData(form);
                
                // Add checkbox values
                formData.append('APP_DEBUG', this.formData.APP_DEBUG ? '1' : '0');
                formData.append('SESSION_SECURE', this.formData.SESSION_SECURE ? '1' : '0');
                
                const response = await fetch('/config', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    flashMsg.textContent = data.message || 'Konfiguration erfolgreich gespeichert' + (data.backup_created ? ' (Backup erstellt: .env.backup)' : '');
                    flashMsg.className = 'mb-6 p-4 rounded-lg border border-green-500/50 bg-green-900/20 text-green-300';
                    flashMsg.classList.remove('hidden');
                    
                    // Scroll to top
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                } else {
                    flashMsg.textContent = data.error || 'Fehler beim Speichern der Konfiguration';
                    flashMsg.className = 'mb-6 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
                    flashMsg.classList.remove('hidden');
                }
            } catch (error) {
                flashMsg.textContent = 'Fehler: ' + error.message;
                flashMsg.className = 'mb-6 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
                flashMsg.classList.remove('hidden');
            } finally {
                this.saving = false;
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


