<?php

namespace App\Controllers;

class HomeController
{
    public function index(): string
    {
        return view('home', [
            'title' => 'Willkommen',
            'env' => $_ENV['APP_ENV'] ?? 'local',
        ]);
    }
}
