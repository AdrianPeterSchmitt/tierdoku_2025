<?php

/**
 * Create Tierdoku System Tables
 *
 * Run with: php migrate.php
 */

return function ($schema) {
    // Standort
    $schema->create('standort', function ($table) {
        $table->id('standort_id');
        $table->string('name')->unique();
        $table->boolean('aktiv')->default(true);
        $table->timestamps();
    });

    // Herkunft
    $schema->create('herkunft', function ($table) {
        $table->id('herkunft_id');
        $table->string('name');
        $table->integer('verwendungen_count')->default(0);
        $table->timestamps();
    });

    // Tierart
    $schema->create('tierart', function ($table) {
        $table->id('tierart_id');
        $table->string('bezeichnung')->unique();
    });

    // Extend users table if it exists (add columns for auth system)
    // Note: SQLite has limited ALTER TABLE support, so we try/catch
    if ($schema->hasTable('users')) {
        try {
            $schema->table('users', function ($table) {
                // Only add if not exists - SQLite doesn't support IF NOT EXISTS in all operations
                // So we just try to add and catch any errors
                $table->string('username')->unique()->nullable()->after('id');
                $table->string('password_hash')->nullable()->after('email');
                $table->enum('role', ['admin', 'manager', 'mitarbeiter'])->default('mitarbeiter')->after('password_hash');
                $table->foreignId('standort_id')->nullable()->constrained('standort', 'standort_id')->onDelete('set null')->after('role');
                $table->string('reset_token')->nullable()->after('standort_id');
                $table->timestamp('reset_token_expires')->nullable()->after('reset_token');
                $table->integer('failed_login_attempts')->default(0)->after('reset_token_expires');
                $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            });
        } catch (Exception $e) {
            // Columns might already exist, ignore error
        }
    }

    // Kremation
    $schema->create('kremation', function ($table) {
        $table->string('vorgangs_id', 20)->primary(); // String primary key with standort prefix format
        $table->date('eingangsdatum');
        $table->decimal('gewicht', 8, 2);
        $table->timestamp('einaescherungsdatum')->nullable();
        $table->foreignId('standort_id')->constrained('standort', 'standort_id');
        $table->foreignId('herkunft_id')->constrained('herkunft', 'herkunft_id');
        $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
        $table->softDeletes();
        $table->timestamps();
    });

    // Kremation_Tiere (Pivot Table)
    $schema->create('kremation_tiere', function ($table) {
        $table->string('kremation_id', 20); // String foreign key to match vorgangs_id
        $table->foreignId('tierart_id')->constrained('tierart', 'tierart_id')->onDelete('cascade');
        $table->integer('anzahl')->default(0);
        $table->primary(['kremation_id', 'tierart_id']);
        $table->foreign('kremation_id')->references('vorgangs_id')->on('kremation')->onDelete('cascade');
    });

    // Audit Log
    $schema->create('audit_log', function ($table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->enum('action', ['created', 'updated', 'deleted', 'restored', 'completed', 'bulk_deleted', 'bulk_completed']);
        $table->string('table_name');
        $table->string('record_id', 50); // String to support both integer IDs and string IDs (like vorgangs_id)
        $table->json('old_value')->nullable();
        $table->json('new_value')->nullable();
        $table->string('ip_address')->nullable();
        $table->timestamp('created_at')->useCurrent();

        $table->index(['table_name', 'record_id']);
    });

    // Notifications
    $schema->create('notifications', function ($table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->enum('type', ['info', 'warning', 'success', 'error']);
        $table->string('title');
        $table->text('message');
        $table->timestamp('read_at')->nullable();
        $table->timestamps();

        $table->index('user_id');
        $table->index('read_at');
    });

    // Login Attempts
    $schema->create('login_attempts', function ($table) {
        $table->id();
        $table->string('username');
        $table->string('ip_address');
        $table->boolean('success')->default(false);
        $table->timestamp('created_at')->useCurrent();

        $table->index(['username', 'created_at']);
        $table->index(['ip_address', 'created_at']);
    });
};
