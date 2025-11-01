<?php

/**
 * Tierdoku Seed Data
 *
 * Run with: php seed.php tierdoku
 */

use Illuminate\Database\Capsule\Manager as Capsule;

return function () {
    // Standorte
    echo "Seeding Standorte...\n";
    $standorte = ['Laudenbach', 'Usingen', 'Schwarzwald'];
    foreach ($standorte as $name) {
        Capsule::table('standort')->insertOrIgnore([
            'name' => $name,
            'aktiv' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // Tierarten
    echo "Seeding Tierarten...\n";
    $tierarten = ['Vogel', 'Heimtier', 'Katze', 'Hund'];
    foreach ($tierarten as $bezeichnung) {
        Capsule::table('tierart')->insertOrIgnore([
            'bezeichnung' => $bezeichnung,
        ]);
    }

    // Admin-User (falls noch nicht vorhanden)
    echo "Seeding Admin-User...\n";
    $adminExists = Capsule::table('users')->where('username', 'admin')->exists();

    if (!$adminExists && Capsule::table('standort')->count() > 0) {
        $firstStandort = Capsule::table('standort')->first();

        Capsule::table('users')->insert([
            'username' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@animea.local',
            'password_hash' => password_hash('admin123', PASSWORD_ARGON2ID),
            'role' => 'admin',
            'standort_id' => $firstStandort->standort_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        echo "Admin-User erstellt: username=admin, password=admin123\n";
    } else {
        echo "Admin-User existiert bereits oder kein Standort vorhanden\n";
    }

    echo "✅ Seed-Daten erfolgreich eingefügt!\n";
};
