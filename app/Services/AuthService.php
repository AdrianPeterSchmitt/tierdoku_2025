<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\LoginAttempt;

/**
 * Authentication Service
 *
 * Handles user authentication and session management
 */
class AuthService
{
    /**
     * Login user
     *
     * @param string $username
     * @param string $password
     * @param string $ipAddress
     * @return User|false
     */
    public function login(string $username, string $password, string $ipAddress): User|false
    {
        // Log login attempt (will be done before login by RateLimitMiddleware)
        $this->logLoginAttempt($username, $ipAddress, false);

        $user = User::where('username', $username)->first();

        if (!$user) {
            return false;
        }

        // Check if account is locked
        if ($user->isLocked()) {
            return false;
        }

        // Verify password
        if (!password_verify($password, $user->password_hash)) {
            $user->failed_login_attempts++;

            // Lock after 5 failed attempts
            if ($user->failed_login_attempts >= 5) {
                $user->lockAccount(15);
            }

            $user->save();
            return false;
        }

        // Success - reset failed attempts and unlock
        $user->unlockAccount();

        // Start session
        session_start();
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['last_activity'] = time();

        // Regenerate session ID for security
        session_regenerate_id(true);

        // Log successful attempt
        $this->logLoginAttempt($username, $ipAddress, true);

        return $user;
    }

    /**
     * Logout user
     *
     * @return void
     */
    public function logout(): void
    {
        session_start();
        session_destroy();
    }

    /**
     * Get current user from session
     *
     * @return User|null
     */
    public function currentUser(): ?User
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        return User::find($_SESSION['user_id']);
    }

    /**
     * Check if user is logged in
     *
     * @return bool
     */
    public function check(): bool
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['last_activity']);
    }

    /**
     * Check if user has permission
     *
     * @param string $permission
     * @return bool
     */
    public function can(string $permission): bool
    {
        $user = $this->currentUser();

        if (!$user) {
            return false;
        }

        return $user->can($permission);
    }

    /**
     * Generate password reset token
     *
     * @param string $email
     * @return string
     */
    public function generateResetToken(string $email): string
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            throw new \InvalidArgumentException('User not found');
        }

        $token = bin2hex(random_bytes(32));

        $user->reset_token = $token;
        $user->reset_token_expires = now()->addHours(1);
        $user->save();

        return $token;
    }

    /**
     * Reset password using token
     *
     * @param string $token
     * @param string $newPassword
     * @return bool
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $user = User::where('reset_token', $token)
            ->where('reset_token_expires', '>', now())
            ->first();

        if (!$user) {
            return false;
        }

        $user->password_hash = password_hash($newPassword, PASSWORD_ARGON2ID);
        $user->reset_token = null;
        $user->reset_token_expires = null;
        $user->save();

        return true;
    }

    /**
     * Unlock user account
     *
     * @param int $userId
     * @return void
     */
    public function unlockUser(int $userId): void
    {
        $user = User::find($userId);

        if ($user) {
            $user->unlockAccount();
        }
    }

    /**
     * Extend session (for session timeout warning)
     *
     * @return void
     */
    public function extendSession(): void
    {
        if (isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
    }

    /**
     * Log login attempt
     *
     * @param string $username
     * @param string $ipAddress
     * @param bool $success
     * @return void
     */
    private function logLoginAttempt(string $username, string $ipAddress, bool $success): void
    {
        LoginAttempt::create([
            'username' => $username,
            'ip_address' => $ipAddress,
            'success' => $success,
            'created_at' => now(),
        ]);
    }
}
