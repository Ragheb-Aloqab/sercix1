<?php

namespace App\Observers;

use App\Models\Vehicle;
use App\Models\VehicleLocation;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Cache;

class VehicleObserver
{
    private function invalidateAdminStats(): void
    {
        Cache::put('admin_stats_version', (Cache::get('admin_stats_version', 1) + 1));
    }

    /**
     * When IMEI is changed, clear old locations so the map shows the new device's position.
     */
    public function updating(Vehicle $vehicle): void
    {
        if ($vehicle->isDirty('imei')) {
            VehicleLocation::where('vehicle_id', $vehicle->id)->delete();
        }
    }

    public function created(Vehicle $vehicle): void
    {
        if ($vehicle->company_id) {
            Cache::forget("company_dashboard_{$vehicle->company_id}");
        }
        $this->invalidateAdminStats();
        $desc = ($vehicle->plate_number ?? $vehicle->make . ' ' . $vehicle->model) . ' (Company #' . $vehicle->company_id . ')';
        ActivityLogger::log('vehicle_created', 'vehicle', $vehicle->id, "Vehicle created: {$desc}");
    }

    public function updated(Vehicle $vehicle): void
    {
        if ($vehicle->company_id) {
            Cache::forget("company_dashboard_{$vehicle->company_id}");
        }
        $this->invalidateAdminStats();
        if ($vehicle->wasChanged()) {
            ActivityLogger::log('vehicle_updated', 'vehicle', $vehicle->id, 'Vehicle #' . $vehicle->id . ' updated', $vehicle->getOriginal(), $vehicle->getChanges());
        }
    }

    public function deleted(Vehicle $vehicle): void
    {
        $this->invalidateAdminStats();
        ActivityLogger::log('vehicle_deleted', 'vehicle', $vehicle->id, 'Vehicle #' . $vehicle->id . ' deleted');
    }
}
