<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Notification;

/**
 * Notification Service
 *
 * Handles user notifications
 */
class NotificationService
{
    /**
     * Create a notification for a user
     *
     * @param User $user The target user
     * @param string $type The notification type (info, warning, success, error)
     * @param string $title The notification title
     * @param string $message The notification message
     */
    public function create(User $user, string $type, string $title, string $message): void
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
        ]);
    }

    /**
     * Mark notification as read
     *
     * @param int $notificationId The notification ID
     */
    public function markAsRead(int $notificationId): void
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    /**
     * Get unread notifications for a user
     *
     * @param User $user The user
     * @return \Illuminate\Support\Collection
     */
    public function getUnread(User $user)
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get unread count for a user
     *
     * @param User $user The user
     * @return int
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Mark all notifications as read for a user
     *
     * @param User $user The user
     */
    public function markAllAsRead(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
