<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\YearFolder;
use App\Support\CooperationFolderTaxonomy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FolderHierarchyRepairService
{
    /**
     * @return array{
     *     years: int,
     *     created: int,
     *     skipped: int,
     *     moved: int,
     *     merged: int,
     *     documents_preserved: int,
     *     details: list<array<string, mixed>>
     * }
     */
    public function repairAll(?int $actorId = null): array
    {
        $totals = [
            'years' => 0,
            'created' => 0,
            'skipped' => 0,
            'moved' => 0,
            'merged' => 0,
            'documents_preserved' => 0,
            'details' => [],
        ];

        $years = YearFolder::query()->withTrashed()->with('category.section')->orderBy('id')->get();

        foreach ($years as $year) {
            $stats = $this->repairYear($year, $actorId);
            $totals['years']++;
            $totals['created'] += $stats['created'];
            $totals['skipped'] += $stats['skipped'];
            $totals['moved'] += $stats['moved'];
            $totals['merged'] += $stats['merged'];
            $totals['documents_preserved'] += $stats['documents_preserved'];
            $totals['details'][] = $stats;
        }

        return $totals;
    }

    /**
     * @return array{
     *     year_folder_id: int,
     *     label: string,
     *     created: int,
     *     skipped: int,
     *     moved: int,
     *     merged: int,
     *     documents_preserved: int,
     *     roots: list<array{name: string, children: list<string>}>
     * }
     */
    public function repairYear(YearFolder $yearFolder, ?int $actorId = null): array
    {
        $documentsBefore = Document::withTrashed()
            ->where('year_folder_id', $yearFolder->id)
            ->count();

        $stats = [
            'year_folder_id' => $yearFolder->id,
            'label' => $this->yearLabel($yearFolder),
            'created' => 0,
            'skipped' => 0,
            'moved' => 0,
            'merged' => 0,
            'documents_preserved' => 0,
            'roots' => [],
        ];

        DB::transaction(function () use ($yearFolder, $actorId, &$stats, $documentsBefore): void {
            $roots = [];

            foreach (CooperationFolderTaxonomy::tree() as $spec) {
                $root = $this->ensureFolder($yearFolder, $spec, null, $actorId, $stats);
                $childNames = [];

                foreach ($spec['children'] as $childSpec) {
                    $child = $this->ensureFolder($yearFolder, $childSpec, $root, $actorId, $stats, excludeRootAliases: true);
                    $childNames[] = $child->name;
                }

                $roots[] = [
                    'name' => $root->name,
                    'children' => $childNames,
                ];
            }

            $this->dedupeSiblings($yearFolder, $stats);

            $documentsAfter = Document::withTrashed()
                ->where('year_folder_id', $yearFolder->id)
                ->count();

            $stats['documents_preserved'] = $documentsAfter;
            $stats['roots'] = $roots;

            if ($documentsAfter !== $documentsBefore) {
                throw new \RuntimeException(
                    "Document count changed for YearFolder #{$yearFolder->id}: {$documentsBefore} → {$documentsAfter}."
                );
            }
        });

        return $stats;
    }

    /**
     * @param  array{name: string, slug: string, sort_order: int, aliases: list<string>}  $spec
     */
    private function ensureFolder(
        YearFolder $yearFolder,
        array $spec,
        ?DocumentFolder $parent,
        ?int $actorId,
        array &$stats,
        bool $excludeRootAliases = false,
    ): DocumentFolder {
        $desiredParentId = $parent?->id;
        $matches = $this->findMatches($yearFolder->id, $spec['aliases'], $spec['slug'], $excludeRootAliases);

        if ($matches->isEmpty()) {
            $stats['created']++;

            return DocumentFolder::query()->create([
                'year_folder_id' => $yearFolder->id,
                'parent_id' => $desiredParentId,
                'name' => $spec['name'],
                'slug' => $this->uniqueSlug($yearFolder->id, $desiredParentId, $spec['slug']),
                'sort_order' => $spec['sort_order'],
                'is_active' => true,
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);
        }

        $survivor = $this->pickSurvivor($matches, $spec['name'], $desiredParentId);

        foreach ($matches as $other) {
            if ($other->id === $survivor->id) {
                continue;
            }
            $this->mergeInto($survivor, $other, $stats);
            $survivor->refresh();
        }

        $this->normalizeSurvivor($survivor, $spec, $desiredParentId, $actorId, $stats);

        return $survivor->fresh();
    }

    /**
     * @param  list<string>  $aliases
     * @return Collection<int, DocumentFolder>
     */
    private function findMatches(int $yearFolderId, array $aliases, string $slug, bool $excludeRootAliases): Collection
    {
        $normalizedAliases = array_map(
            fn (string $alias) => CooperationFolderTaxonomy::normalize($alias),
            $aliases,
        );
        $rootAliasNorm = CooperationFolderTaxonomy::allRootNormalizedAliases();
        $rootSlugs = CooperationFolderTaxonomy::rootSlugs();

        return DocumentFolder::withTrashed()
            ->where('year_folder_id', $yearFolderId)
            ->get()
            ->filter(function (DocumentFolder $folder) use ($normalizedAliases, $slug, $excludeRootAliases, $rootAliasNorm, $rootSlugs) {
                $normalized = CooperationFolderTaxonomy::normalize($folder->name);
                $matches = $folder->slug === $slug || in_array($normalized, $normalizedAliases, true);

                if (! $matches) {
                    return false;
                }

                if ($excludeRootAliases) {
                    if (in_array($folder->slug, $rootSlugs, true) || in_array($normalized, $rootAliasNorm, true)) {
                        return false;
                    }
                }

                return true;
            })
            ->values();
    }

    /**
     * @param  Collection<int, DocumentFolder>  $matches
     */
    private function pickSurvivor(Collection $matches, string $canonicalName, ?int $desiredParentId): DocumentFolder
    {
        $canonical = CooperationFolderTaxonomy::normalize($canonicalName);

        return $matches
            ->sortBy(function (DocumentFolder $folder) use ($canonical, $desiredParentId) {
                $trash = $folder->trashed() ? 1 : 0;
                $inactive = $folder->is_active ? 0 : 1;
                $name = CooperationFolderTaxonomy::normalize($folder->name) === $canonical ? 0 : 1;
                $parent = $folder->parent_id === $desiredParentId ? 0 : 1;

                return sprintf('%d%d%d%d%010d', $trash, $inactive, $name, $parent, $folder->id);
            })
            ->first();
    }

    /**
     * @param  array{name: string, slug: string, sort_order: int}  $spec
     */
    private function normalizeSurvivor(
        DocumentFolder $survivor,
        array $spec,
        ?int $desiredParentId,
        ?int $actorId,
        array &$stats,
    ): void {
        if ($survivor->trashed()) {
            $survivor->restore();
        }

        $changedParent = $survivor->parent_id !== $desiredParentId;
        $alreadyCorrect = ! $changedParent
            && $survivor->name === $spec['name']
            && $survivor->sort_order === $spec['sort_order']
            && $survivor->is_active;

        foreach (
            DocumentFolder::withTrashed()
                ->where('year_folder_id', $survivor->year_folder_id)
                ->where('id', '!=', $survivor->id)
                ->where('slug', $spec['slug'])
                ->get() as $conflict
        ) {
            $conflict->slug = $conflict->slug.'-legacy-'.$conflict->id;
            $conflict->save();
        }

        $survivor->fill([
            'parent_id' => $desiredParentId,
            'name' => $spec['name'],
            'slug' => $spec['slug'],
            'sort_order' => $spec['sort_order'],
            'is_active' => true,
            'updated_by' => $actorId,
        ]);
        $survivor->save();

        if ($changedParent) {
            $stats['moved']++;
        } elseif ($alreadyCorrect) {
            $stats['skipped']++;
        } else {
            $stats['skipped']++;
        }
    }

    private function mergeInto(DocumentFolder $survivor, DocumentFolder $duplicate, array &$stats): void
    {
        if ($survivor->id === $duplicate->id) {
            return;
        }

        if ($duplicate->legacy_institution_folder_id) {
            if (! $survivor->legacy_institution_folder_id) {
                $survivor->legacy_institution_folder_id = $duplicate->legacy_institution_folder_id;
                $survivor->save();
            }
            $duplicate->legacy_institution_folder_id = null;
            $duplicate->save();
        }

        Document::withTrashed()
            ->where('document_folder_id', $duplicate->id)
            ->update(['document_folder_id' => $survivor->id]);

        $children = DocumentFolder::withTrashed()
            ->where('parent_id', $duplicate->id)
            ->get();

        foreach ($children as $child) {
            $conflict = $this->siblingByNormalizedName(
                $survivor->year_folder_id,
                $survivor->id,
                $child->name,
                $child->id,
            );

            if ($conflict) {
                $this->mergeInto($conflict, $child, $stats);
            } else {
                $child->parent_id = $survivor->id;
                $child->save();
                $stats['moved']++;
            }
        }

        if (
            Document::withTrashed()->where('document_folder_id', $duplicate->id)->exists()
            || DocumentFolder::withTrashed()->where('parent_id', $duplicate->id)->exists()
        ) {
            return;
        }

        $duplicate->is_active = false;
        $duplicate->save();

        if (! $duplicate->trashed()) {
            $duplicate->delete();
        }

        $stats['merged']++;
    }

    private function dedupeSiblings(YearFolder $yearFolder, array &$stats): void
    {
        $folders = DocumentFolder::query()
            ->where('year_folder_id', $yearFolder->id)
            ->get()
            ->groupBy(fn (DocumentFolder $folder) => ($folder->parent_id ?? 'root').'|'.CooperationFolderTaxonomy::normalize($folder->name));

        foreach ($folders as $group) {
            if ($group->count() < 2) {
                continue;
            }

            $ordered = $group->sortBy('id')->values();
            $survivor = $ordered->first();

            foreach ($ordered->skip(1) as $duplicate) {
                $this->mergeInto($survivor, $duplicate, $stats);
            }
        }
    }

    private function siblingByNormalizedName(int $yearFolderId, ?int $parentId, string $name, int $ignoreId): ?DocumentFolder
    {
        $normalized = CooperationFolderTaxonomy::normalize($name);

        return DocumentFolder::withTrashed()
            ->where('year_folder_id', $yearFolderId)
            ->where('id', '!=', $ignoreId)
            ->where(function ($q) use ($parentId) {
                if ($parentId === null) {
                    $q->whereNull('parent_id');
                } else {
                    $q->where('parent_id', $parentId);
                }
            })
            ->get()
            ->first(fn (DocumentFolder $folder) => CooperationFolderTaxonomy::normalize($folder->name) === $normalized);
    }

    private function uniqueSlug(int $yearFolderId, ?int $parentId, string $base): string
    {
        $candidate = $base !== '' ? $base : 'folder';
        $i = 2;

        while (
            DocumentFolder::withTrashed()
                ->where('year_folder_id', $yearFolderId)
                ->where(function ($q) use ($parentId) {
                    if ($parentId === null) {
                        $q->whereNull('parent_id');
                    } else {
                        $q->where('parent_id', $parentId);
                    }
                })
                ->where('slug', $candidate)
                ->exists()
        ) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    private function yearLabel(YearFolder $yearFolder): string
    {
        $section = $yearFolder->category?->section?->name;
        $category = $yearFolder->category?->name;
        $year = $yearFolder->year;

        $parts = array_filter([$section, $category, $year]);

        return 'YearFolder #'.$yearFolder->id.($parts !== [] ? ' / '.implode(' / ', $parts) : '');
    }
}
