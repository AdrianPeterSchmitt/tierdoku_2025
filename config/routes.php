<?php

use App\Controllers\HomeController;
use App\Controllers\AboutController;

return [
    // GET Routes
    'GET' => [
        '/' => [HomeController::class, 'index'],
        '/about' => [AboutController::class, 'index'],
    ],

    // POST Routes
    'POST' => [
        // Add POST routes here
    ],
];
