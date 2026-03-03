<?php

namespace App\Console\Commands;

use App\Services\MonthlyMileageSnapshotService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CaptureMonthlyMileageSnapshotCommand extends Command
{
    protected $signature = 'mileage:capture-monthly
                            {--date= : Run for specific date (Y-m-d), default: today}
                            {--month-start-only : Only run month-start snapshot, skip current month update}';

    protected $description = 'Capture monthly mileage snapshots: close previous month, create new month, update current from GPS';

    public function handle(MonthlyMileageSnapshotService $service): int
    {
        $forDate = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now();

        $isFirstOfMonth = $forDate->day === 1;

        if ($isFirstOfMonth) {
            $this->info('Running month-start snapshot...');
            $result = $service->captureMonthStartSnapshot($forDate);
            $this->line("  → Closed {$result['closed']} previous month(s), created {$result['created']} new month record(s)");
        }

        if (!$this->option('month-start-only')) {
            $this->info('Updating current month from latest GPS readings...');
            $result = $service->updateCurrentMonthFromLatest();
            $this->line("  → Updated {$result['updated']} vehicle(s)");
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
