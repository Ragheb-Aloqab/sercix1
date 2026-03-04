<?php

namespace App\Console\Commands;

use App\Services\DailyOdometerSnapshotService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class StoreDailyOdometerCommand extends Command
{
    protected $signature = 'odometer:store-daily
                            {--date= : Store for specific date (Y-m-d), default: today}';

    protected $description = 'Store the last odometer value for each vehicle (daily snapshot)';

    public function handle(DailyOdometerSnapshotService $service): int
    {
        $forDate = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now();

        $this->info("Storing daily odometer snapshots for {$forDate->toDateString()}...");

        $result = $service->storeDailySnapshots($forDate);

        $this->line("  → Stored: {$result['stored']}, Updated: {$result['updated']}");
        $this->info('Done.');

        return self::SUCCESS;
    }
}
