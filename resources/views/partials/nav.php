<?php /** @var \App\Models\User|null $user */ $user = $_REQUEST['_user'] ?? null; ?>
<header class="border-b border-gray-700/50 backdrop-blur-sm bg-gray-900/50 sticky top-0 z-50" x-data="{ open:false }">
    <div class="w-full px-4 py-4">
        <div class="flex items-center justify-between gap-4 text-white">
            <div class="flex items-center gap-3">
                <button @click="open = !open" class="p-2 rounded-lg bg-gray-800 hover:bg-gray-700 border border-gray-700">
                    <!-- Burger Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5h14a1 1 0 100-2H3a1 1 0 100 2zm14 4H3a1 1 0 100 2h14a1 1 0 100-2zm0 6H3a1 1 0 100 2h14a1 1 0 100-2z" clip-rule="evenodd"/></svg>
                </button>
                <a href="/" class="text-xl font-bold bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">Dokumentation der anonymen Tiere</a>
            </div>
            <!-- Desktop: keine Inline-Menüs, alles im Burger -->
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open" x-cloak class="border-t border-gray-800 bg-gray-900/95 text-white">
        <nav class="w-full px-4 py-3 grid gap-2">
            <!-- Hauptfunktionen -->
            <a href="/kremation" class="px-3 py-2 rounded-lg hover:bg-gray-800">🔥 Kremationen</a>
            <a href="/statistics" class="px-3 py-2 rounded-lg hover:bg-gray-800">📊 Statistiken</a>
            
            <!-- Scanner-Funktionen -->
            <div class="border-t border-gray-700/50 my-1"></div>
            <a href="/kremation/scan" class="px-3 py-2 rounded-lg hover:bg-gray-800 bg-green-900/20 border border-green-500/30">📷 QR-Scanner (Schnell)</a>
            <a href="/kremation/batch-scan" class="px-3 py-2 rounded-lg hover:bg-gray-800">📦 Batch-Scan (Mehrere)</a>
            
            <!-- Verwaltung (Admin & Manager) -->
            <?php if ($user && ($user->isAdmin() || $user->isManager())): ?>
            <div class="border-t border-gray-700/50 my-1"></div>
            <a href="/herkunft" class="px-3 py-2 rounded-lg hover:bg-gray-800">🏢 Herkünfte</a>
            <a href="/standort" class="px-3 py-2 rounded-lg hover:bg-gray-800">📍 Standorte</a>
            <?php endif; ?>
            
            <!-- Admin-Funktionen -->
            <?php if ($user && $user->isAdmin()): ?>
            <div class="border-t border-gray-700/50 my-1"></div>
            <a href="/users" class="px-3 py-2 rounded-lg hover:bg-gray-800">👥 Benutzer</a>
            <a href="/config" class="px-3 py-2 rounded-lg hover:bg-gray-800">⚙️ Konfiguration</a>
            <?php endif; ?>
            
            <!-- Benutzer-Bereich -->
            <div class="border-t border-gray-700/50 my-1"></div>
            <?php if ($user): ?>
            <div class="px-3 py-2 text-gray-300">👤 <?= htmlspecialchars($user->username) ?></div>
            <a href="/logout" class="px-3 py-2 rounded-lg hover:bg-gray-800">🚪 Logout</a>
            <?php else: ?>
            <a href="/login" class="px-3 py-2 rounded-lg bg-blue-500 hover:bg-blue-600 text-white">🔐 Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<style>
[x-cloak] { display: none !important; }
</style>


