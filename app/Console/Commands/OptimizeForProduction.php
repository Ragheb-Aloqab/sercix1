<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class OptimizeForProduction extends Command
{
    protected $signature = 'optimize:production';

    protected $description = 'Run all optimizations for production (caching, config, routes, views)';

    public function handle(): int
    {
        $this->info('Running production optimizations...');

        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');
        $this->call('event:cache');

        $this->info('Production optimizations complete.');

        return self::SUCCESS;
    }
}
