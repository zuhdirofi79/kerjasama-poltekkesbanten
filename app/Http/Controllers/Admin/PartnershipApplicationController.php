<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PartnershipApplicationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePartnershipApplicationStatusRequest;
use App\Models\PartnershipApplication;
use App\Models\PartnershipApplicationAttachment;
use App\Services\PartnershipApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartnershipApplicationController extends Controller
{
    public function __construct(
        private readonly PartnershipApplicationService $service,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PartnershipApplication::class);

        $status = $request->string('status')->toString();
        $partnerStatus = $request->string('partner_status')->toString();
        $institutionType = $request->string('institution_type')->toString();
        $search = $request->string('q')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        $applications = PartnershipApplication::query()
            ->when(
                $status !== '' && PartnershipApplicationStatus::tryFrom($status),
                fn ($q) => $q->where('status', $status),
            )
            ->when(
                $partnerStatus !== '' && array_key_exists($partnerStatus, config('partnership_applications.partner_statuses', [])),
                fn ($q) => $q->where('partner_status', $partnerStatus),
            )
            ->when(
                $institutionType !== '' && array_key_exists($institutionType, config('partnership_applications.institution_types', [])),
                fn ($q) => $q->where('institution_type', $institutionType),
            )
            ->when($dateFrom !== '', fn ($q) => $q->whereDate('submission_date', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($q) => $q->whereDate('submission_date', '<=', $dateTo))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%'.$search.'%';
                $q->where(function ($inner) use ($like) {
                    $inner->where('submission_code', 'like', $like)
                        ->orWhere('institution_name', 'like', $like)
                        ->orWhere('institution_email', 'like', $like)
                        ->orWhere('contact_person_name', 'like', $like)
                        ->orWhere('contact_person_email', 'like', $like)
                        ->orWhere('proposal_title', 'like', $like);
                });
            })
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.partnership-applications.index', [
            'applications' => $applications,
            'filters' => [
                'status' => $status,
                'partner_status' => $partnerStatus,
                'institution_type' => $institutionType,
                'q' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'statuses' => PartnershipApplicationStatus::cases(),
            'partnerStatuses' => config('partnership_applications.partner_statuses'),
            'institutionTypes' => config('partnership_applications.institution_types'),
            'submittedCount' => PartnershipApplication::query()
                ->where('status', PartnershipApplicationStatus::Submitted)
                ->count(),
        ]);
    }

    public function show(PartnershipApplication $application): View
    {
        $this->authorize('view', $application);

        $application->load(['attachments', 'reviewer']);

        return view('admin.partnership-applications.show', [
            'application' => $application,
            'statuses' => PartnershipApplicationStatus::cases(),
        ]);
    }

    public function updateStatus(
        UpdatePartnershipApplicationStatusRequest $request,
        PartnershipApplication $application,
    ): RedirectResponse {
        $status = PartnershipApplicationStatus::from($request->validated('status'));

        $this->service->updateStatus(
            $application,
            $status,
            $request->user(),
            $request->validated('review_note'),
        );

        return redirect()
            ->route('admin.partnership-applications.show', $application)
            ->with('status', 'Status pengajuan diperbarui.');
    }

    public function downloadAttachment(
        PartnershipApplication $application,
        PartnershipApplicationAttachment $attachment,
    ): StreamedResponse {
        $this->authorize('downloadAttachment', $application);

        return $this->service->downloadAttachment($application, $attachment, request()->user());
    }
}
