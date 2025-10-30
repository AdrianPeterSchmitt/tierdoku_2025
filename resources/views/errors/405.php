<?php
$title = '405 - Methode nicht erlaubt';
$layout = 'layouts/app';
$content = ob_start();
?>

<div class="px-4 py-8 text-center">
    <h1 class="text-6xl font-bold text-gray-900 mb-4">405</h1>
    <h2 class="text-2xl font-semibold text-gray-700 mb-4">HTTP-Methode nicht erlaubt</h2>
    <p class="text-gray-600 mb-8">Die angewendete HTTP-Methode ist für diese Route nicht zulässig.</p>
    <a href="/" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
        Zur Startseite
    </a>
</div>

<?php
$content = ob_get_clean();
require $GLOBALS['viewBasePath'] . 'layouts/app.php';
