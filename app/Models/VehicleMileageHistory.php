<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMileageHistory extends Model
{
    public const TRACKING_GPS = 'gps';
    public const TRACKING_MANUAL = 'manual';

    public const SOURCE_VEHICLE_LOCATIONS = 'vehicle_locations';
    public const SOURCE_FUEL_REFILLS = 'fuel_refills';
    public const SOURCE_MOBILE_TRIPS = 'mobile_tracking_trips';
    public const SOURCE_MANUAL_DAILY = 'manual_daily';

    protected $table = 'vehicle_mileage_history';

    protected $fillable = [
        'vehicle_id',
        'tracking_type',
        'previous_reading',
        'current_reading',
        'calculated_difference',
        'recorded_date',
        'source',
    ];

    protected $casts = [
        'previous_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'calculated_difference' => 'decimal:2',
        'recorded_date' => 'date',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
