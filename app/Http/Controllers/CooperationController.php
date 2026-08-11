<?php

namespace App\Http\Controllers;

use App\Models\CooperationCategory;
use App\Models\CooperationSection;
use App\Models\DocumentFolder;
use App\Models\InstitutionFolder;
use App\Models\YearFolder;
use App\Services\DocumentAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CooperationController extends Controller
{
    public function __construct(
        private readonly DocumentAccessService $documentAccessService,
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

    public function year(CooperationSection $section, CooperationCategory $category, string $year): View
    {
        abort_unless($section->is_active && $category->is_active, 404);
        abort_unless($category->section_id === $section->id, 404);

        $yearFolder = $this->resolveYearFolder($category, $year);

        $folders = DocumentFolder::query()
            ->where('year_folder_id', $yearFolder->id)
            ->root()
            ->active()
            ->ordered()
            ->withCount([
                'children as children_count' => fn ($q) => $q->active(),
                'documents as documents_count' => fn ($q) => $q->published(),
            ])
            ->get();

        $documents = $yearFolder->documents()
            ->published()
            ->whereNull('document_folder_id')
            ->orderBy('title')
            ->get();

        $statusLabels = $this->statusLabelsFor($documents);

        return view('cooperation.year', compact(
            'section',
            'category',
            'yearFolder',
            'folders',
            'documents',
            'statusLabels',
        ));
    }

    public function folder(
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

        $children = DocumentFolder::query()
            ->where('parent_id', $folder->id)
            ->active()
            ->ordered()
            ->withCount([
                'children as children_count' => fn ($q) => $q->active(),
                'documents as documents_count' => fn ($q) => $q->published(),
            ])
            ->get();

        $documents = $folder->documents()
            ->published()
            ->orderBy('title')
            ->get();

        $ancestors = $folder->ancestorChain();
        $statusLabels = $this->statusLabelsFor($documents);

        return view('cooperation.folder', compact(
            'section',
            'category',
            'yearFolder',
            'folder',
            'children',
            'documents',
            'ancestors',
            'statusLabels',
        ));
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
