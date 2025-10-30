<?php

/**
 * Seed Runner
 * Run seeds: php seed.php tierdoku
 */

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Setup Illuminate Database
$capsule = new Capsule();

$driver = $_ENV['DB_CONNECTION'] ?? 'sqlite';

if ($driver === 'sqlite') {
    $capsule->addConnection([
        'driver' => 'sqlite',
        'database' => __DIR__ . '/' . ($_ENV['DB_DATABASE'] ?? 'database/database.sqlite'),
        'prefix' => '',
    ]);
} else {
    $capsule->addConnection([
        'driver' => 'mysql',
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'database' => $_ENV['DB_DATABASE'] ?? '',
        'username' => $_ENV['DB_USERNAME'] ?? '',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ]);
}

$capsule->setAsGlobal();
$capsule->bootEloquent();

// Get seed name from command line
$seedName = $argv[1] ?? null;

if (!$seedName) {
    echo "Usage: php seed.php <seed_name>\n";
    echo "Available seeds: tierdoku\n";
    exit(1);
}

$seedFile = __DIR__ . '/database/seeds/' . $seedName . '_seeds.php';

if (!file_exists($seedFile)) {
    echo "❌ Seed file not found: {$seedFile}\n";
    exit(1);
}

echo "🌱 Running seed: {$seedName}\n";

try {
    $seed = require $seedFile;
    
    if (is_callable($seed)) {
        $seed();
    }
    
    echo "✨ Seed completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}


