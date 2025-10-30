<?php
$title = '500 - Server-Fehler';
$layout = 'layouts/app';
$content = ob_start();
?>

<div class="px-4 py-8 text-center">
    <h1 class="text-6xl font-bold text-gray-900 mb-4">500</h1>
    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Interner Server-Fehler</h2>
    <p class="text-gray-600 mb-8">Es ist ein Fehler aufgetreten. Bitte versuche es später erneut.</p>
    <a href="/" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
        Zur Startseite
    </a>
</div>

<?php
$content = ob_get_clean();
require $GLOBALS['viewBasePath'] . 'layouts/app.php';
