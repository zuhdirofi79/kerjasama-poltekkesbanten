<?php

namespace App\Services;

use App\Support\CooperationTaxonomy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DocumentSearchQuery
{
    public function __construct(
        private readonly DocumentSearchAliasNormalizer $aliasNormalizer,
    ) {}

    /**
     * @return array{
     *     q: string,
     *     area: string,
     *     subtype: string,
     *     date_from: string,
     *     date_to: string,
     *     sort: string,
     *     year: string,
     *     section: string,
     *     category_id: string,
     *     expiry_status: string
     * }
     */
    public function filtersFromRequest(Request $request): array
    {
        $sorts = array_keys(CooperationTaxonomy::sorts());
        $areas = array_keys(CooperationTaxonomy::areas());
        $sort = $request->string('sort')->toString();
        if (! in_array($sort, $sorts, true)) {
            $sort = 'document_date_desc';
        }

        $area = $request->string('area')->toString();
        if ($area !== '' && ! in_array($area, $areas, true)) {
            $area = '';
        }

        $subtype = $request->string('subtype')->toString();
        if ($subtype !== '' && $area !== '' && ! CooperationTaxonomy::isValidSubtype($area, $subtype)) {
            $subtype = '';
        }

        $expiry = $request->string('expiry_status')->toString();
        if (! in_array($expiry, ['active', 'expiring_soon', 'expired', 'unspecified'], true)) {
            $expiry = '';
        }

        return [
            'q' => mb_substr($request->string('q')->trim()->toString(), 0, 200),
            'area' => $area,
            'subtype' => $subtype,
            'date_from' => $this->validDate($request->string('date_from')->toString()),
            'date_to' => $this->validDate($request->string('date_to')->toString()),
            'sort' => $sort,
            'year' => $request->string('year')->toString(),
            'section' => $request->string('section')->toString(),
            'category_id' => $request->string('category_id')->toString(),
            'expiry_status' => $expiry,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function hasActiveFilters(array $filters): bool
    {
        foreach (['q', 'area', 'subtype', 'date_from', 'date_to'] as $key) {
            if (filled($filters[$key] ?? null)) {
                return true;
            }
        }

        $sort = (string) ($filters['sort'] ?? 'document_date_desc');

        return $sort !== 'document_date_desc';
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function apply(Builder $query, array $filters): Builder
    {
        $resolved = $this->aliasNormalizer->resolve((string) ($filters['q'] ?? ''));
        $area = (string) ($filters['area'] ?? '');
        if ($area === '' && $resolved['area']) {
            $area = $resolved['area'];
        }

        $subtype = (string) ($filters['subtype'] ?? '');
        $dateFrom = (string) ($filters['date_from'] ?? '');
        $dateTo = (string) ($filters['date_to'] ?? '');
        $text = $resolved['text'];

        if ($area !== '') {
            $query->where('documents.cooperation_area', $area);
        }

        if ($subtype !== '') {
            $query->where('documents.cooperation_subtype', $subtype);
        }

        if ($dateFrom !== '') {
            $query->whereDate('documents.document_date', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('documents.document_date', '<=', $dateTo);
        }

        if ($text !== '') {
            $like = '%'.$this->escapeLike($text).'%';
            $query->where(function (Builder $inner) use ($like) {
                $inner->where('documents.title', 'like', $like)
                    ->orWhere('documents.description', 'like', $like)
                    ->orWhere('documents.original_filename', 'like', $like)
                    ->orWhereHas('documentFolder', fn (Builder $folder) => $folder->where('name', 'like', $like))
                    ->orWhereHas('institutionFolder', fn (Builder $folder) => $folder->where('name', 'like', $like));
            });
        }

        return $this->applySort($query, (string) ($filters['sort'] ?? 'document_date_desc'));
    }

    public function applySort(Builder $query, string $sort): Builder
    {
        return match ($sort) {
            'document_date_asc' => $query
                ->orderByRaw('documents.document_date is null')
                ->orderBy('documents.document_date')
                ->orderBy('documents.id'),
            'uploaded_desc' => $query->orderByDesc('documents.created_at')->orderByDesc('documents.id'),
            'uploaded_asc' => $query->orderBy('documents.created_at')->orderBy('documents.id'),
            'title_asc' => $query->orderBy('documents.title')->orderBy('documents.id'),
            'title_desc' => $query->orderByDesc('documents.title')->orderBy('documents.id'),
            default => $query
                ->orderByRaw('documents.document_date is null')
                ->orderByDesc('documents.document_date')
                ->orderByDesc('documents.id'),
        };
    }

    private function validDate(string $value): string
    {
        if ($value === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return '';
        }

        return $value;
    }

    private function escapeLike(string $value): string
    {
        return $value;
    }
}
