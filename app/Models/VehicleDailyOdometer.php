<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDailyOdometer extends Model
{
    protected $table = 'vehicle_daily_odometer';

    protected $fillable = [
        'vehicle_id',
        'date',
        'odometer_km',
        'source',
    ];

    protected $casts = [
        'date' => 'date',
        'odometer_km' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
