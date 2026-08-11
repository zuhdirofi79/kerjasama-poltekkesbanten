<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves ONLY public preview thumbnails from the public disk.
 * Never exposes private original documents.
 */
class DocumentPreviewController extends Controller
{
    public function show(Document $document): StreamedResponse
    {
        abort_unless($document->is_active, 404);

        $relative = $this->normalizedPreviewPath($document->preview_image_path);
        abort_unless($relative !== null, 404);

        $disk = Storage::disk(config('documents.public_preview_disk', 'public'));
        abort_unless($disk->exists($relative), 404);

        $mime = $disk->mimeType($relative) ?: 'image/jpeg';
        abort_unless(str_starts_with($mime, 'image/'), 404);

        return response()->stream(function () use ($disk, $relative): void {
            echo $disk->get($relative);
        }, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function normalizedPreviewPath(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        $path = str_replace('\\', '/', trim($path));
        $path = ltrim($path, '/');

        foreach ([
            'storage/app/public/',
            'app/public/',
            'public/',
            'storage/',
        ] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
            }
        }

        $path = ltrim($path, '/');

        // Must remain under the configured preview directory.
        $previewDir = trim((string) config('documents.preview_directory', 'previews'), '/');
        if (! str_starts_with($path, $previewDir.'/')) {
            return null;
        }

        // Reject traversal.
        if (str_contains($path, '..')) {
            return null;
        }

        return $path;
    }
}
