<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLoggerService
{
    public static function log(
        string $action,
        string $description,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $department = null,
        ?array $diff = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => strtoupper($action),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'department' => $department,
            'description' => $description,
            'ip_address' => Request::ip() ?? '127.0.0.1',
            'diff' => $diff,
            'created_at' => now(),
        ]);
    }
}
