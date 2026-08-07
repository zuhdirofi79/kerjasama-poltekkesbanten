<?php

namespace App\Services;

use App\Enums\AccessRequestStatus;
use App\Enums\UserRole;
use App\Mail\AccessRequestDecisionMail;
use App\Mail\NewAccessRequestMail;
use App\Models\Document;
use App\Models\DocumentAccessRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AccessRequestService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function createRequest(User $user, Document $document, ?string $message = null): DocumentAccessRequest
    {
        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'document' => 'Akun tidak aktif.',
            ]);
        }

        $existingPending = DocumentAccessRequest::query()
            ->where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->where('status', AccessRequestStatus::Pending)
            ->exists();

        if ($existingPending) {
            throw ValidationException::withMessages([
                'document' => 'Permintaan akses untuk dokumen ini masih menunggu persetujuan.',
            ]);
        }

        $existingApproved = DocumentAccessRequest::query()
            ->where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->where('status', AccessRequestStatus::Approved)
            ->get()
            ->first(fn (DocumentAccessRequest $r) => $r->isCurrentlyValid());

        if ($existingApproved) {
            throw ValidationException::withMessages([
                'document' => 'Anda sudah memiliki akses aktif ke dokumen ini.',
            ]);
        }

        $request = DB::transaction(function () use ($user, $document, $message) {
            $request = DocumentAccessRequest::query()->create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'status' => AccessRequestStatus::Pending,
                'request_message' => $message,
                'requested_at' => now(),
            ]);

            $this->auditLogService->log('access_request.created', $request, null, [
                'document_id' => $document->id,
                'user_id' => $user->id,
            ], $user);

            return $request;
        });

        $this->notifyApprovers($request);

        return $request;
    }

    public function approve(
        DocumentAccessRequest $request,
        User $reviewer,
        ?string $note = null,
        ?\DateTimeInterface $expiresAt = null,
    ): DocumentAccessRequest {
        $this->assertCanReview($reviewer);

        $updated = DB::transaction(function () use ($request, $reviewer, $note, $expiresAt) {
            $old = ['status' => $request->status->value];

            $request->update([
                'status' => AccessRequestStatus::Approved,
                'review_note' => $note,
                'reviewed_at' => now(),
                'reviewed_by' => $reviewer->id,
                'access_expires_at' => $expiresAt,
            ]);

            $this->auditLogService->log('access_request.approved', $request, $old, [
                'status' => AccessRequestStatus::Approved->value,
                'access_expires_at' => $expiresAt?->format('c'),
            ], $reviewer);

            return $request->fresh(['user', 'document']);
        });

        $this->notifyRequester($updated);

        return $updated;
    }

    public function reject(DocumentAccessRequest $request, User $reviewer, ?string $note = null): DocumentAccessRequest
    {
        $this->assertCanReview($reviewer);

        $updated = DB::transaction(function () use ($request, $reviewer, $note) {
            $old = ['status' => $request->status->value];

            $request->update([
                'status' => AccessRequestStatus::Rejected,
                'review_note' => $note,
                'reviewed_at' => now(),
                'reviewed_by' => $reviewer->id,
            ]);

            $this->auditLogService->log('access_request.rejected', $request, $old, [
                'status' => AccessRequestStatus::Rejected->value,
            ], $reviewer);

            return $request->fresh(['user', 'document']);
        });

        $this->notifyRequester($updated);

        return $updated;
    }

    public function revoke(DocumentAccessRequest $request, User $reviewer, ?string $note = null): DocumentAccessRequest
    {
        $this->assertCanReview($reviewer);

        return DB::transaction(function () use ($request, $reviewer, $note) {
            $old = ['status' => $request->status->value];

            $request->update([
                'status' => AccessRequestStatus::Revoked,
                'review_note' => $note,
                'reviewed_at' => now(),
                'reviewed_by' => $reviewer->id,
            ]);

            $this->auditLogService->log('access_request.revoked', $request, $old, [
                'status' => AccessRequestStatus::Revoked->value,
            ], $reviewer);

            return $request->fresh(['user', 'document']);
        });
    }

    public function latestForUser(User $user, Document $document): ?DocumentAccessRequest
    {
        $request = DocumentAccessRequest::query()
            ->where('document_id', $document->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        $request?->markExpiredIfNeeded();

        return $request?->fresh();
    }

    public function userCanViewDocument(User $user, Document $document): bool
    {
        if ($user->canAccessAdminPanel()) {
            return true;
        }

        $request = $this->latestForUser($user, $document);

        return $request?->isCurrentlyValid() ?? false;
    }

    private function assertCanReview(User $reviewer): void
    {
        if (! $reviewer->role?->canManageAccessRequests()) {
            abort(403, 'Tidak berwenang meninjau permintaan akses.');
        }
    }

    private function notifyApprovers(DocumentAccessRequest $request): void
    {
        try {
            $approvers = User::query()
                ->where('is_active', true)
                ->whereIn('role', [UserRole::Superadmin2->value, UserRole::Superadmin1->value])
                ->get();

            foreach ($approvers as $approver) {
                Mail::to($approver->email)->send(new NewAccessRequestMail($request));
            }
        } catch (\Throwable) {
            // Email failure must not roll back the saved request.
        }
    }

    private function notifyRequester(DocumentAccessRequest $request): void
    {
        try {
            if ($request->user?->email) {
                Mail::to($request->user->email)->send(new AccessRequestDecisionMail($request));
            }
        } catch (\Throwable) {
            // Email failure must not roll back the decision.
        }
    }
}
