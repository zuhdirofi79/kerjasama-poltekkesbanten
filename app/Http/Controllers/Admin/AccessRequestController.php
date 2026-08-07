<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccessRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccessDecisionRequest;
use App\Models\DocumentAccessRequest;
use App\Services\AccessRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessRequestController extends Controller
{
    public function __construct(
        private readonly AccessRequestService $accessRequestService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DocumentAccessRequest::class);

        $status = $request->string('status')->toString();

        $requests = DocumentAccessRequest::query()
            ->with(['document', 'user', 'reviewer'])
            ->when(
                $status !== '' && AccessRequestStatus::tryFrom($status),
                fn ($q) => $q->where('status', $status),
            )
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.access-requests.index', [
            'requests' => $requests,
            'currentStatus' => $status,
            'statuses' => AccessRequestStatus::cases(),
        ]);
    }

    public function show(DocumentAccessRequest $accessRequest): View
    {
        $this->authorize('view', $accessRequest);

        $accessRequest->load(['document.yearFolder.category.section', 'user', 'reviewer']);

        return view('admin.access-requests.show', compact('accessRequest'));
    }

    public function approve(AccessDecisionRequest $request, DocumentAccessRequest $accessRequest): RedirectResponse
    {
        $expiresAt = $request->filled('access_expires_at')
            ? $request->date('access_expires_at')
            : null;

        $this->accessRequestService->approve(
            $accessRequest,
            $request->user(),
            $request->validated('review_note'),
            $expiresAt,
        );

        return redirect()
            ->route('admin.access-requests.show', $accessRequest)
            ->with('status', 'Permintaan akses disetujui.');
    }

    public function reject(AccessDecisionRequest $request, DocumentAccessRequest $accessRequest): RedirectResponse
    {
        $this->accessRequestService->reject(
            $accessRequest,
            $request->user(),
            $request->validated('review_note'),
        );

        return redirect()
            ->route('admin.access-requests.show', $accessRequest)
            ->with('status', 'Permintaan akses ditolak.');
    }

    public function revoke(AccessDecisionRequest $request, DocumentAccessRequest $accessRequest): RedirectResponse
    {
        $this->accessRequestService->revoke(
            $accessRequest,
            $request->user(),
            $request->validated('review_note'),
        );

        return redirect()
            ->route('admin.access-requests.show', $accessRequest)
            ->with('status', 'Akses dokumen dicabut.');
    }
}
