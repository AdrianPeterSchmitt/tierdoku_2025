<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\NotificationService;
use App\Models\Notification;

/**
 * Notification Controller
 */
class NotificationController
{
    public function __construct(
        private NotificationService $notificationService
    ) {
    }

    /**
     * Get unread count
     * 
     * @return void
     */
    public function unreadCount(): void
    {
        $user = $_REQUEST['_user'] ?? null;

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }

        header('Content-Type: application/json');

        $count = $this->notificationService->getUnreadCount($user);

        echo json_encode(['count' => $count]);
    }
}


