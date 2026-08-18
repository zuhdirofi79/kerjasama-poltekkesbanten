<?php

namespace App\Services;

use App\Models\Document;
use App\Support\CooperationTaxonomy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RekapitulasiService
{
    public function __construct(
        private readonly DocumentSearchQuery $documentSearchQuery,
        private readonly CooperationExpiryService $expiryService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function dashboard(array $filters): array
    {
        $scoped = $this->scopedQuery($filters);

        $kpi = [
            'total' => (clone $scoped)->whereIn('documents.cooperation_area', CooperationTaxonomy::triDharmaAreas())->count(),
            'education' => (clone $scoped)->where('documents.cooperation_area', CooperationTaxonomy::EDUCATION)->count(),
            'research' => (clone $scoped)->where('documents.cooperation_area', CooperationTaxonomy::RESEARCH)->count(),
            'community_service' => (clone $scoped)->where('documents.cooperation_area', CooperationTaxonomy::COMMUNITY_SERVICE)->count(),
        ];

        $byArea = (clone $scoped)
            ->whereNotNull('documents.cooperation_area')
            ->selectRaw('documents.cooperation_area as area, count(*) as total')
            ->groupBy('documents.cooperation_area')
            ->pluck('total', 'area');

        $bySubtype = (clone $scoped)
            ->whereNotNull('documents.cooperation_subtype')
            ->selectRaw('documents.cooperation_area as area, documents.cooperation_subtype as subtype, count(*) as total')
            ->groupBy('documents.cooperation_area', 'documents.cooperation_subtype')
            ->get();

        $byYear = (clone $scoped)
            ->join('year_folders', 'year_folders.id', '=', 'documents.year_folder_id')
            ->selectRaw('year_folders.year as year, count(*) as total')
            ->groupBy('year_folders.year')
            ->orderByDesc('year_folders.year')
            ->pluck('total', 'year');

        $documents = $this->documentSearchQuery
            ->apply((clone $scoped)->with([
                'yearFolder.category.section',
                'documentFolder',
                'institutionFolder',
            ]), $filters)
            ->paginate(25)
            ->withQueryString();

        return compact('kpi', 'byArea', 'bySubtype', 'byYear', 'documents');
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function scopedQuery(array $filters): Builder
    {
        $query = Document::query();

        $year = (string) ($filters['year'] ?? '');
        if ($year !== '' && ctype_digit($year)) {
            $query->whereHas('yearFolder', fn (Builder $q) => $q->where('year', (int) $year));
        }

        $section = (string) ($filters['section'] ?? '');
        if (in_array($section, ['domestic', 'international'], true)) {
            $query->whereHas('yearFolder.category.section', fn (Builder $q) => $q->where('type', $section));
        }

        $categoryId = (string) ($filters['category_id'] ?? '');
        if ($categoryId !== '' && ctype_digit($categoryId)) {
            $query->whereHas('yearFolder', fn (Builder $q) => $q->where('category_id', (int) $categoryId));
        }

        $expiry = (string) ($filters['expiry_status'] ?? '');
        if ($expiry !== '') {
            $this->expiryService->applyStatusFilter($query, $expiry);
        }

        return $query;
    }

    /**
     * @return Collection<int, int>
     */
    public function availableYears(): Collection
    {
        return Document::query()
            ->join('year_folders', 'year_folders.id', '=', 'documents.year_folder_id')
            ->selectRaw('year_folders.year as year')
            ->groupBy('year_folders.year')
            ->orderByDesc('year_folders.year')
            ->pluck('year');
    }
}
