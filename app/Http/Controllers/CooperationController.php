<?php

namespace App\Http\Controllers;

use App\Models\CooperationCategory;
use App\Models\CooperationSection;
use App\Models\InstitutionFolder;
use App\Models\YearFolder;
use App\Services\DocumentAccessService;
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

        $yearFolder = YearFolder::query()
            ->where('category_id', $category->id)
            ->where(function ($q) use ($year) {
                $q->where('slug', $year)->orWhere('year', $year);
            })
            ->active()
            ->firstOrFail();

        $yearFolder->load([
            'institutions' => fn ($q) => $q->active()->ordered(),
            'documents' => fn ($q) => $q->published()
                ->whereNull('institution_folder_id')
                ->orderBy('title'),
        ]);

        $statusLabels = $this->statusLabelsFor($yearFolder->documents);

        return view('cooperation.year', compact('section', 'category', 'yearFolder', 'statusLabels'));
    }

    public function institution(
        CooperationSection $section,
        CooperationCategory $category,
        string $year,
        InstitutionFolder $institution,
    ): View {
        abort_unless($section->is_active && $category->is_active, 404);
        abort_unless($category->section_id === $section->id, 404);

        $yearFolder = YearFolder::query()
            ->where('category_id', $category->id)
            ->where(function ($q) use ($year) {
                $q->where('slug', $year)->orWhere('year', $year);
            })
            ->active()
            ->firstOrFail();

        abort_unless($institution->year_folder_id === $yearFolder->id, 404);
        abort_unless($institution->is_active, 404);

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
