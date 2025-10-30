<?php
$layout = 'layouts/app';
$content = ob_start();
?>

<div class="px-4 py-8">
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Über das Projekt</h1>
            
            <div class="prose max-w-none">
                <p class="text-gray-600 mb-4">
                    Dies ist eine moderne PHP WebApp, optimiert für Shared Hosting-Umgebungen.
                </p>
                
                <h2 class="text-2xl font-semibold text-gray-900 mt-6 mb-4">Technologien</h2>
                <ul class="list-disc list-inside text-gray-600 space-y-2">
                    <li><strong>PHP 8.2+</strong> - Moderne PHP-Features</li>
                    <li><strong>FastRoute</strong> - High-Performance Routing</li>
                    <li><strong>Illuminate Database</strong> - Elegante Datenbank-Abstraktion</li>
                    <li><strong>TailwindCSS</strong> - Utility-First CSS Framework</li>
                    <li><strong>Monolog</strong> - PSR-3 Logging</li>
                    <li><strong>Respect/Validation</strong> - Input-Validierung</li>
                </ul>

                <h2 class="text-2xl font-semibold text-gray-900 mt-6 mb-4">Tools</h2>
                <ul class="list-disc list-inside text-gray-600 space-y-2">
                    <li><strong>PHPStan</strong> - Static Analysis</li>
                    <li><strong>PHPUnit</strong> - Testing Framework</li>
                    <li><strong>Laravel Pint</strong> - Code Formatter</li>
                </ul>

                <div class="mt-8">
                    <a href="/" class="text-blue-600 hover:text-blue-700 font-medium">
                        ← Zurück zur Startseite
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require $GLOBALS['viewBasePath'] . 'layouts/app.php';
