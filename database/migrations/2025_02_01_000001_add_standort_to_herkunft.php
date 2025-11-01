<?php

/**
 * Add standort_id to herkunft and unique index (standort_id, name)
 */

return function ($schema) {
    if ($schema->hasTable('herkunft')) {
        try {
            $schema->table('herkunft', function ($table) {
                $table->foreignId('standort_id')->nullable()->constrained('standort', 'standort_id')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // ignore if already exists
        }

        try {
            $schema->table('herkunft', function ($table) {
                $table->unique(['standort_id', 'name']);
            });
        } catch (\Throwable $e) {
            // ignore if already exists
        }
    }
};
