<?php

namespace App\Models;

use App\Enums\AccessRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAccessRequest extends Model
{
    protected $fillable = [
        'document_id',
        'user_id',
        'status',
        'request_message',
        'review_note',
        'requested_at',
        'reviewed_at',
        'reviewed_by',
        'access_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AccessRequestStatus::class,
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'access_expires_at' => 'datetime',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', AccessRequestStatus::Pending);
    }

    public function isCurrentlyValid(): bool
    {
        if ($this->status !== AccessRequestStatus::Approved) {
            return false;
        }

        if ($this->access_expires_at && $this->access_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function markExpiredIfNeeded(): bool
    {
        if ($this->status === AccessRequestStatus::Approved
            && $this->access_expires_at
            && $this->access_expires_at->isPast()) {
            $this->update(['status' => AccessRequestStatus::Expired]);

            return true;
        }

        return false;
    }
}
