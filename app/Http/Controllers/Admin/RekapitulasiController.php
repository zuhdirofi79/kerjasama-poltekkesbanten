<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CooperationCategory;
use App\Models\Document;
use App\Services\DocumentSearchQuery;
use App\Services\RekapitulasiService;
use App\Support\CooperationTaxonomy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RekapitulasiController extends Controller
{
    public function __construct(
        private readonly RekapitulasiService $rekapitulasiService,
        private readonly DocumentSearchQuery $documentSearchQuery,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Document::class);

        $filters = $this->documentSearchQuery->filtersFromRequest($request);
        $data = $this->rekapitulasiService->dashboard($filters);

        return view('admin.rekapitulasi.index', [
            'kpi' => $data['kpi'],
            'byArea' => $data['byArea'],
            'bySubtype' => $data['bySubtype'],
            'byYear' => $data['byYear'],
            'documents' => $data['documents'],
            'filters' => $filters,
            'areas' => CooperationTaxonomy::areas(),
            'subtypes' => CooperationTaxonomy::subtypes(),
            'years' => $this->rekapitulasiService->availableYears(),
            'categories' => CooperationCategory::query()->with('section')->orderBy('name')->get(),
        ]);
    }
}
