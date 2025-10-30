<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Models\LoginAttempt;

/**
 * Rate Limiting Middleware
 * 
 * Prevents brute-force attacks by limiting login attempts
 */
class RateLimitMiddleware
{
    private const MAX_ATTEMPTS = 5;
    private const TIME_WINDOW = 900; // 15 minutes

    /**
     * Handle the request
     * 
     * @param callable $next
     * @return mixed
     */
    public function handle(callable $next)
    {
        $username = $_POST['username'] ?? '';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Count recent failed attempts
        $cutoffTime = date('Y-m-d H:i:s', time() - self::TIME_WINDOW);
        $recentAttempts = LoginAttempt::where('success', false)
            ->where('created_at', '>', $cutoffTime)
            ->where(function ($query) use ($username, $ipAddress) {
                $query->where('username', $username)
                      ->orWhere('ip_address', $ipAddress);
            })
            ->count();

        if ($recentAttempts >= self::MAX_ATTEMPTS) {
            http_response_code(429);
            echo json_encode([
                'error' => 'Zu viele fehlgeschlagene Login-Versuche. Bitte versuchen Sie es in 15 Minuten erneut.',
                'retry_after' => 900
            ]);
            exit;
        }

        return $next();
    }
}

