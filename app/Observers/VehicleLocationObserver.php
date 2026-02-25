<?php

namespace App\Observers;

use App\Events\VehicleLocationUpdated;
use App\Models\VehicleLocation;

class VehicleLocationObserver
{
    public function created(VehicleLocation $location): void
    {
        $vehicle = $location->vehicle;
        if ($vehicle) {
            event(new VehicleLocationUpdated($vehicle, $location));
        }
    }
}
