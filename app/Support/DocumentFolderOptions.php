<?php

namespace App\Support;

use App\Models\DocumentFolder;
use App\Models\YearFolder;
use Illuminate\Support\Collection;

class DocumentFolderOptions
{
    /**
     * Flat options for a single year, with indentation for nested folders.
     *
     * @return list<array{id: int, label: string, depth: int, parent_id: int|null}>
     */
    public static function forYear(YearFolder|int $yearFolder, bool $activeOnly = true): array
    {
        $yearFolderId = $yearFolder instanceof YearFolder ? $yearFolder->id : $yearFolder;

        $folders = DocumentFolder::query()
            ->where('year_folder_id', $yearFolderId)
            ->when($activeOnly, fn ($q) => $q->active())
            ->ordered()
            ->get(['id', 'parent_id', 'name', 'sort_order']);

        return self::flattenTree($folders);
    }

    /**
     * @param  Collection<int, DocumentFolder>  $folders
     * @return list<array{id: int, label: string, depth: int, parent_id: int|null}>
     */
    public static function flattenTree(Collection $folders): array
    {
        $byParent = $folders->groupBy(fn (DocumentFolder $folder) => $folder->parent_id ?? 0);
        $options = [];
        $visited = [];

        $walk = function (?int $parentId, int $depth) use (&$walk, &$options, &$visited, $byParent): void {
            if ($depth > 20) {
                return;
            }

            $key = $parentId ?? 0;
            $siblings = $byParent->get($key, collect())->values();
            $lastIndex = $siblings->count() - 1;

            foreach ($siblings as $index => $folder) {
                if (isset($visited[$folder->id])) {
                    continue;
                }
                $visited[$folder->id] = true;

                $prefix = '';
                if ($depth > 0) {
                    $branch = $index === $lastIndex ? '└─ ' : '├─ ';
                    $prefix = str_repeat('    ', $depth).$branch;
                }

                $options[] = [
                    'id' => $folder->id,
                    'label' => $prefix.$folder->name,
                    'depth' => $depth,
                    'parent_id' => $folder->parent_id,
                ];

                $walk($folder->id, $depth + 1);
            }
        };

        $walk(null, 0);

        // Orphans (parent missing / outside set) appear at root to avoid silent loss.
        foreach ($folders as $folder) {
            if (! isset($visited[$folder->id])) {
                $visited[$folder->id] = true;
                $options[] = [
                    'id' => $folder->id,
                    'label' => $folder->name,
                    'depth' => 0,
                    'parent_id' => $folder->parent_id,
                ];
            }
        }

        return $options;
    }
}
