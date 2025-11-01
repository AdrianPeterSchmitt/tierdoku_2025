<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => __DIR__ . '/database/database.sqlite',
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Check herkunft table structure
echo "Checking herkunft table structure:\n";
try {
    $columns = Capsule::select('PRAGMA table_info(herkunft)');
    foreach ($columns as $col) {
        echo "  - {$col->name} ({$col->type}, nullable: " . ($col->notnull == 0 ? 'yes' : 'no') . ")\n";
    }
    
    // Try to manually add column if it doesn't exist
    $columnExists = false;
    foreach ($columns as $col) {
        if ($col->name === 'standort_id') {
            $columnExists = true;
            break;
        }
    }
    
    if (!$columnExists) {
        echo "\n⚠️  standort_id column does not exist! Trying to add it...\n";
        try {
            Capsule::statement('ALTER TABLE herkunft ADD COLUMN standort_id INTEGER REFERENCES standort(standort_id) ON DELETE CASCADE');
            echo "✅ Column added successfully!\n";
        } catch (\Exception $e) {
            echo "❌ Error adding column: " . $e->getMessage() . "\n";
        }
    } else {
        echo "\n✅ standort_id column exists!\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}





