<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;
use App\Middleware\RateLimitMiddleware;

/**
 * Authentication Controller
 */
class AuthController
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    /**
     * Show login form
     * 
     * @return string
     */
    public function loginForm(): string
    {
        // Redirect if already logged in
        if ($this->authService->check()) {
            redirect('/kremation');
        }

        return view('auth/login');
    }

    /**
     * Process login
     * 
     * @return void
     */
    public function login(): void
    {
        // Apply rate limiting - temporarily disabled to debug DB issue
        // $rateLimit = new RateLimitMiddleware();
        // $rateLimit->handle(function () {
        //     // Continue with login
        // });

        header('Content-Type: application/json');

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Benutzername und Passwort sind erforderlich.']);
            return;
        }

        $user = $this->authService->login($username, $password, $ipAddress);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Ungültige Anmeldedaten.']);
            return;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Erfolgreich angemeldet.',
            'redirect' => '/kremation'
        ]);
    }

    /**
     * Logout
     * 
     * @return void
     */
    public function logout(): void
    {
        $this->authService->logout();
        redirect('/login');
    }

    /**
     * Extend session (for session timeout warning)
     * 
     * @return void
     */
    public function extendSession(): void
    {
        header('Content-Type: application/json');

        if (!$this->authService->check()) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        $this->authService->extendSession();

        echo json_encode(['success' => true]);
    }
}

