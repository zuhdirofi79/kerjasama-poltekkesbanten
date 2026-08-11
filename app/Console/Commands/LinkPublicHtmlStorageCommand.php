<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Create public_html/storage → storage/app/public for Hostinger-style deploys.
 * Default `php artisan storage:link` only links laravel/public/storage.
 */
class LinkPublicHtmlStorageCommand extends Command
{
    protected $signature = 'cooperation:link-public-storage
                            {--path= : Absolute path to public_html/storage link location}
                            {--force : Replace existing non-symlink directory after backup}';

    protected $description = 'Link public web root /storage to laravel storage/app/public (Hostinger-safe)';

    public function handle(): int
    {
        $link = $this->option('path')
            ?: env('PUBLIC_HTML_STORAGE_PATH')
            ?: base_path('../public_html/storage');

        $link = rtrim(str_replace('\\', '/', $link), '/');
        $target = storage_path('app/public');

        $this->line('Link:   '.$link);
        $this->line('Target: '.$target);

        if (! is_dir($target)) {
            File::ensureDirectoryExists($target);
        }

        if (is_link($link)) {
            $current = readlink($link);
            $this->info('Already a symlink → '.$current);

            return self::SUCCESS;
        }

        if (file_exists($link)) {
            if (! $this->option('force')) {
                $this->error('Path exists and is not a symlink. Re-run with --force to backup and replace.');
                $this->line('Inspect first: ls -lah '.dirname($link).'/storage');

                return self::FAILURE;
            }

            $backup = $link.'-backup-'.date('Ymd-His');
            rename($link, $backup);
            $this->warn('Backed up existing path to '.$backup);
        }

        $parent = dirname($link);
        if (! is_dir($parent)) {
            $this->error('Parent directory missing: '.$parent);

            return self::FAILURE;
        }

        if (! symlink($target, $link)) {
            $this->error('Failed to create symlink. Host may block symlinks — use /previews/{document} route instead.');

            return self::FAILURE;
        }

        $this->info('Symlink created successfully.');

        return self::SUCCESS;
    }
}
