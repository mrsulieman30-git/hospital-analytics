<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    protected $fillable = [
        'department',
        'report_date',
        'shift',
        'form_code',
        'lead_staff_name',
        'status',
        'created_by',
        'submitted_by',
        'audited_by',
        'submitted_at',
        'audited_at',
        'audit_feedback',
        'form_data',
        'summary_metrics',
    ];

    protected $casts = [
        'report_date' => 'date',
        'submitted_at' => 'datetime',
        'audited_at' => 'datetime',
        'form_data' => 'array',
        'summary_metrics' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function scopeForDepartment(Builder $query, string $department): Builder
    {
        return $query->where('department', $department);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeForDateRange(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
            'submitted' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
            'returned' => 'bg-rose-500/10 text-rose-500 border-rose-500/20',
            default => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
        };
    }

    /**
     * Automatically determine the operational shift based on the hospital schedule:
     * - Morning shift: 08:00 to 14:00 (8 AM to 2 PM)
     * - Noon / Afternoon shift: 14:00 to 21:00 (2 PM to 9 PM)
     * - Night shift: 21:00 to 08:00 (9 PM to 8 AM)
     */
    public static function determineCurrentShift(?CarbonInterface $time = null): string
    {
        $time = $time ?? now();
        $hour = (int) $time->format('G');

        if ($hour >= 8 && $hour < 14) {
            return 'Morning';
        }

        if ($hour >= 14 && $hour < 21) {
            return 'Afternoon';
        }

        return 'Night';
    }

    /**
     * Get shift display label with operational hours.
     */
    public static function getShiftLabel(string $shift): string
    {
        return match ($shift) {
            'Morning' => 'Morning Shift (08:00 - 14:00)',
            'Afternoon', 'Noon' => 'Noon Shift (14:00 - 21:00)',
            'Night' => 'Night Shift (21:00 - 08:00)',
            default => $shift,
        };
    }
}
