<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeepseekConfig extends Model
{
    protected $fillable = [
        'api_key',
        'selected_model',
        'custom_system_prompt',
        'enable_web_search',
        'temperature',
        'updated_by',
    ];

    protected $casts = [
        'enable_web_search' => 'boolean',
        'temperature' => 'float',
    ];

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'selected_model' => 'deepseek-chat',
            'custom_system_prompt' => 'You are an elite Senior Hospital Operations & Clinical Analytics Director with 25+ years of experience in tertiary hospital governance, supply chain optimization, and clinical quality assurance. Analyze the provided multi-department hospital daily metrics, identify hidden bottlenecks, compute key clinical ratios, compare with international benchmarks (WHO, JCI), and generate a boardroom-ready executive report with clear root-cause breakdowns and prioritized operational interventions.',
            'enable_web_search' => true,
            'temperature' => 0.70,
        ]);
    }
}
