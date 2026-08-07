<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'year_folder_id',
        'institution_folder_id',
        'title',
        'description',
        'original_filename',
        'stored_filename',
        'private_file_path',
        'preview_image_path',
        'mime_type',
        'extension',
        'file_size',
        'checksum',
        'is_active',
        'uploaded_by',
    ];

    protected $hidden = [
        'private_file_path',
        'stored_filename',
        'checksum',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function yearFolder(): BelongsTo
    {
        return $this->belongsTo(YearFolder::class, 'year_folder_id');
    }

    public function institutionFolder(): BelongsTo
    {
        return $this->belongsTo(InstitutionFolder::class, 'institution_folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function accessRequests(): HasMany
    {
        return $this->hasMany(DocumentAccessRequest::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->active()->whereNotNull('preview_image_path')->where('preview_image_path', '!=', '');
    }

    public function previewUrl(): ?string
    {
        if (! $this->preview_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->preview_image_path);
    }

    public function formattedSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
