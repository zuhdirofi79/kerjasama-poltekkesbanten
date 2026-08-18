<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\YearFolder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class DocumentStorageService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
    ) {}

    public function store(
        UploadedFile $file,
        UploadedFile $preview,
        YearFolder $yearFolder,
        ?DocumentFolder $folder = null,
        array $meta = [],
        ?int $uploadedBy = null,
    ): Document {
        $this->assertAllowedDocument($file);
        $this->assertAllowedPreview($preview);

        if ($folder && $folder->year_folder_id !== $yearFolder->id) {
            throw new InvalidArgumentException('Folder dokumen tidak cocok dengan folder tahun.');
        }

        $disk = config('documents.private_disk');
        $publicDisk = config('documents.public_preview_disk');

        // Physical path is year-scoped (UUID filename). Display hierarchy lives in DB only.
        $relativeDir = $this->buildRelativeDirectory($yearFolder);
        $extension = strtolower($file->getClientOriginalExtension());
        $storedFilename = Str::uuid()->toString().'.'.$extension;
        $privatePath = trim($relativeDir.'/'.$storedFilename, '/');

        $previewExt = strtolower($preview->getClientOriginalExtension() ?: 'jpg');
        $previewFilename = Str::uuid()->toString().'.'.$previewExt;
        $previewPath = trim(config('documents.preview_directory').'/'.$previewFilename, '/');

        $writtenPrivate = false;
        $writtenPreview = false;

        try {
            return DB::transaction(function () use (
                $file,
                $preview,
                $yearFolder,
                $folder,
                $meta,
                $uploadedBy,
                $disk,
                $publicDisk,
                $privatePath,
                $previewPath,
                $storedFilename,
                $extension,
                &$writtenPrivate,
                &$writtenPreview,
            ) {
                if (! Storage::disk($disk)->putFileAs(dirname($privatePath), $file, basename($privatePath))) {
                    throw new RuntimeException('Gagal menyimpan dokumen privat.');
                }
                $writtenPrivate = true;

                if (! Storage::disk($publicDisk)->putFileAs(dirname($previewPath), $preview, basename($previewPath))) {
                    throw new RuntimeException('Gagal menyimpan preview publik.');
                }
                $writtenPreview = true;

                $document = Document::query()->create([
                    'year_folder_id' => $yearFolder->id,
                    'document_folder_id' => $folder?->id,
                    'institution_folder_id' => $folder?->legacy_institution_folder_id,
                    'title' => $meta['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'description' => $meta['description'] ?? null,
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_filename' => $storedFilename,
                    'private_file_path' => $privatePath,
                    'preview_image_path' => $previewPath,
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'extension' => $extension,
                    'file_size' => $file->getSize() ?: 0,
                    'checksum' => hash_file('sha256', $file->getRealPath()),
                    'is_active' => (bool) ($meta['is_active'] ?? true),
                    'uploaded_by' => $uploadedBy,
                    'document_date' => $meta['document_date'] ?? null,
                    'cooperation_area' => $meta['cooperation_area'] ?? null,
                    'cooperation_subtype' => $meta['cooperation_subtype'] ?? null,
                    'cooperation_start_date' => $meta['cooperation_start_date'] ?? null,
                    'cooperation_end_date' => $meta['cooperation_end_date'] ?? null,
                ]);

                $this->auditLogService->log('document.uploaded', $document, null, [
                    'title' => $document->title,
                    'year_folder_id' => $document->year_folder_id,
                    'document_folder_id' => $document->document_folder_id,
                ]);

                return $document;
            });
        } catch (\Throwable $e) {
            if ($writtenPrivate) {
                Storage::disk($disk)->delete($privatePath);
            }
            if ($writtenPreview) {
                Storage::disk($publicDisk)->delete($previewPath);
            }
            throw $e;
        }
    }

    public function replaceFile(Document $document, UploadedFile $file): Document
    {
        $this->assertAllowedDocument($file);

        $disk = config('documents.private_disk');
        $extension = strtolower($file->getClientOriginalExtension());
        $storedFilename = Str::uuid()->toString().'.'.$extension;
        $directory = dirname($document->private_file_path);
        $privatePath = trim($directory.'/'.$storedFilename, '/');
        $oldPath = $document->private_file_path;

        return DB::transaction(function () use ($document, $file, $disk, $privatePath, $storedFilename, $extension, $oldPath) {
            if (! Storage::disk($disk)->putFileAs(dirname($privatePath), $file, basename($privatePath))) {
                throw new RuntimeException('Gagal mengganti dokumen privat.');
            }

            $document->update([
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $storedFilename,
                'private_file_path' => $privatePath,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'extension' => $extension,
                'file_size' => $file->getSize() ?: 0,
                'checksum' => hash_file('sha256', $file->getRealPath()),
            ]);

            Storage::disk($disk)->delete($oldPath);

            $this->auditLogService->log('document.replaced', $document);

            return $document->fresh();
        });
    }

    public function replacePreview(Document $document, UploadedFile $preview): Document
    {
        $this->assertAllowedPreview($preview);

        $publicDisk = config('documents.public_preview_disk');
        $previewExt = strtolower($preview->getClientOriginalExtension() ?: 'jpg');
        $previewFilename = Str::uuid()->toString().'.'.$previewExt;
        $previewPath = trim(config('documents.preview_directory').'/'.$previewFilename, '/');
        $oldPreview = $document->preview_image_path;

        return DB::transaction(function () use ($document, $preview, $publicDisk, $previewPath, $oldPreview) {
            if (! Storage::disk($publicDisk)->putFileAs(dirname($previewPath), $preview, basename($previewPath))) {
                throw new RuntimeException('Gagal mengganti preview.');
            }

            $document->update(['preview_image_path' => $previewPath]);

            if ($oldPreview) {
                Storage::disk($publicDisk)->delete($oldPreview);
            }

            $this->auditLogService->log('document.preview_replaced', $document);

            return $document->fresh();
        });
    }

    public function softDelete(Document $document): void
    {
        $document->delete();
        $this->auditLogService->log('document.soft_deleted', $document);
    }

    public function forceDelete(Document $document): void
    {
        $disk = config('documents.private_disk');
        $publicDisk = config('documents.public_preview_disk');
        $privatePath = $document->private_file_path;
        $previewPath = $document->preview_image_path;

        DB::transaction(function () use ($document, $disk, $publicDisk, $privatePath, $previewPath) {
            $this->auditLogService->log('document.force_deleted', $document, [
                'title' => $document->title,
            ]);
            $document->forceDelete();
            Storage::disk($disk)->delete($privatePath);
            if ($previewPath) {
                Storage::disk($publicDisk)->delete($previewPath);
            }
        });
    }

    public function assertAllowedDocument(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType() ?: '';

        if (in_array($extension, config('documents.blocked_extensions'), true)) {
            throw new InvalidArgumentException('Tipe file executable/berbahaya ditolak.');
        }

        $allowed = config('documents.allowed_extensions');
        if (! in_array($extension, $allowed, true)) {
            throw new InvalidArgumentException('Ekstensi file tidak diizinkan.');
        }

        $allowedMimes = config('documents.allowed_mimes.'.$extension, []);
        if ($allowedMimes && ! in_array($mime, $allowedMimes, true)) {
            throw new InvalidArgumentException('MIME type tidak sesuai ekstensi file.');
        }

        $maxBytes = config('documents.max_upload_mb') * 1024 * 1024;
        if (($file->getSize() ?: 0) > $maxBytes) {
            throw new InvalidArgumentException('Ukuran file melebihi batas upload.');
        }
    }

    public function assertAllowedPreview(UploadedFile $file): void
    {
        $mime = $file->getMimeType() ?: '';
        if (! in_array($mime, config('documents.preview_allowed_mimes'), true)) {
            throw new InvalidArgumentException('Preview harus berupa JPG, PNG, atau WEBP.');
        }

        $maxBytes = config('documents.preview_max_mb') * 1024 * 1024;
        if (($file->getSize() ?: 0) > $maxBytes) {
            throw new InvalidArgumentException('Ukuran preview melebihi batas.');
        }
    }

    private function buildRelativeDirectory(YearFolder $yearFolder): string
    {
        $yearFolder->loadMissing('category.section');

        $section = $yearFolder->category->section->type->value;
        $category = $yearFolder->category->slug;
        $year = (string) $yearFolder->year;

        return implode('/', ['kerjasama', $section, $category, $year]);
    }
}
