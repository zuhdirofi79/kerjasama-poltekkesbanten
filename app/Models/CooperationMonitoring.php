<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CooperationMonitoring extends Model
{
    protected $fillable = [
        'document_id',
        'monitoring_date',
        'implementation_status',
        'progress_percentage',
        'notes',
        'follow_up',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'monitoring_date' => 'date',
            'progress_percentage' => 'integer',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
