<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Services\AccessRequestService;
use App\Services\DocumentAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentRequestController extends Controller
{
    public function __construct(
        private readonly AccessRequestService $accessRequestService,
        private readonly DocumentAccessService $documentAccessService,
    ) {}

    public function show(Request $request, Document $document): View|RedirectResponse
    {
        abort_unless($document->is_active, 404);

        $user = $request->user();

        if ($this->accessRequestService->userCanViewDocument($user, $document)) {
            return redirect()->route('documents.view', $document);
        }

        $latest = $this->accessRequestService->latestForUser($user, $document);
        $statusLabel = $this->documentAccessService->publicStatusLabel($user, $document);

        return view('documents.request', compact('document', 'latest', 'statusLabel'));
    }

    public function store(Request $request, Document $document): RedirectResponse
    {
        $this->authorize('requestAccess', $document);

        abort_unless($document->is_active, 404);

        $validated = $request->validate([
            'request_message' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->accessRequestService->createRequest(
            $request->user(),
            $document,
            $validated['request_message'] ?? null,
        );

        return redirect()
            ->route('documents.request', $document)
            ->with('status', 'Permintaan akses berhasil dikirim. Menunggu persetujuan admin.');
    }
}
