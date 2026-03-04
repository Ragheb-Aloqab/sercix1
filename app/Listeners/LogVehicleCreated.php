<?php

namespace App\Listeners;

use App\Events\VehicleCreated;
use App\Services\ActivityLogger;

class LogVehicleCreated
{
    public function __construct(
        private readonly ActivityLogger $logger
    ) {}

    public function handle(VehicleCreated $event): void
    {
        $v = $event->vehicle;
        $this->logger->log('vehicle_created', [
            'vehicle_id' => $v->id,
            'company_id' => $v->company_id,
            'plate_number' => $v->plate_number,
        ]);
    }
}
