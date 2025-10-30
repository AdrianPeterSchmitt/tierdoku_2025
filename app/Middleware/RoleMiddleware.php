<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\User;

/**
 * Role-based Authorization Middleware
 * 
 * Checks if user has required permission
 */
class RoleMiddleware
{
    /**
     * Handle the request
     * 
     * @param callable $next
     * @param string $requiredPermission
     * @return mixed
     */
    public function handle(callable $next, string $requiredPermission)
    {
        /** @var User|null $user */
        $user = $_REQUEST['_user'] ?? null;

        if (!$user) {
            http_response_code(403);
            echo json_encode(['error' => 'Not authenticated']);
            exit;
        }

        if (!$user->can($requiredPermission)) {
            http_response_code(403);
            echo json_encode(['error' => 'Insufficient permissions']);
            exit;
        }

        return $next();
    }
}


