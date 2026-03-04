<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobileTrackingTrip extends Model
{
    protected $fillable = [
        'vehicle_id',
        'driver_phone',
        'start_odometer',
        'end_odometer',
        'trip_distance_km',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'start_odometer' => 'decimal:2',
        'end_odometer' => 'decimal:2',
        'trip_distance_km' => 'decimal:2',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
