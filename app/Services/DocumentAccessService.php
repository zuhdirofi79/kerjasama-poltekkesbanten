<?php

namespace App\Services;

use App\Enums\AccessRequestStatus;
use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentAccessService
{
    public function __construct(
        private readonly AccessRequestService $accessRequestService,
    ) {}

    public function stream(Document $document, User $user, bool $download = false): StreamedResponse
    {
        if (! $this->accessRequestService->userCanViewDocument($user, $document)) {
            abort(403, 'Akses dokumen ditolak.');
        }

        $disk = Storage::disk(config('documents.private_disk'));

        if (! $disk->exists($document->private_file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $disposition = $download ? 'attachment' : 'inline';
        $filename = $document->original_filename;

        return response()->stream(function () use ($disk, $document) {
            $stream = $disk->readStream($document->private_file_path);
            if ($stream === false) {
                return;
            }
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => $disposition.'; filename="'.addslashes($filename).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    public function publicStatusLabel(?User $user, Document $document): string
    {
        if (! $user) {
            return 'Minta Akses';
        }

        if ($user->canAccessAdminPanel()) {
            return 'Buka Dokumen';
        }

        $request = $this->accessRequestService->latestForUser($user, $document);

        return match ($request?->status) {
            AccessRequestStatus::Pending => 'Menunggu Persetujuan',
            AccessRequestStatus::Approved => $request->isCurrentlyValid() ? 'Buka Dokumen' : 'Akses Berakhir',
            AccessRequestStatus::Rejected => 'Permintaan Ditolak',
            AccessRequestStatus::Revoked => 'Akses Dicabut',
            AccessRequestStatus::Expired => 'Akses Berakhir',
            default => 'Minta Akses',
        };
    }
}
