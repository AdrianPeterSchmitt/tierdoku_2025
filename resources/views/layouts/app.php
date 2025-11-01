<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'PHP WebApp' ?> - Dokumentation der anonymen Tiere</title>
    <link rel="stylesheet" href="/dist/style.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="text-xl font-bold text-gray-900">Dokumentation der anonymen Tiere</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="/" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Home</a>
                        <a href="/about" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Über uns</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="w-full py-6 sm:px-6 lg:px-8">
        <?= $content ?? '' ?>
    </main>

    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="w-full py-6 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-gray-500 text-sm">© 2025 Dokumentation der anonymen Tiere. Built with PHP & TailwindCSS.</p>
        </div>
    </footer>
</body>
</html>


