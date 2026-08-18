<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SatisfactionSurvey extends Model
{
    protected $fillable = [
        'public_token',
        'title',
        'document_id',
        'partnership_application_id',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (SatisfactionSurvey $survey): void {
            if (empty($survey->public_token)) {
                $survey->public_token = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_token';
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function partnershipApplication(): BelongsTo
    {
        return $this->belongsTo(PartnershipApplication::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SatisfactionResponse::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
