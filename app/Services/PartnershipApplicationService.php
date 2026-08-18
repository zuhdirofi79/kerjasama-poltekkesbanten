<?php

namespace App\Services;

use App\Enums\PartnershipApplicationStatus;
use App\Enums\UserRole;
use App\Mail\NewPartnershipApplicationMail;
use App\Mail\PartnershipApplicationConfirmationMail;
use App\Models\PartnershipApplication;
use App\Models\PartnershipApplicationAttachment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartnershipApplicationService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly PartnershipApplicationStorageService $storageService,
    ) {}

    public function submit(array $data, array $files, ?string $ip = null, ?string $userAgent = null): PartnershipApplication
    {
        $writtenPaths = [];
        $disk = $this->storageService->disk();

        try {
            $application = DB::transaction(function () use ($data, $files, $ip, $userAgent, &$writtenPaths) {
                $token = (string) Str::uuid();
                $year = now()->year;
                $code = $this->generateSubmissionCode($year);
                $folderUuid = (string) Str::uuid();

                $application = PartnershipApplication::query()->create([
                    'public_token' => $token,
                    'submission_code' => $code,
                    'status' => PartnershipApplicationStatus::Submitted,
                    'applicant_type' => $data['applicant_type'] ?? null,
                    'institution_email' => $data['institution_email'],
                    'institution_name' => $data['institution_name'],
                    'institution_address' => $data['institution_address'],
                    'institution_phone' => $data['institution_phone'],
                    'institution_website' => $data['institution_website'] ?? null,
                    'legal_registration_number' => $data['legal_registration_number'] ?? null,
                    'institution_profile' => $data['institution_profile'],
                    'institution_type' => $data['institution_type'],
                    'institution_type_other' => $data['institution_type_other'] ?? null,
                    'partner_status' => $data['partner_status'],
                    'country' => $data['country'],
                    'authorized_officer_name' => $data['authorized_officer_name'],
                    'authorized_officer_position' => $data['authorized_officer_position'],
                    'contact_person_name' => $data['contact_person_name'],
                    'contact_person_position' => $data['contact_person_position'],
                    'contact_person_whatsapp' => $data['contact_person_whatsapp'],
                    'contact_person_email' => $data['contact_person_email'],
                    'proposal_type' => $data['proposal_type'],
                    'proposal_type_other' => $data['proposal_type_other'] ?? null,
                    'proposal_title' => $data['proposal_title'],
                    'proposal_purpose' => $data['proposal_purpose'],
                    'expected_benefits' => $data['expected_benefits'],
                    'target_units' => $data['target_units'],
                    'target_unit_other' => $data['target_unit_other'] ?? null,
                    'proposed_scopes' => $data['proposed_scopes'],
                    'proposed_scope_other' => $data['proposed_scope_other'] ?? null,
                    'document_form' => $data['document_form'],
                    'activity_description' => $data['activity_description'],
                    'target_description' => $data['target_description'],
                    'planned_start_date' => $data['planned_start_date'],
                    'planned_end_date' => $data['planned_end_date'],
                    'proposed_duration' => $data['proposed_duration'],
                    'proposed_duration_other' => $data['proposed_duration_other'] ?? null,
                    'activity_location' => $data['activity_location'],
                    'expected_poltekkes_contribution' => $data['expected_poltekkes_contribution'] ?? null,
                    'requires_funding' => $data['requires_funding'],
                    'funding_sources' => $data['funding_sources'],
                    'declaration_data_accurate' => true,
                    'declaration_willing_verification' => true,
                    'declaration_data_usage' => true,
                    'declarant_name' => $data['declarant_name'],
                    'declarant_position' => $data['declarant_position'],
                    'submission_date' => $data['submission_date'],
                    'submitted_at' => now(),
                    'applicant_ip' => $ip,
                    'applicant_user_agent' => $userAgent ? Str::limit($userAgent, 500, '') : null,
                ]);

                foreach (['official_request_letter', 'institution_profile', 'supporting_document', 'filled_template'] as $type) {
                    /** @var UploadedFile|null $file */
                    $file = $files[$type] ?? null;
                    if (! $file instanceof UploadedFile) {
                        continue;
                    }

                    $stored = $this->storageService->store($file, $type, $year, $folderUuid);
                    $writtenPaths[] = $stored['private_path'];

                    PartnershipApplicationAttachment::query()->create([
                        'partnership_application_id' => $application->id,
                        'type' => $type,
                        'original_filename' => $stored['original_filename'],
                        'stored_filename' => $stored['stored_filename'],
                        'private_path' => $stored['private_path'],
                        'mime_type' => $stored['mime_type'],
                        'extension' => $stored['extension'],
                        'file_size' => $stored['file_size'],
                        'uploaded_at' => now(),
                    ]);
                }

                $this->auditLogService->log('partnership_application.created', $application, null, [
                    'submission_code' => $application->submission_code,
                    'institution_name' => $application->institution_name,
                    'status' => $application->status->value,
                ]);

                return $application->fresh(['attachments']);
            });
        } catch (\Throwable $e) {
            foreach ($writtenPaths as $path) {
                Storage::disk($disk)->delete($path);
            }
            throw $e;
        }

        $this->notifyStaff($application);
        $this->notifyApplicant($application);

        return $application;
    }

    public function updateStatus(
        PartnershipApplication $application,
        PartnershipApplicationStatus $status,
        User $reviewer,
        ?string $note = null,
    ): PartnershipApplication {
        if (! $reviewer->role?->canManagePartnershipApplications()) {
            abort(403, 'Tidak berwenang meninjau pengajuan kerja sama.');
        }

        return DB::transaction(function () use ($application, $status, $reviewer, $note) {
            $old = [
                'status' => $application->status->value,
                'review_note' => $application->review_note,
            ];

            $application->update([
                'status' => $status,
                'review_note' => $note,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            $this->auditLogService->log('partnership_application.status_changed', $application, $old, [
                'status' => $status->value,
                'review_note' => $note,
            ], $reviewer);

            return $application->fresh(['attachments', 'reviewer']);
        });
    }

    public function downloadAttachment(
        PartnershipApplication $application,
        PartnershipApplicationAttachment $attachment,
        User $user,
    ): StreamedResponse {
        if ($attachment->partnership_application_id !== $application->id) {
            abort(404);
        }

        if (! $user->role?->canManagePartnershipApplications()) {
            abort(403);
        }

        $this->auditLogService->log('partnership_application.attachment_downloaded', $application, null, [
            'attachment_id' => $attachment->id,
            'type' => $attachment->type,
            'original_filename' => $attachment->original_filename,
        ], $user);

        return $this->storageService->download($attachment);
    }

    private function generateSubmissionCode(int $year): string
    {
        $prefix = config('partnership_applications.submission_code_prefix', 'KS');
        $pattern = $prefix.'-'.$year.'-';

        $latest = PartnershipApplication::query()
            ->where('submission_code', 'like', $pattern.'%')
            ->lockForUpdate()
            ->orderByDesc('submission_code')
            ->value('submission_code');

        $next = 1;
        if (is_string($latest) && preg_match('/-(\d+)$/', $latest, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%d-%06d', $prefix, $year, $next);
    }

    private function notifyStaff(PartnershipApplication $application): void
    {
        try {
            $staff = User::query()
                ->where('is_active', true)
                ->whereIn('role', [
                    UserRole::Superadmin1->value,
                    UserRole::Superadmin2->value,
                ])
                ->get();

            foreach ($staff as $user) {
                Mail::to($user->email)->send(new NewPartnershipApplicationMail($application));
            }
        } catch (\Throwable) {
            // Mail failure must not roll back the submission.
        }
    }

    private function notifyApplicant(PartnershipApplication $application): void
    {
        try {
            $recipients = collect([
                $application->institution_email,
                $application->contact_person_email,
            ])->filter()->unique()->values();

            foreach ($recipients as $email) {
                Mail::to($email)->send(new PartnershipApplicationConfirmationMail($application));
            }
        } catch (\Throwable) {
            // Mail failure must not roll back the submission.
        }
    }
}
