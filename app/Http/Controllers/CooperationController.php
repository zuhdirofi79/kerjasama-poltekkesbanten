<?php

namespace App\Http\Controllers;

use App\Models\CooperationCategory;
use App\Models\CooperationSection;
use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\InstitutionFolder;
use App\Models\YearFolder;
use App\Services\DocumentAccessService;
use App\Services\DocumentSearchQuery;
use App\Support\CooperationTaxonomy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CooperationController extends Controller
{
    public function __construct(
        private readonly DocumentAccessService $documentAccessService,
        private readonly DocumentSearchQuery $documentSearchQuery,
    ) {}

    public function section(CooperationSection $section): View
    {
        abort_unless($section->is_active, 404);

        $section->load([
            'categories' => fn ($q) => $q->active()->orderBy('sort_order'),
        ]);

        return view('cooperation.section', compact('section'));
    }

    public function category(CooperationSection $section, CooperationCategory $category): View
    {
        abort_unless($section->is_active && $category->is_active, 404);
        abort_unless($category->section_id === $section->id, 404);

        $category->load([
            'yearFolders' => fn ($q) => $q->active()->ordered(),
        ]);

        return view('cooperation.category', compact('section', 'category'));
    }

    public function year(Request $request, CooperationSection $section, CooperationCategory $category, string $year): View
    {
        abort_unless($section->is_active && $category->is_active, 404);
        abort_unless($category->section_id === $section->id, 404);

        $yearFolder = $this->resolveYearFolder($category, $year);
        $filters = $this->documentSearchQuery->filtersFromRequest($request);
        $filtering = $this->documentSearchQuery->hasActiveFilters($filters);

        $folders = DocumentFolder::query()
            ->where('year_folder_id', $yearFolder->id)
            ->canonicalRoots()
            ->active()
            ->ordered()
            ->withCount([
                'children as children_count' => fn ($q) => $q->active(),
                'documents as documents_count' => fn ($q) => $q->published(),
            ])
            ->get();

        $documentsQuery = Document::query()
            ->published()
            ->where('year_folder_id', $yearFolder->id)
            ->with(['documentFolder', 'institutionFolder']);

        if (! $filtering) {
            $documentsQuery->whereNull('document_folder_id');
        }

        $documents = $this->documentSearchQuery
            ->apply($documentsQuery, $filters)
            ->paginate(12)
            ->withQueryString();

        $statusLabels = $this->statusLabelsFor($documents);

        return view('cooperation.year', [
            'section' => $section,
            'category' => $category,
            'yearFolder' => $yearFolder,
            'folders' => $folders,
            'documents' => $documents,
            'statusLabels' => $statusLabels,
            'filters' => $filters,
            'filtering' => $filtering,
            'areas' => CooperationTaxonomy::areas(),
            'subtypes' => CooperationTaxonomy::subtypes(),
            'sorts' => CooperationTaxonomy::sorts(),
        ]);
    }

    public function folder(
        Request $request,
        CooperationSection $section,
        CooperationCategory $category,
        string $year,
        DocumentFolder $folder,
    ): View {
        abort_unless($section->is_active && $category->is_active, 404);
        abort_unless($category->section_id === $section->id, 404);

        $yearFolder = $this->resolveYearFolder($category, $year);

        abort_unless($folder->year_folder_id === $yearFolder->id, 404);
        abort_unless($folder->is_active, 404);

        $folder->load([
            'parent.parent.parent.parent.parent',
        ]);

        $filters = $this->documentSearchQuery->filtersFromRequest($request);
        $filtering = $this->documentSearchQuery->hasActiveFilters($filters);

        $children = DocumentFolder::query()
            ->where('parent_id', $folder->id)
            ->active()
            ->ordered()
            ->withCount([
                'children as children_count' => fn ($q) => $q->active(),
                'documents as documents_count' => fn ($q) => $q->published(),
            ])
            ->get();

        $documentsQuery = Document::query()
            ->published()
            ->where('year_folder_id', $yearFolder->id)
            ->with(['documentFolder', 'institutionFolder']);

        if ($filtering) {
            $documentsQuery->whereIn('document_folder_id', $this->folderTreeIds($folder));
        } else {
            $documentsQuery->where('document_folder_id', $folder->id);
        }

        $documents = $this->documentSearchQuery
            ->apply($documentsQuery, $filters)
            ->paginate(12)
            ->withQueryString();

        $ancestors = $folder->ancestorChain();
        $statusLabels = $this->statusLabelsFor($documents);

        return view('cooperation.folder', [
            'section' => $section,
            'category' => $category,
            'yearFolder' => $yearFolder,
            'folder' => $folder,
            'children' => $children,
            'documents' => $documents,
            'ancestors' => $ancestors,
            'statusLabels' => $statusLabels,
            'filters' => $filters,
            'filtering' => $filtering,
            'areas' => CooperationTaxonomy::areas(),
            'subtypes' => CooperationTaxonomy::subtypes(),
            'sorts' => CooperationTaxonomy::sorts(),
        ]);
    }

    /**
     * Legacy institution URL → redirect to migrated DocumentFolder when available.
     */
    public function institution(
        CooperationSection $section,
        CooperationCategory $category,
        string $year,
        InstitutionFolder $institution,
    ): RedirectResponse|View {
        abort_unless($section->is_active && $category->is_active, 404);
        abort_unless($category->section_id === $section->id, 404);

        $yearFolder = $this->resolveYearFolder($category, $year);

        abort_unless($institution->year_folder_id === $yearFolder->id, 404);
        abort_unless($institution->is_active, 404);

        $mapped = DocumentFolder::query()
            ->where('legacy_institution_folder_id', $institution->id)
            ->active()
            ->first();

        if ($mapped) {
            return redirect()->route('cooperation.folder', [
                $section,
                $category,
                $yearFolder->slug,
                $mapped,
            ]);
        }

        $institution->load([
            'documents' => fn ($q) => $q->published()->orderBy('title'),
        ]);

        $statusLabels = $this->statusLabelsFor($institution->documents);

        return view('cooperation.institution', compact(
            'section',
            'category',
            'yearFolder',
            'institution',
            'statusLabels',
        ));
    }

    private function resolveYearFolder(CooperationCategory $category, string $year): YearFolder
    {
        return YearFolder::query()
            ->where('category_id', $category->id)
            ->where(function ($q) use ($year) {
                $q->where('slug', $year)->orWhere('year', $year);
            })
            ->active()
            ->firstOrFail();
    }

    /**
     * @return list<int>
     */
    private function folderTreeIds(DocumentFolder $folder): array
    {
        $ids = [$folder->id];
        $all = DocumentFolder::query()
            ->where('year_folder_id', $folder->year_folder_id)
            ->active()
            ->get(['id', 'parent_id']);
        $byParent = $all->groupBy('parent_id');
        $queue = [$folder->id];

        while ($queue !== []) {
            $current = array_pop($queue);
            foreach ($byParent->get($current, []) as $child) {
                $ids[] = $child->id;
                $queue[] = $child->id;
            }
        }

        return $ids;
    }

    private function statusLabelsFor($documents): array
    {
        $user = request()->user();
        $labels = [];

        foreach ($documents as $document) {
            $labels[$document->id] = $this->documentAccessService->publicStatusLabel($user, $document);
        }

        return $labels;
    }
}
