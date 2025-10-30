<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\User;

/**
 * Authentication Middleware
 * 
 * Checks if user is logged in and loads user into context
 */
class AuthMiddleware
{
    /**
     * Handle the request
     * 
     * @param callable $next
     * @return mixed
     */
    public function handle(callable $next)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $user = User::find($_SESSION['user_id']);

        if (!$user) {
            unset($_SESSION['user_id']);
            header('Location: /login');
            exit;
        }

        // Add user to request context
        $_REQUEST['_user'] = $user;

        return $next();
    }
}


