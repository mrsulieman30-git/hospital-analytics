<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutiveInsight extends Model
{
    protected $fillable = [
        'report_type',
        'period_date',
        'title',
        'executive_summary',
        'key_findings',
        'bottlenecks_and_root_causes',
        'action_plan',
        'medical_benchmarks',
        'status',
        'published_by',
        'is_ai_generated',
    ];

    protected $casts = [
        'period_date' => 'date',
        'key_findings' => 'array',
        'bottlenecks_and_root_causes' => 'array',
        'action_plan' => 'array',
        'medical_benchmarks' => 'array',
        'is_ai_generated' => 'boolean',
    ];

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
