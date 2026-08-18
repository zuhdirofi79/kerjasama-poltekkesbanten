<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCooperationMonitoringRequest;
use App\Http\Requests\Admin\StoreSatisfactionSurveyRequest;
use App\Models\CooperationCategory;
use App\Models\CooperationMonitoring;
use App\Models\Document;
use App\Models\SatisfactionQuestion;
use App\Services\CooperationExpiryService;
use App\Services\DocumentSearchQuery;
use App\Services\SatisfactionSurveyService;
use App\Support\CooperationTaxonomy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonitoringController extends Controller
{
    public function __construct(
        private readonly DocumentSearchQuery $documentSearchQuery,
        private readonly CooperationExpiryService $expiryService,
        private readonly SatisfactionSurveyService $surveyService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Document::class);

        $filters = $this->documentSearchQuery->filtersFromRequest($request);
        $query = Document::query()
            ->with([
                'yearFolder.category.section',
                'documentFolder',
                'institutionFolder',
                'satisfactionSurveys.responses.answers',
            ]);

        $year = (string) ($filters['year'] ?? '');
        if ($year !== '' && ctype_digit($year)) {
            $query->whereHas('yearFolder', fn ($q) => $q->where('year', (int) $year));
        }

        $expiry = (string) ($filters['expiry_status'] ?? '');
        if ($expiry !== '') {
            $this->expiryService->applyStatusFilter($query, $expiry);
        }

        $documents = $this->documentSearchQuery
            ->apply($query, $filters)
            ->paginate(25)
            ->withQueryString();

        $today = now()->startOfDay();
        $soonLimit = $today->copy()->addDays($this->expiryService->warningDays());

        $stats = [
            'active' => Document::query()
                ->whereNotNull('cooperation_end_date')
                ->whereDate('cooperation_end_date', '>', $soonLimit->toDateString())
                ->count(),
            'expiring_soon' => Document::query()
                ->whereNotNull('cooperation_end_date')
                ->whereDate('cooperation_end_date', '>=', $today->toDateString())
                ->whereDate('cooperation_end_date', '<=', $soonLimit->toDateString())
                ->count(),
            'expired' => Document::query()
                ->whereNotNull('cooperation_end_date')
                ->whereDate('cooperation_end_date', '<', $today->toDateString())
                ->count(),
        ];

        $analytics = $this->surveyService->analytics();

        return view('admin.monitoring.index', [
            'documents' => $documents,
            'filters' => $filters,
            'stats' => $stats,
            'analytics' => $analytics,
            'areas' => CooperationTaxonomy::areas(),
            'subtypes' => CooperationTaxonomy::subtypes(),
            'questions' => SatisfactionQuestion::query()->ordered()->get(),
            'categories' => CooperationCategory::query()->with('section')->orderBy('name')->get(),
        ]);
    }

    public function storeNote(StoreCooperationMonitoringRequest $request, Document $document): RedirectResponse
    {
        CooperationMonitoring::query()->create([
            'document_id' => $document->id,
            'monitoring_date' => $request->validated('monitoring_date'),
            'implementation_status' => $request->validated('implementation_status'),
            'progress_percentage' => $request->validated('progress_percentage'),
            'notes' => $request->validated('notes'),
            'follow_up' => $request->validated('follow_up'),
            'created_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Catatan monitoring disimpan.');
    }

    public function storeSurvey(StoreSatisfactionSurveyRequest $request): RedirectResponse
    {
        $survey = $this->surveyService->createSurvey(
            $request->integer('document_id') ?: null,
            null,
            $request->validated('title'),
            $request->user()->id,
        );

        return back()->with('status', 'Tautan survei: '.route('satisfaction.show', $survey));
    }
}
