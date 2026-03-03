<?php

namespace App\Console\Commands;

use App\Models\VehicleMonthlyMileage;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillMonthlyMileageCommand extends Command
{
    protected $signature = 'mileage:backfill
                            {--months=6 : Number of past months to backfill}
                            {--company= : Only backfill for specific company ID}';

    protected $description = 'Backfill vehicle_monthly_mileage from fuel_refills and vehicle_locations (run once for existing data)';

    public function handle(): int
    {
        $months = (int) $this->option('months');
        $companyId = $this->option('company');

        $vehicleQuery = DB::table('vehicles');
        if ($companyId) {
            $vehicleQuery->where('company_id', $companyId);
        }
        $vehicleIds = $vehicleQuery->pluck('id')->toArray();

        if (empty($vehicleIds)) {
            $this->warn('No vehicles found.');
            return self::SUCCESS;
        }

        $created = 0;
        for ($i = $months; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $month = (int) $date->month;
            $year = (int) $date->year;
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();
            $isCurrentMonth = $date->isCurrentMonth();

            foreach ($vehicleIds as $vehicleId) {
                $existing = VehicleMonthlyMileage::where('vehicle_id', $vehicleId)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->exists();

                if ($existing) {
                    continue;
                }

                // Get MIN/MAX odometer from vehicle_locations in month
                $locRange = DB::table('vehicle_locations')
                    ->where('vehicle_id', $vehicleId)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->whereNotNull('odometer')
                    ->where('odometer', '>', 0)
                    ->selectRaw('MIN(odometer) as min_odo, MAX(odometer) as max_odo')
                    ->first();

                if ($locRange && $locRange->min_odo !== null) {
                    $startOdo = (float) $locRange->min_odo;
                    $endOdo = (float) $locRange->max_odo;
                    $totalKm = max(0, $endOdo - $startOdo);
                } else {
                    // Fallback: fuel_refills
                    $fuelRange = DB::table('fuel_refills')
                        ->where('vehicle_id', $vehicleId)
                        ->whereBetween('refilled_at', [$monthStart, $monthEnd])
                        ->whereNotNull('odometer_km')
                        ->where('odometer_km', '>', 0)
                        ->selectRaw('MIN(odometer_km) as min_odo, MAX(odometer_km) as max_odo')
                        ->first();

                    if (!$fuelRange || $fuelRange->min_odo === null) {
                        continue;
                    }
                    $startOdo = (float) $fuelRange->min_odo;
                    $endOdo = (float) $fuelRange->max_odo;
                    $totalKm = max(0, $endOdo - $startOdo);
                }

                VehicleMonthlyMileage::create([
                    'vehicle_id' => $vehicleId,
                    'month' => $month,
                    'year' => $year,
                    'start_odometer' => $startOdo,
                    'end_odometer' => $endOdo,
                    'total_km' => $totalKm,
                    'is_closed' => !$isCurrentMonth,
                ]);
                $created++;
            }
        }

        $this->info("Backfilled {$created} monthly mileage record(s).");
        return self::SUCCESS;
    }
}
