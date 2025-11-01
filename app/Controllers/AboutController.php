<?php

namespace App\Controllers;

class AboutController
{
    public function index(): string
    {
        return view('about', [
            'title' => 'Über uns',
        ]);
    }
}
