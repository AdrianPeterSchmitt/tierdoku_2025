<?php

/**
 * Create users table
 *
 * Run with: php migrate.php
 */

return function ($schema) {
    $schema->create('users', function ($table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamps();
    });
};
