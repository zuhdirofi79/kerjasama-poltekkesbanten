<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePartnershipApplicationRequest;
use App\Models\PartnershipApplication;
use App\Services\PartnershipApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PartnershipApplicationController extends Controller
{
    public function __construct(
        private readonly PartnershipApplicationService $service,
    ) {}

    public function create(): View
    {
        return view('public.partnership-applications.create', [
            'options' => [
                'institution_types' => config('partnership_applications.institution_types'),
                'partner_statuses' => config('partnership_applications.partner_statuses'),
                'proposal_types' => config('partnership_applications.proposal_types'),
                'target_units' => config('partnership_applications.target_units'),
                'proposed_scopes' => config('partnership_applications.proposed_scopes'),
                'document_forms' => config('partnership_applications.document_forms'),
                'proposed_durations' => config('partnership_applications.proposed_durations'),
                'requires_funding_options' => config('partnership_applications.requires_funding_options'),
                'funding_sources' => config('partnership_applications.funding_sources'),
            ],
            'maxUploadMb' => (int) config('partnership_applications.max_upload_mb', 10),
            'today' => now()->toDateString(),
            'internalInstitution' => config('partnership_applications.internal_institution'),
            'prefill' => [
                'email' => auth()->user()?->email,
                'name' => auth()->user()?->name,
            ],
        ]);
    }

    public function store(StorePartnershipApplicationRequest $request): RedirectResponse
    {
        $application = $this->service->submit(
            $request->payload(),
            $request->uploadedFiles(),
            $request->ip(),
            $request->userAgent(),
        );

        return redirect()
            ->route('partnership-applications.success', $application)
            ->with('status', 'Pengajuan kerja sama berhasil dikirim.');
    }

    public function success(PartnershipApplication $application): View
    {
        return view('public.partnership-applications.success', compact('application'));
    }
}
