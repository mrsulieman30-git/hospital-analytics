<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentForm extends Model
{
    protected $fillable = [
        'slug',
        'name_en',
        'name_so',
        'form_code',
        'cadence',
        'allowed_shifts',
        'dropdown_options',
        'field_settings',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'allowed_shifts' => 'array',
        'dropdown_options' => 'array',
        'field_settings' => 'array',
        'is_active' => 'boolean',
    ];

    public static function forSlug(string $slug): ?self
    {
        return self::where('slug', $slug)->first();
    }
}
