<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * User Model
 *
 * @property int $id
 * @property string $username
 * @property string $name
 * @property string $email
 * @property string $password_hash
 * @property string $role
 * @property int|null $standort_id
 * @property int|null $default_standort_id
 * @property string|null $reset_token
 * @property string|null $reset_token_expires
 * @property int $failed_login_attempts
 * @property string|null $locked_until
 */
class User extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'username',
        'name',
        'email',
        'password_hash',
        'role',
        'standort_id',
        'default_standort_id',
        'reset_token',
        'reset_token_expires',
        'failed_login_attempts',
        'locked_until',
    ];

    protected $casts = [
        'failed_login_attempts' => 'integer',
        'reset_token_expires' => 'datetime',
        'locked_until' => 'datetime',
    ];

    protected $hidden = [
        'password_hash',
        'reset_token',
    ];

    /**
     * Get standorte (many-to-many)
     */
    public function standorte(): BelongsToMany
    {
        return $this->belongsToMany(Standort::class, 'user_standort', 'user_id', 'standort_id')
            ->withTimestamps()
            ->where('aktiv', true); // Nur aktive Standorte
    }

    /**
     * Get standort (legacy - kept for backwards compatibility)
     * @deprecated Use standorte() instead
     */
    public function standort(): BelongsTo
    {
        return $this->belongsTo(Standort::class, 'standort_id', 'standort_id');
    }

    /**
     * Get notifications
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    /**
     * Get created kremations
     */
    public function createdKremations(): HasMany
    {
        return $this->hasMany(Kremation::class, 'created_by');
    }

    /**
     * Check if user has permission
     */
    public function can(string $permission): bool
    {
        $permissions = [
            'admin' => ['*'], // Admin kann alles
            'manager' => ['view', 'create', 'update', 'complete', 'delete', 'view_statistics'],
            'mitarbeiter' => ['view', 'create', 'update', 'complete'],
        ];

        $rolePermissions = $permissions[$this->role] ?? [];

        return in_array('*', $rolePermissions) || in_array($permission, $rolePermissions);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is manager
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Lock account
     */
    public function lockAccount(int $minutes = 15): void
    {
        $this->locked_until = now()->addMinutes($minutes);
        $this->save();
    }

    /**
     * Unlock account
     */
    public function unlockAccount(): void
    {
        $this->locked_until = null;
        $this->failed_login_attempts = 0;
        $this->save();
    }

    /**
     * Check if account is locked
     */
    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until > now();
    }

    /**
     * Check if user has access to a specific standort
     */
    public function hasStandort(int $standortId): bool
    {
        if ($this->isAdmin()) {
            return true; // Admins have access to all standorte
        }
        return $this->standorte()->where('standort_id', $standortId)->exists();
    }

    /**
     * Get all allowed standort IDs for this user
     */
    public function getAllowedStandortIds(): array
    {
        if ($this->isAdmin()) {
            // Admins see all active standorte
            return Standort::where('aktiv', true)->pluck('standort_id')->toArray();
        }
        return $this->standorte()->pluck('standort_id')->toArray();
    }

    /**
     * Get default standort ID for this user
     * Returns default_standort_id if set and user has access, otherwise first assigned standort
     */
    public function getDefaultStandortId(): ?int
    {
        if ($this->isAdmin()) {
            return null; // Admins have no default
        }

        // If default_standort_id is set and user still has access to it, return it
        if ($this->default_standort_id && $this->hasStandort($this->default_standort_id)) {
            return $this->default_standort_id;
        }

        // Otherwise, return first assigned standort
        $firstStandort = $this->standorte()->first();
        return $firstStandort ? $firstStandort->standort_id : null;
    }

    /**
     * Set default standort for this user
     */
    public function setDefaultStandort(int $standortId): bool
    {
        if (!$this->hasStandort($standortId)) {
            return false; // User doesn't have access to this standort
        }

        $this->default_standort_id = $standortId;
        return $this->save();
    }
}
