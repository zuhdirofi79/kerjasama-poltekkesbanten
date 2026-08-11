<?php

namespace App\Console\Commands;

use App\Services\DocumentFolderBackfillService;
use Illuminate\Console\Command;

class BackfillDocumentFoldersCommand extends Command
{
    protected $signature = 'cooperation:backfill-document-folders {--dry-run : Report actions without writing}';

    protected $description = 'Idempotently provision default year folders and migrate legacy institution folders';

    public function handle(DocumentFolderBackfillService $backfillService): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info($dryRun ? 'Dry-run: backfill document folders...' : 'Backfilling document folders...');

        $stats = $backfillService->run($dryRun);

        $this->table(
            ['Metric', 'Value'],
            collect($stats)->map(fn ($value, $key) => [$key, is_bool($value) ? ($value ? 'yes' : 'no') : $value])->values()->all()
        );

        $this->info('Done.');

        return self::SUCCESS;
    }
}
