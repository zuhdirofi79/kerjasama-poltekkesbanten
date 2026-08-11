<?php

namespace App\Services;

use App\Models\DocumentFolder;
use App\Models\YearFolder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DefaultYearFolderProvisioner
{
    /**
     * Idempotently create the default root folders for a year.
     *
     * @return array{created: int, skipped: int}
     */
    public function provision(YearFolder $yearFolder, ?int $actorId = null): array
    {
        $names = config('cooperation.default_year_folders', []);
        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($yearFolder, $names, $actorId, &$created, &$skipped): void {
            foreach (array_values($names) as $index => $name) {
                $name = trim((string) $name);
                if ($name === '') {
                    continue;
                }

                $slug = $this->baseSlug($name);
                $existing = DocumentFolder::query()
                    ->where('year_folder_id', $yearFolder->id)
                    ->whereNull('parent_id')
                    ->where(function ($q) use ($name, $slug) {
                        $q->where('name', $name)->orWhere('slug', $slug);
                    })
                    ->first();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                DocumentFolder::query()->create([
                    'year_folder_id' => $yearFolder->id,
                    'parent_id' => null,
                    'name' => $name,
                    'slug' => $this->uniqueSlug($yearFolder->id, null, $slug),
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'created_by' => $actorId,
                    'updated_by' => $actorId,
                ]);
                $created++;
            }
        });

        return compact('created', 'skipped');
    }

    private function baseSlug(string $name): string
    {
        $slug = Str::slug($name);

        return $slug !== '' ? $slug : 'folder';
    }

    private function uniqueSlug(int $yearFolderId, ?int $parentId, string $base): string
    {
        $candidate = $base;
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
}
