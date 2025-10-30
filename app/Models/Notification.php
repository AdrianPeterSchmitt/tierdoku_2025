<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Notification Model
 * 
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property string $title
 * @property string $message
 * @property string|null $read_at
 */
class Notification extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Get user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->read_at = now();
        $this->save();
    }

    /**
     * Check if read
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}


