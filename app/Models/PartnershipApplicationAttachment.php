<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnershipApplicationAttachment extends Model
{
    protected $fillable = [
        'partnership_application_id',
        'type',
        'original_filename',
        'stored_filename',
        'private_path',
        'mime_type',
        'extension',
        'file_size',
        'uploaded_at',
    ];

    protected $hidden = [
        'private_path',
        'stored_filename',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
            'file_size' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PartnershipApplication::class, 'partnership_application_id');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'official_request_letter' => 'Surat resmi pengajuan',
            'institution_profile' => 'Profil instansi',
            'supporting_document' => 'Dokumen pendukung',
            'filled_template' => 'Form pengajuan (template)',
            default => $this->type,
        };
    }
}
