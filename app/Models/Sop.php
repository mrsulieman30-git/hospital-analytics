<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sop extends Model
{
    protected $fillable = [
        'title',
        'sop_code',
        'department',
        'category',
        'version',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'is_downloadable',
        'extracted_summary',
        'extracted_html',
        'extracted_metadata',
        'leaflet_images',
        'effective_date',
        'review_date',
        'author_name',
        'created_by',
        'status',
    ];

    protected $casts = [
        'is_downloadable' => 'boolean',
        'extracted_metadata' => 'array',
        'leaflet_images' => 'array',
        'effective_date' => 'date',
        'review_date' => 'date',
        'file_size' => 'integer',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPdf(): bool
    {
        return strtolower($this->file_type) === 'pdf';
    }

    public function isDocx(): bool
    {
        return in_array(strtolower($this->file_type), ['docx', 'doc']);
    }

    public function canBeDownloadedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isStealthSuperAdmin()) {
            return true;
        }

        return $this->is_downloadable;
    }

    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1).' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0).' KB';
        }

        return $bytes.' B';
    }

    public function getDepartmentBadgeColor(): string
    {
        return match ($this->department) {
            'laboratory' => 'bg-[#7C3AED] text-white',
            'pharmacy' => 'bg-[#10B981] text-white',
            'emergency' => 'bg-[#F59E0B] text-slate-900',
            'inpatient' => 'bg-[#0EA5E9] text-white',
            'reception' => 'bg-[#3B82F6] text-white',
            default => 'bg-[#124E78] text-white',
        };
    }
}
