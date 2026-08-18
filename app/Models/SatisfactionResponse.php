<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SatisfactionResponse extends Model
{
    protected $fillable = [
        'satisfaction_survey_id',
        'respondent_name',
        'respondent_institution',
        'suggestion',
        'respondent_ip',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }

    public function survey(): BelongsTo
    {
        return $this->belongsTo(SatisfactionSurvey::class, 'satisfaction_survey_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SatisfactionAnswer::class);
    }
}
