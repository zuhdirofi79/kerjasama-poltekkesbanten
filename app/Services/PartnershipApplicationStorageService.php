<?php

namespace App\Services;

use App\Models\PartnershipApplicationAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartnershipApplicationStorageService
{
    public function disk(): string
    {
        return (string) config('partnership_applications.private_disk', 'private_docs');
    }

    public function store(UploadedFile $file, string $type, int $year, string $folderUuid): array
    {
        $this->assertAllowed($file, $type);

        $extension = strtolower($file->getClientOriginalExtension());
        $storedFilename = Str::uuid()->toString().'.'.$extension;
        $typeDir = match ($type) {
            'official_request_letter' => 'official-request',
            'institution_profile' => 'institution-profile',
            'supporting_document' => 'supporting',
            'filled_template' => 'filled-template',
            default => $type,
        };

        $root = trim((string) config('partnership_applications.storage_root', 'partnership-applications'), '/');
        $directory = "{$root}/{$year}/{$folderUuid}/{$typeDir}";
        $privatePath = "{$directory}/{$storedFilename}";

        if (! Storage::disk($this->disk())->putFileAs($directory, $file, $storedFilename)) {
            throw new RuntimeException('Gagal menyimpan lampiran pengajuan kerja sama.');
        }

        return [
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedFilename,
            'private_path' => $privatePath,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'extension' => $extension,
            'file_size' => $file->getSize() ?: 0,
        ];
    }

    public function download(PartnershipApplicationAttachment $attachment): StreamedResponse
    {
        $disk = Storage::disk($this->disk());

        if (! $disk->exists($attachment->private_path)) {
            abort(404, 'Berkas tidak ditemukan.');
        }

        return $disk->download(
            $attachment->private_path,
            $attachment->original_filename,
            [
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ],
        );
    }

    public function assertAllowed(UploadedFile $file, string $type): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $blocked = config('partnership_applications.blocked_extensions', []);
        $allowedForType = config("partnership_applications.allowed_extensions.{$type}", []);
        $maxMb = (int) config('partnership_applications.max_upload_mb', 10);
        $maxBytes = $maxMb * 1024 * 1024;

        if (in_array($extension, $blocked, true)) {
            throw ValidationException::withMessages([
                $type => 'Format file tidak diizinkan demi keamanan.',
            ]);
        }

        if (! in_array($extension, $allowedForType, true)) {
            throw ValidationException::withMessages([
                $type => 'Format file tidak didukung untuk jenis lampiran ini.',
            ]);
        }

        $allowedMimes = config("partnership_applications.allowed_mimes.{$extension}", []);
        $mime = (string) ($file->getMimeType() ?: '');
        if ($allowedMimes !== [] && ! in_array($mime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                $type => 'Tipe MIME file tidak valid.',
            ]);
        }

        if (($file->getSize() ?: 0) > $maxBytes) {
            throw ValidationException::withMessages([
                $type => "Ukuran file maksimal {$maxMb} MB.",
            ]);
        }
    }
}
