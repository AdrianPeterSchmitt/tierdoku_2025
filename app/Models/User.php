<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * User Model
 * 
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $role
 * @property int|null $standort_id
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
     * Get standort
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
}

