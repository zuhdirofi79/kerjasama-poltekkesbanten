<?php

namespace App\Console\Commands;

use App\Services\FolderHierarchyRepairService;
use Illuminate\Console\Command;

class RepairFolderHierarchyCommand extends Command
{
    protected $signature = 'cooperation:repair-folder-hierarchy';

    protected $description = 'Idempotently nest legacy year folders under the four canonical roots without losing documents';

    public function handle(FolderHierarchyRepairService $repairService): int
    {
        $this->info('Repairing document folder hierarchy...');

        $totals = $repairService->repairAll();

        foreach ($totals['details'] as $year) {
            $this->line($year['label']);
            foreach ($year['roots'] as $root) {
                $this->line('✓ '.$root['name']);
                foreach ($root['children'] as $child) {
                    $this->line('  ✓ '.$child);
                }
            }
            $this->newLine();
        }

        $this->table(
            ['Metric', 'Value'],
            [
                ['Years processed', $totals['years']],
                ['Created', $totals['created']],
                ['Skipped', $totals['skipped']],
                ['Moved', $totals['moved']],
                ['Merged', $totals['merged']],
                ['Documents preserved', $totals['documents_preserved']],
            ],
        );

        $this->info('Done. Re-running this command is safe (idempotent).');

        return self::SUCCESS;
    }
}
