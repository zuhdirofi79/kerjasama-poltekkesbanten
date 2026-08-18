<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\CooperationExpiryService;
use Illuminate\View\View;

class ExpiryNotificationController extends Controller
{
    public function __construct(
        private readonly CooperationExpiryService $expiryService,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Document::class);

        $today = now()->startOfDay();
        $soonLimit = $today->copy()->addDays($this->expiryService->warningDays());

        $documents = Document::query()
            ->with(['yearFolder.category.section', 'documentFolder', 'institutionFolder'])
            ->whereNotNull('cooperation_end_date')
            ->whereDate('cooperation_end_date', '<=', $soonLimit->toDateString())
            ->orderBy('cooperation_end_date')
            ->paginate(30);

        return view('admin.expiry.index', compact('documents'));
    }
}
