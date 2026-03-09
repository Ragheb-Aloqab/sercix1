<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'tag',
        'description',
        'price',
        'price_unit',
        'is_active',
        'sort_order',
        'features',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'features' => 'array',
        'price' => 'decimal:2',
    ];

    /** All known feature keys used for plan/UI. */
    public const FEATURES = [
        'fuel_manual',
        'maintenance_manual',
        'basic_reports',
        'dashboard',
        'driver_accounts',
        'request_maintenance_offers',
        'limited_vehicles',
        'data_assistant_partial',
        'auto_fuel_invoice',
        'auto_maintenance_invoice',
        'vehicle_cost_reports',
        'distance_reports',
        'tax_reports',
        'cost_per_km',
        'enhanced_driver_accounts',
        'driver_alerts',
        'vehicle_tracking',
        'advanced_reports',
        'white_label',
        'api_integration',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class, 'plan_id');
    }

    public function hasFeature(string $key): bool
    {
        $features = $this->features ?? [];
        return in_array($key, $features, true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
