<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * LoginAttempt Model
 * 
 * @property int $id
 * @property string $username
 * @property string $ip_address
 * @property bool $success
 */
class LoginAttempt extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'username',
        'ip_address',
        'success',
        'created_at',
    ];

    protected $casts = [
        'success' => 'boolean',
    ];
}


