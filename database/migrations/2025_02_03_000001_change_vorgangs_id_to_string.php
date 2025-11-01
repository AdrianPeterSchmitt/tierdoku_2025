<?php

/**
 * Change vorgangs_id from INTEGER to VARCHAR with standort prefix format
 *
 * Format: {PREFIX}{NUMMER} (e.g. LAU001, SCH002)
 * Prefix: First 3 letters of standort name (uppercase)
 * Number: 3-digit sequential number per standort
 *
 * Run with: php migrate.php
 */

return function ($schema) {
    if (!$schema->hasTable('kremation')) {
        // Table doesn't exist yet, nothing to migrate
        // New installations will use string format directly from initial migration
        return;
    }

    try {
        // Get database connection
        $connection = $schema->getConnection();
        $driver = $connection->getDriverName();

        // Check if vorgangs_id is already a string (for new installations or already migrated)
        // This prevents running the migration twice
        try {
            $testQuery = $connection->table('kremation')->limit(1)->first();
            if ($testQuery) {
                $idValue = $testQuery->vorgangs_id;
                // If vorgangs_id is already a string (contains letters), skip migration
                if (!is_numeric($idValue) && preg_match('/[A-Za-z]/', (string)$idValue)) {
                    // Already migrated or new installation with string format
                    return;
                }
            }
        } catch (\Exception $e) {
            // If we can't check, proceed with migration
        }

        // Step 1: Get all kremations with their standorts and full data
        $kremations = $connection->table('kremation')
            ->join('standort', 'kremation.standort_id', '=', 'standort.standort_id')
            ->select(
                'kremation.*',
                'standort.name as standort_name'
            )
            ->orderBy('kremation.standort_id')
            ->orderBy('kremation.created_at', 'asc')
            ->get();

        if (count($kremations) === 0) {
            // No data to migrate, just alter column type if needed
            if ($driver === 'mysql') {
                try {
                    $connection->statement('ALTER TABLE kremation MODIFY COLUMN vorgangs_id VARCHAR(20) NOT NULL');
                    if ($schema->hasTable('kremation_tiere')) {
                        $connection->statement('ALTER TABLE kremation_tiere MODIFY COLUMN kremation_id VARCHAR(20) NOT NULL');
                    }
                } catch (\Exception $e) {
                    // Ignore if already VARCHAR
                }
            }
            return;
        }

        // Step 2: Generate migration map (old_id => new_id)
        $standortCounters = [];
        $migrationMap = [];
        $allKremationData = [];

        foreach ($kremations as $kremation) {
            $standortId = $kremation->standort_id;
            $standortName = $kremation->standort_name;
            $oldId = $kremation->vorgangs_id;

            // Generate prefix (first 3 letters, uppercase)
            $prefix = strtoupper(substr($standortName, 0, 3));
            if (strlen($prefix) < 3) {
                $prefix = str_pad($prefix, 3, 'X', STR_PAD_RIGHT);
            }

            // Get next number for this standort
            if (!isset($standortCounters[$standortId])) {
                $standortCounters[$standortId] = 1;
            } else {
                $standortCounters[$standortId]++;
            }

            // Format new ID: PREFIX + 3-digit number
            $newVorgangsId = sprintf('%s%03d', $prefix, $standortCounters[$standortId]);

            $migrationMap[$oldId] = $newVorgangsId;

            // Store full kremation data with new ID
            $kremationData = (array) $kremation;
            $kremationData['vorgangs_id'] = $newVorgangsId;
            unset($kremationData['standort_name']); // Remove joined column
            $allKremationData[] = $kremationData;
        }

        // Step 3: Update foreign keys in kremation_tiere
        if ($schema->hasTable('kremation_tiere')) {
            foreach ($migrationMap as $oldId => $newId) {
                $connection->table('kremation_tiere')
                    ->where('kremation_id', $oldId)
                    ->update(['kremation_id' => $newId]);
            }
        }

        // Step 4: Update audit_log
        if ($schema->hasTable('audit_log')) {
            // Update record_id values first
            foreach ($migrationMap as $oldId => $newId) {
                $connection->table('audit_log')
                    ->where('table_name', 'kremation')
                    ->where('record_id', $oldId)
                    ->update(['record_id' => $newId]);
            }

            // Update column type if not already string (MySQL/PostgreSQL only)
            if ($driver === 'mysql') {
                try {
                    $connection->statement('ALTER TABLE audit_log MODIFY COLUMN record_id VARCHAR(50) NOT NULL');
                } catch (\Exception $e) {
                    // Column might already be VARCHAR, ignore error
                }
            } elseif ($driver === 'pgsql') {
                try {
                    $connection->statement('ALTER TABLE audit_log ALTER COLUMN record_id TYPE VARCHAR(50)');
                } catch (\Exception $e) {
                    // Ignore if already VARCHAR
                }
            }
            // SQLite doesn't support ALTER COLUMN TYPE, but it's flexible with types
        }

        // Step 5: Handle column type change (database-specific)
        if ($driver === 'sqlite') {
            // SQLite: Recreate table with string primary key

            // Disable foreign key checks temporarily
            $connection->statement('PRAGMA foreign_keys = OFF');

            // Create temporary table with new structure
            $schema->create('kremation_temp', function ($table) {
                $table->string('vorgangs_id', 20)->primary();
                $table->date('eingangsdatum');
                $table->decimal('gewicht', 8, 2);
                $table->timestamp('einaescherungsdatum')->nullable();
                $table->foreignId('standort_id')->constrained('standort', 'standort_id');
                $table->foreignId('herkunft_id')->constrained('herkunft', 'herkunft_id');
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->softDeletes();
                $table->timestamps();
            });

            // Insert all data with new IDs
            foreach ($allKremationData as $data) {
                // Convert timestamps to proper format
                if (isset($data['created_at'])) {
                    $data['created_at'] = $data['created_at'];
                }
                if (isset($data['updated_at'])) {
                    $data['updated_at'] = $data['updated_at'];
                }
                if (isset($data['deleted_at']) && empty($data['deleted_at'])) {
                    unset($data['deleted_at']);
                }
                if (isset($data['einaescherungsdatum']) && empty($data['einaescherungsdatum'])) {
                    $data['einaescherungsdatum'] = null;
                }
                if (isset($data['created_by']) && empty($data['created_by'])) {
                    $data['created_by'] = null;
                }

                $connection->table('kremation_temp')->insert($data);
            }

            // Drop old table
            $schema->drop('kremation');

            // Rename temp table
            $connection->statement('ALTER TABLE kremation_temp RENAME TO kremation');

            // Re-enable foreign keys
            $connection->statement('PRAGMA foreign_keys = ON');

            // Update kremation_tiere table structure (if needed, SQLite handles this automatically)
            // But we need to recreate foreign key constraint
            if ($schema->hasTable('kremation_tiere')) {
                // Get all data from kremation_tiere
                $kremationTiereData = $connection->table('kremation_tiere')->get()->toArray();

                // Drop table
                $schema->drop('kremation_tiere');

                // Recreate with string foreign key
                $schema->create('kremation_tiere', function ($table) {
                    $table->string('kremation_id', 20);
                    $table->foreignId('tierart_id')->constrained('tierart', 'tierart_id')->onDelete('cascade');
                    $table->integer('anzahl')->default(0);
                    $table->primary(['kremation_id', 'tierart_id']);
                    $table->foreign('kremation_id')->references('vorgangs_id')->on('kremation')->onDelete('cascade');
                });

                // Re-insert data
                foreach ($kremationTiereData as $data) {
                    $connection->table('kremation_tiere')->insert((array) $data);
                }
            }

        } elseif ($driver === 'mysql') {
            // MySQL: Update IDs first, then alter column type

            // Update kremation table with new IDs
            foreach ($migrationMap as $oldId => $newId) {
                $connection->table('kremation')
                    ->where('vorgangs_id', $oldId)
                    ->update(['vorgangs_id' => $newId]);
            }

            // Alter column type
            try {
                $connection->statement('ALTER TABLE kremation MODIFY COLUMN vorgangs_id VARCHAR(20) NOT NULL');

                if ($schema->hasTable('kremation_tiere')) {
                    $connection->statement('ALTER TABLE kremation_tiere MODIFY COLUMN kremation_id VARCHAR(20) NOT NULL');
                }
            } catch (\Exception $e) {
                error_log('Column type change skipped: ' . $e->getMessage());
            }

        } else {
            // PostgreSQL or other databases
            // Update IDs first
            foreach ($migrationMap as $oldId => $newId) {
                $connection->table('kremation')
                    ->where('vorgangs_id', $oldId)
                    ->update(['vorgangs_id' => $newId]);
            }

            // Alter column type
            try {
                $connection->statement('ALTER TABLE kremation ALTER COLUMN vorgangs_id TYPE VARCHAR(20)');

                if ($schema->hasTable('kremation_tiere')) {
                    $connection->statement('ALTER TABLE kremation_tiere ALTER COLUMN kremation_id TYPE VARCHAR(20)');
                }
            } catch (\Exception $e) {
                error_log('Column type change skipped: ' . $e->getMessage());
            }
        }

    } catch (\Exception $e) {
        error_log('Migration error: ' . $e->getMessage());
        throw $e;
    }
};
