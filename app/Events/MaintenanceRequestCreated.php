<?php

namespace App\Events;

use App\Models\MaintenanceRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MaintenanceRequestCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly MaintenanceRequest $maintenanceRequest) {}
}
