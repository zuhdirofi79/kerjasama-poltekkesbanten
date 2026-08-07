<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\DocumentAccessService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentFileController extends Controller
{
    public function __construct(
        private readonly DocumentAccessService $documentAccessService,
    ) {}

    public function view(Request $request, Document $document): StreamedResponse
    {
        $this->authorize('view', $document);

        abort_unless($document->is_active || $request->user()->canAccessAdminPanel(), 404);

        return $this->documentAccessService->stream($document, $request->user(), download: false);
    }

    public function download(Request $request, Document $document): StreamedResponse
    {
        $this->authorize('download', $document);

        abort_unless($document->is_active || $request->user()->canAccessAdminPanel(), 404);

        return $this->documentAccessService->stream($document, $request->user(), download: true);
    }
}
