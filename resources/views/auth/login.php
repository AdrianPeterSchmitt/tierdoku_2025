<!DOCTYPE html>
<html lang="de" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tierdokumentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-slate-900 to-gray-900 min-h-screen flex items-center justify-center px-4">
    
    <div class="w-full max-w-md">
        <!-- Logo/Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent mb-2">
                Tierdokumentation
            </h1>
            <p class="text-gray-400">Animea Tierkrematorium</p>
        </div>

        <!-- Login Card -->
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur border border-gray-700/50 rounded-2xl p-8 shadow-2xl">
            <h2 class="text-2xl font-bold text-white mb-6 text-center">Anmelden</h2>

            <!-- Flash Messages -->
            <div id="flash-message" class="mb-4 hidden"></div>

            <form id="login-form" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-2">
                        Benutzername
                    </label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autocomplete="username"
                        class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        placeholder="Benutzername eingeben"
                    >
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                        Passwort
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="w-full px-4 py-3 bg-gray-900 border border-gray-700 rounded-lg focus:border-blue-500 focus:outline-none text-white"
                        placeholder="Passwort eingeben"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-lg transition shadow-lg"
                >
                    Anmelden
                </button>
            </form>

            <!-- Footer -->
            <div class="mt-6 text-center text-sm text-gray-400">
                <p>© Animea Tierkrematorium <?= date('Y') ?></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const flashMsg = document.getElementById('flash-message');

            // Disable button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Anmelden...';

            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    flashMsg.className = 'mb-4 p-4 rounded-lg border border-green-500/50 bg-green-900/20 text-green-300';
                    flashMsg.textContent = data.message;
                    flashMsg.classList.remove('hidden');

                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 500);
                } else {
                    flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
                    flashMsg.textContent = data.error || 'Anmeldung fehlgeschlagen';
                    flashMsg.classList.remove('hidden');

                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Anmelden';
                }
            } catch (error) {
                flashMsg.className = 'mb-4 p-4 rounded-lg border border-red-500/50 bg-red-900/20 text-red-300';
                flashMsg.textContent = 'Fehler: ' + error.message;
                flashMsg.classList.remove('hidden');

                submitBtn.disabled = false;
            submitBtn.textContent = 'Anmelden';
            }
        });
    </script>

</body>
</html>

