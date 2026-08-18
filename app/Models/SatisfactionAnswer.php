<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SatisfactionAnswer extends Model
{
    protected $fillable = [
        'satisfaction_response_id',
        'satisfaction_question_id',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    public function response(): BelongsTo
    {
        return $this->belongsTo(SatisfactionResponse::class, 'satisfaction_response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SatisfactionQuestion::class, 'satisfaction_question_id');
    }
}
