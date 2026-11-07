<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalSetting extends Model
{
    protected $fillable = [
        'hospital_name',
        'tagline',
        'logo_path',
        'primary_color',
        'secondary_color',
        'accent_color',
        'currency',
        'contact_email',
        'contact_phone',
        'address',
        'submission_deadline',
        'default_locale',
        'extra_metadata',
    ];

    protected $casts = [
        'extra_metadata' => 'array',
    ];

    public static function current(): self
    {
        return self::firstOrCreate([], [
            'hospital_name' => 'Kaafi Hospitals',
            'tagline' => 'Specialized Hospital & Clinical Analytics',
            'logo_path' => 'images/kaafi_logo.png',
            'primary_color' => '#124E78',
            'secondary_color' => '#1E293B',
            'accent_color' => '#C81D25',
            'currency' => '$',
            'contact_email' => 'info@kaafihospital.so',
            'contact_phone' => '+252 61 555 0100',
            'address' => 'Wadajir District, Mogadishu, Somalia',
            'submission_deadline' => '08:30',
            'default_locale' => 'en',
        ]);
    }
}
