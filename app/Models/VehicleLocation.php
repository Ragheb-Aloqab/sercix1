<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleLocation extends Model
{
    public const SOURCE_DEVICE_API = 'device_api';
    public const SOURCE_MOBILE = 'mobile';

    protected $fillable = [
        'vehicle_id',
        'source',
        'driver_phone',
        'lat',
        'lng',
        'speed',
        'address',
        'status',
        'odometer',
        'engine_hours',
        'fuel_level',
        'raw_data',
        'tracker_timestamp',
    ];

    protected $casts = [
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'speed' => 'decimal:2',
        'odometer' => 'decimal:2',
        'engine_hours' => 'decimal:2',
        'fuel_level' => 'decimal:2',
        'raw_data' => 'array',
        'tracker_timestamp' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
