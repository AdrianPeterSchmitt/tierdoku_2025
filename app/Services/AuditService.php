<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\AuditLog;

/**
 * Audit Service
 *
 * Handles audit logging for all data changes
 */
class AuditService
{
    /**
     * Log an action to audit trail
     *
     * @param User $user The user performing the action
     * @param string $action The action type
     * @param string $table The table name
     * @param int|string $recordId The record ID (int for most tables, string for kremation vorgangs_id)
     * @param array<string, mixed>|null $oldValue The old values (JSON)
     * @param array<string, mixed>|null $newValue The new values (JSON)
     * @param string|null $ipAddress The IP address
     */
    public function log(
        User $user,
        string $action,
        string $table,
        int|string $recordId,
        ?array $oldValue = null,
        ?array $newValue = null,
        ?string $ipAddress = null
    ): void {
        AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'table_name' => $table,
            'record_id' => $recordId,
            'old_value' => $oldValue ? json_encode($oldValue) : null,
            'new_value' => $newValue ? json_encode($newValue) : null,
            'ip_address' => $ipAddress ?? $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at' => now(),
        ]);
    }

    /**
     * Get change history for a record
     *
     * @param string $table The table name
     * @param int|string $recordId The record ID (int for most tables, string for kremation vorgangs_id)
     * @return \Illuminate\Support\Collection
     */
    public function getHistory(string $table, int|string $recordId)
    {
        return AuditLog::where('table_name', $table)
            ->where('record_id', $recordId)
            ->with('user:id,username')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
