<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccessRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentAccessRequest;
use App\Models\InstitutionFolder;
use App\Models\User;
use App\Models\YearFolder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'years' => YearFolder::query()->count(),
            'institutions' => InstitutionFolder::query()->count(),
            'documents' => Document::query()->count(),
            'pending_requests' => DocumentAccessRequest::query()->pending()->count(),
            'users' => User::query()->count(),
        ];

        $recentPending = DocumentAccessRequest::query()
            ->with(['document', 'user'])
            ->where('status', AccessRequestStatus::Pending)
            ->latest('id')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPending'));
    }
}
