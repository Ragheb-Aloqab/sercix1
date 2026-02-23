<?php

namespace App\Observers;

use App\Models\Vehicle;
use App\Models\VehicleLocation;

class VehicleObserver
{
    /**
     * When IMEI is changed, clear old locations so the map shows the new device's position.
     */
    public function updating(Vehicle $vehicle): void
    {
        if ($vehicle->isDirty('imei')) {
            VehicleLocation::where('vehicle_id', $vehicle->id)->delete();
        }
    }
}
