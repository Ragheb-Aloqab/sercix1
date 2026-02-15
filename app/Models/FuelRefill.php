<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelRefill extends Model
{
    public const PROVIDER_MANUAL = 'manual';

    protected $fillable = [
        'vehicle_id',
        'company_id',
        'liters',
        'cost',
        'price_per_liter',
        'refilled_at',
        'odometer_km',
        'fuel_type',
        'notes',
        'provider',
        'external_id',
        'external_metadata',
        'receipt_path',
        'logged_by_phone',
        'logged_by_user_id',
    ];

    protected $casts = [
        'refilled_at' => 'datetime',
        'liters' => 'float',
        'cost' => 'float',
        'price_per_liter' => 'float',
        'odometer_km' => 'integer',
        'external_metadata' => 'array',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function loggedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by_user_id');
    }

    /** Whether this refill was synced from an external fuel provider API */
    public function isFromExternalProvider(): bool
    {
        return $this->provider !== self::PROVIDER_MANUAL;
    }
}
