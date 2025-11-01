<?php

/**
 * Create user_standort Pivot Table and add default_standort_id to users
 * 
 * Run with: php migrate.php
 */

return function ($schema) {
    // Create user_standort pivot table
    if (!$schema->hasTable('user_standort')) {
        $schema->create('user_standort', function ($table) {
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('standort_id')->constrained('standort', 'standort_id')->onDelete('cascade');
            $table->timestamps();
            
            // Composite primary key
            $table->primary(['user_id', 'standort_id']);
            
            // Indexes for performance
            $table->index('user_id');
            $table->index('standort_id');
        });
    }
    
    // Add default_standort_id to users table
    if ($schema->hasTable('users')) {
        try {
            if (!$schema->hasColumn('users', 'default_standort_id')) {
                $schema->table('users', function ($table) {
                    $table->foreignId('default_standort_id')->nullable()->constrained('standort', 'standort_id')->onDelete('set null')->after('standort_id');
                });
            }
        } catch (Exception $e) {
            // Column might already exist, ignore error
        }
        
        // Migrate existing standort_id values to pivot table
        try {
            $users = $schema->getConnection()->table('users')
                ->whereNotNull('standort_id')
                ->get(['id', 'standort_id']);
            
            foreach ($users as $user) {
                // Check if relationship already exists
                $exists = $schema->getConnection()->table('user_standort')
                    ->where('user_id', $user->id)
                    ->where('standort_id', $user->standort_id)
                    ->exists();
                
                if (!$exists) {
                    $schema->getConnection()->table('user_standort')->insert([
                        'user_id' => $user->id,
                        'standort_id' => $user->standort_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                // Set default_standort_id if not already set
                $schema->getConnection()->table('users')
                    ->where('id', $user->id)
                    ->whereNull('default_standort_id')
                    ->update(['default_standort_id' => $user->standort_id]);
            }
        } catch (Exception $e) {
            // Migration might fail if table doesn't exist yet, that's okay
            // It will be run again after tables are created
        }
    }
};

