<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keine Berechtigung - Dokumentation der anonymen Tiere</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 text-white min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-4xl font-bold mb-4">403</h1>
        <p class="text-xl text-gray-300 mb-6"><?= htmlspecialchars($message ?? 'Keine Berechtigung') ?></p>
    </div>
</body>
</html>


