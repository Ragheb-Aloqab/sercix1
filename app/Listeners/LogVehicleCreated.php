<?php

namespace App\Listeners;

use App\Events\VehicleCreated;
use App\Models\Vehicle;
use App\Services\ActivityLogger;

class LogVehicleCreated
{
    public function handle(VehicleCreated $event): void
    {
        $v = $event->vehicle;
        ActivityLogger::log(
            'vehicle_created',
            Vehicle::class,
            $v->id,
            "Vehicle created: {$v->plate_number}",
            null,
            [
                'vehicle_id' => $v->id,
                'company_id' => $v->company_id,
                'plate_number' => $v->plate_number,
            ]
        );
    }
}
