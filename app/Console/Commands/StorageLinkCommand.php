<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Hostinger-safe storage:link — works when exec() and symlink() are disabled.
 * If the link already exists (created manually via SSH), reports success.
 */
#[AsCommand(name: 'storage:link')]
class StorageLinkCommand extends Command
{
    protected $signature = 'storage:link
                {--relative : Create the symbolic link using relative paths}
                {--force : Recreate existing symbolic links}';

    protected $description = 'Create the symbolic links configured for the application';

    public function handle(): int
    {
        $links = config('filesystems.links') ?? [
            public_path('storage') => storage_path('app/public'),
        ];

        foreach ($links as $link => $target) {
            if (file_exists($link) && is_link($link)) {
                $this->components->info("The [{$link}] link already exists.");
                continue;
            }
            if (file_exists($link) && ! is_link($link)) {
                if (! $this->option('force')) {
                    $this->components->error("The [{$link}] link already exists.");
                    continue;
                }
                @unlink($link);
            }


            if (! is_dir($target)) {
                (new Filesystem)->makeDirectory($target, 0755, true);
            }

            if (function_exists('symlink')) {
                if (@symlink($target, $link)) {
                    $this->components->info("The [{$link}] link has been connected to [{$target}].");
                    continue;
                }
            }

            $this->components->warn("Cannot create [{$link}]: symlink() and exec() are disabled on this server.");
            $this->line('');
            $this->line('Create it manually via SSH:');
            $this->line('  cd ' . base_path());
            $this->line('  ln -s ../storage/app/public public/storage');
            $this->line('');
            return self::SUCCESS;
        }

        return self::SUCCESS;
    }
}
