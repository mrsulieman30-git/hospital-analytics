<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'department',
        'description',
        'ip_address',
        'diff',
        'created_at',
    ];

    protected $casts = [
        'diff' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get a natural human-readable narrative sentence for the audit log.
     * e.g., "Dr. Mohamed Jama submitted Daily Report in Pharmacy Department."
     */
    public function getHumanNarrativeAttribute(): string
    {
        $userName = $this->user ? $this->user->name : 'System';
        $departmentName = $this->department ? ucfirst($this->department) : null;
        $desc = $this->description ?? '';

        switch ($this->action) {
            case 'SUBMIT':
                return $departmentName
                    ? "{$userName} submitted official Daily Report in {$departmentName} Department."
                    : "{$userName} submitted an official report.";
            case 'APPROVE':
                return $departmentName
                    ? "{$userName} reviewed and approved Daily Report in {$departmentName} Department."
                    : "{$userName} approved an official report.";
            case 'RETURN':
                return $departmentName
                    ? "{$userName} returned Daily Report in {$departmentName} Department for revision."
                    : "{$userName} returned a report for revision.";
            case 'CREATE':
                return $departmentName
                    ? "{$userName} recorded a new report draft in {$departmentName} Department."
                    : "{$userName} created a new record.";
            case 'UPDATE':
                return $departmentName
                    ? "{$userName} updated data records in {$departmentName} Department."
                    : "{$userName} updated records in system.";
            case 'DELETE':
                return $departmentName
                    ? "{$userName} deleted a record in {$departmentName} Department."
                    : "{$userName} deleted a record.";
            case 'LOGIN':
                if (str_contains($desc, 'Quick-switched session to')) {
                    $target = str_replace('Quick-switched session to ', '', $desc);

                    return "{$userName} switched active user session to {$target}.";
                }

                return "{$userName} logged into the system securely.";
            default:
                if (! empty($desc)) {
                    return $desc;
                }

                return $departmentName
                    ? "{$userName} performed {$this->action} in {$departmentName} Department."
                    : "{$userName} performed {$this->action}.";
        }
    }

    /**
     * Parse raw diff into human-readable field changes (no raw code or JSON dumps).
     */
    public function getHumanChangesAttribute(): array
    {
        if (empty($this->diff) || ! is_array($this->diff)) {
            return [];
        }

        $changes = [];

        // Scenario 1: Before and After Diff
        if (isset($this->diff['before'], $this->diff['after'])) {
            $before = is_array($this->diff['before']) ? $this->flattenArray($this->diff['before']) : [];
            $after = is_array($this->diff['after']) ? $this->flattenArray($this->diff['after']) : [];

            $allKeys = array_unique(array_merge(array_keys($before), array_keys($after)));

            foreach ($allKeys as $key) {
                $oldVal = $before[$key] ?? null;
                $newVal = $after[$key] ?? null;

                if ($oldVal !== $newVal) {
                    $friendlyKey = ucwords(str_replace(['_', '.'], ' ', $key));
                    $changes[] = [
                        'type' => 'modified',
                        'field' => $friendlyKey,
                        'old' => is_scalar($oldVal) ? (string) $oldVal : json_encode($oldVal),
                        'new' => is_scalar($newVal) ? (string) $newVal : json_encode($newVal),
                    ];
                }
            }
        } elseif (isset($this->diff['form_data']) && is_array($this->diff['form_data'])) {
            // Scenario 2: Snapshot of created / submitted report
            $flat = $this->flattenArray($this->diff['form_data']);
            $count = count($flat);
            $changes[] = [
                'type' => 'created',
                'field' => 'Department Form Snapshot',
                'old' => null,
                'new' => "Recorded {$count} metrics and clinical entries.",
            ];

            // Sample up to 6 key scalar metrics to display nicely
            $sampled = 0;
            foreach ($flat as $k => $v) {
                if ($sampled >= 6) {
                    break;
                }
                if (is_scalar($v) && $v !== '' && $v !== null && $v !== '0' && $v !== 0) {
                    $changes[] = [
                        'type' => 'entry',
                        'field' => ucwords(str_replace(['_', '.'], ' ', $k)),
                        'old' => null,
                        'new' => (string) $v,
                    ];
                    $sampled++;
                }
            }
        } else {
            // General key-value
            foreach ($this->diff as $k => $v) {
                $changes[] = [
                    'type' => 'info',
                    'field' => ucwords(str_replace(['_', '.'], ' ', $k)),
                    'old' => null,
                    'new' => is_scalar($v) ? (string) $v : 'Updated record',
                ];
            }
        }

        return $changes;
    }

    /**
     * Flatten nested associative arrays using dot notation.
     */
    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : "{$prefix}.{$key}";
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    public function scopeVisibleTo(Builder $query, ?User $viewer): Builder
    {
        if ($viewer && $viewer->isStealthSuperAdmin()) {
            return $query;
        }

        // Hide audit logs generated by stealth users
        return $query->whereHas('user', function ($q) {
            $q->where('is_stealth', false);
        })->orWhereNull('user_id');
    }
}
