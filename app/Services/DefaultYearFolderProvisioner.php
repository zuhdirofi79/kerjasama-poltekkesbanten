<?php

namespace App\Services;

use App\Models\YearFolder;

class DefaultYearFolderProvisioner
{
    public function __construct(
        private readonly FolderHierarchyRepairService $folderHierarchyRepairService,
    ) {}

    /**
     * Idempotently create the canonical nested folders for a year.
     *
     * @return array{created: int, skipped: int}
     */
    public function provision(YearFolder $yearFolder, ?int $actorId = null): array
    {
        $stats = $this->folderHierarchyRepairService->repairYear($yearFolder, $actorId);

        return [
            'created' => $stats['created'],
            'skipped' => $stats['skipped'],
        ];
    }
}
