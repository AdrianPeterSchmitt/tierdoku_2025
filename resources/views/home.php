<?php
$layout = 'layouts/app';
$content = ob_start();
?>

<div class="px-4 py-8">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    🎉 Willkommen zur Tierdokumentation!
                </h1>
                <p class="text-lg text-gray-600 mb-8">
                    Deine moderne PHP WebApp ist erfolgreich eingerichtet.
                </p>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h2 class="text-xl font-semibold text-blue-900 mb-2">Aktuelle Umgebung</h2>
                    <p class="text-blue-700">
                        <strong>APP_ENV:</strong> <code class="bg-blue-100 px-2 py-1 rounded"><?= htmlspecialchars($env) ?></code>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <h3 class="font-semibold text-green-900 mb-2">✅ FastRoute</h3>
                        <p class="text-green-700 text-sm">Routing ist konfiguriert</p>
                    </div>
                    
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                        <h3 class="font-semibold text-purple-900 mb-2">✅ Illuminate DB</h3>
                        <p class="text-purple-700 text-sm">Datenbank-Verbindung bereit</p>
                    </div>
                    
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
                        <h3 class="font-semibold text-orange-900 mb-2">✅ TailwindCSS</h3>
                        <p class="text-orange-700 text-sm">Styling-System aktiv</p>
                    </div>
                </div>

                <div class="mt-8">
                    <a href="/about" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Erfahre mehr über uns →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require $GLOBALS['viewBasePath'] . 'layouts/app.php';
