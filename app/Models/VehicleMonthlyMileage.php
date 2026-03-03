<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMonthlyMileage extends Model
{
    protected $table = 'vehicle_monthly_mileage';

    protected $fillable = [
        'vehicle_id',
        'month',
        'year',
        'start_odometer',
        'end_odometer',
        'total_km',
        'odometer_reset_detected',
        'is_closed',
    ];

    protected $casts = [
        'start_odometer' => 'decimal:2',
        'end_odometer' => 'decimal:2',
        'total_km' => 'decimal:2',
        'odometer_reset_detected' => 'boolean',
        'is_closed' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function isCurrentMonth(): bool
    {
        return $this->month === (int) now()->month && $this->year === (int) now()->year;
    }
}
