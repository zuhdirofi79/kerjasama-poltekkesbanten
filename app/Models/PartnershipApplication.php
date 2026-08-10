<?php

namespace App\Models;

use App\Enums\PartnershipApplicationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartnershipApplication extends Model
{
    protected $fillable = [
        'public_token',
        'submission_code',
        'status',
        'institution_email',
        'institution_name',
        'institution_address',
        'institution_phone',
        'institution_website',
        'legal_registration_number',
        'institution_profile',
        'institution_type',
        'institution_type_other',
        'partner_status',
        'country',
        'authorized_officer_name',
        'authorized_officer_position',
        'contact_person_name',
        'contact_person_position',
        'contact_person_whatsapp',
        'contact_person_email',
        'proposal_type',
        'proposal_type_other',
        'proposal_title',
        'proposal_purpose',
        'expected_benefits',
        'target_units',
        'target_unit_other',
        'proposed_scopes',
        'proposed_scope_other',
        'document_form',
        'activity_description',
        'target_description',
        'planned_start_date',
        'planned_end_date',
        'proposed_duration',
        'proposed_duration_other',
        'activity_location',
        'expected_poltekkes_contribution',
        'requires_funding',
        'funding_sources',
        'declaration_data_accurate',
        'declaration_willing_verification',
        'declaration_data_usage',
        'declarant_name',
        'declarant_position',
        'submission_date',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'review_note',
        'applicant_ip',
        'applicant_user_agent',
    ];

    protected $hidden = [
        'applicant_ip',
        'applicant_user_agent',
    ];

    protected function casts(): array
    {
        return [
            'status' => PartnershipApplicationStatus::class,
            'target_units' => 'array',
            'proposed_scopes' => 'array',
            'funding_sources' => 'array',
            'planned_start_date' => 'date',
            'planned_end_date' => 'date',
            'submission_date' => 'date',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'declaration_data_accurate' => 'boolean',
            'declaration_willing_verification' => 'boolean',
            'declaration_data_usage' => 'boolean',
        ];
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PartnershipApplicationAttachment::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function attachmentOfType(string $type): ?PartnershipApplicationAttachment
    {
        return $this->attachments->firstWhere('type', $type);
    }

    public function labelFor(string $configKey, ?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return (string) (config("partnership_applications.{$configKey}.{$value}") ?? $value);
    }

    public function labelsFor(string $configKey, ?array $values): array
    {
        return collect($values ?? [])
            ->map(fn ($value) => $this->labelFor($configKey, $value))
            ->all();
    }
}
