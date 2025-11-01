<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AuditLog Model
 * 
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property string $table_name
 * @property int $record_id
 * @property string|null $old_value
 * @property string|null $new_value
 * @property string|null $ip_address
 */
class AuditLog extends Model
{
    protected $table = 'audit_log';
    
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'old_value',
        'new_value',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}


