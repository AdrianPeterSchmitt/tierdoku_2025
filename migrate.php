<?php

/**
 * Migration Runner
 * Run migrations: php migrate.php
 */

require __DIR__ . '/vendor/autoload.php';

// Define base_path helper for Illuminate
if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        return __DIR__ . ($path ? '/' . ltrim($path, '/') : '');
    }
}

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
    $dbPath = __DIR__ . '/' . ($_ENV['DB_DATABASE'] ?? 'database/database.sqlite');
    $capsule->addConnection([
        'driver' => 'sqlite',
        'database' => $dbPath,
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

// Create migrations table if not exists
if (!Capsule::schema()->hasTable('migrations')) {
    Capsule::schema()->create('migrations', function ($table) {
        $table->string('migration')->unique();
        $table->timestamp('run_at');
    });
    echo "Created migrations tracking table\n";
}

// Get all migration files
$migrationsDir = __DIR__ . '/database/migrations';
$migrations = glob($migrationsDir . '/*.php');

// Get already run migrations
$runMigrations = Capsule::table('migrations')->pluck('migration')->toArray();

$executed = 0;

foreach ($migrations as $migrationFile) {
    $migrationName = basename($migrationFile);

    // Skip if already run
    if (in_array($migrationName, $runMigrations)) {
        echo "⏭️  Skipping: {$migrationName}\n";
        continue;
    }

    // Require and run migration
    echo "🔄 Running: {$migrationName}\n";

    try {
        $migration = require $migrationFile;

        if (is_callable($migration)) {
            $migration(Capsule::schema());
        }

        // Mark as run
        Capsule::table('migrations')->insert([
            'migration' => $migrationName,
            'run_at' => date('Y-m-d H:i:s'),
        ]);

        $executed++;
        echo "✅ Completed: {$migrationName}\n";
    } catch (Exception $e) {
        echo "❌ Error in {$migrationName}: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if ($executed === 0) {
    echo "✨ All migrations are up to date!\n";
} else {
    echo "\n✨ Migrated {$executed} file(s) successfully!\n";
}
