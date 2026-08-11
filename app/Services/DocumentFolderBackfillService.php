<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentFolder;
use App\Models\InstitutionFolder;
use App\Models\YearFolder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentFolderBackfillService
{
    public function __construct(
        private readonly DefaultYearFolderProvisioner $defaultYearFolderProvisioner,
    ) {}

    /**
     * @return array{
     *     years_processed: int,
     *     defaults_created: int,
     *     institutions_migrated: int,
     *     documents_linked: int,
     *     dry_run: bool
     * }
     */
    public function run(bool $dryRun = false): array
    {
        $stats = [
            'years_processed' => 0,
            'defaults_created' => 0,
            'institutions_migrated' => 0,
            'documents_linked' => 0,
            'dry_run' => $dryRun,
        ];

        $years = YearFolder::query()->withTrashed()->orderBy('id')->get();

        foreach ($years as $year) {
            $stats['years_processed']++;

            if ($dryRun) {
                $existingRoots = DocumentFolder::query()
                    ->where('year_folder_id', $year->id)
                    ->whereNull('parent_id')
                    ->count();
                $expected = count(config('cooperation.default_year_folders', []));
                $stats['defaults_created'] += max(0, $expected - $existingRoots);
            } else {
                $result = $this->defaultYearFolderProvisioner->provision($year);
                $stats['defaults_created'] += $result['created'];
            }

            $institutions = InstitutionFolder::query()
                ->withTrashed()
                ->where('year_folder_id', $year->id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            foreach ($institutions as $institution) {
                $already = DocumentFolder::withTrashed()
                    ->where('legacy_institution_folder_id', $institution->id)
                    ->exists();

                if ($already) {
                    continue;
                }

                if ($dryRun) {
                    $stats['institutions_migrated']++;
                    continue;
                }

                DB::transaction(function () use ($year, $institution, &$stats): void {
                    $folder = DocumentFolder::query()->create([
                        'year_folder_id' => $year->id,
                        'parent_id' => null,
                        'name' => $institution->name,
                        'slug' => $this->uniqueSlug($year->id, null, Str::slug($institution->name) ?: 'institusi'),
                        'description' => $institution->description,
                        'sort_order' => 1000 + (int) $institution->sort_order,
                        'is_active' => (bool) $institution->is_active,
                        'created_by' => $institution->created_by,
                        'updated_by' => $institution->updated_by,
                        'legacy_institution_folder_id' => $institution->id,
                    ]);

                    if ($institution->trashed()) {
                        $folder->delete();
                    }

                    $linked = Document::withTrashed()
                        ->where('institution_folder_id', $institution->id)
                        ->whereNull('document_folder_id')
                        ->update(['document_folder_id' => $folder->id]);

                    $stats['institutions_migrated']++;
                    $stats['documents_linked'] += $linked;
                });
            }
        }

        if (! $dryRun) {
            $mappings = DocumentFolder::withTrashed()
                ->whereNotNull('legacy_institution_folder_id')
                ->pluck('id', 'legacy_institution_folder_id');

            foreach ($mappings as $institutionId => $folderId) {
                $stats['documents_linked'] += Document::withTrashed()
                    ->where('institution_folder_id', $institutionId)
                    ->whereNull('document_folder_id')
                    ->update(['document_folder_id' => $folderId]);
            }
        }

        return $stats;
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
