<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public const TRACKING_DEVICE_API = 'device_api';
    public const TRACKING_MOBILE = 'mobile';

    protected $fillable = [
        'company_id',
        'company_branch_id',
        'type',
        'name',
        'make',
        'model',
        'year',
        'plate_number',
        'original_vehicle_number',
        'imei',
        'tracking_source',
        'is_tracking_active',
        'tracking_driver_phone',
        'color',
        'image_path',
        'registration_document_path',
        'registration_expiry_date',
        'insurance_document_path',
        'insurance_expiry_date',
        'driver_name',
        'driver_phone',
        'is_active',
    ];

    protected $casts = [
        'registration_expiry_date' => 'date',
        'insurance_expiry_date' => 'date',
        'is_tracking_active' => 'boolean',
    ];

    protected $attributes = [
        'tracking_source' => self::TRACKING_DEVICE_API,
    ];

    public function usesMobileTracking(): bool
    {
        return $this->tracking_source === self::TRACKING_MOBILE;
    }

    public function usesDeviceApiTracking(): bool
    {
        return $this->tracking_source === self::TRACKING_DEVICE_API;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
    public function branch()
    {
        return $this->belongsTo(\App\Models\CompanyBranch::class, 'company_branch_id');
    }

    public function fuelRefills()
    {
        return $this->hasMany(FuelRefill::class)->orderByDesc('refilled_at');
    }

    public function locations()
    {
        return $this->hasMany(VehicleLocation::class)->orderByDesc('tracker_timestamp');
    }

    public function latestLocation()
    {
        return $this->hasOne(VehicleLocation::class)->latestOfMany('tracker_timestamp');
    }

    public function inspections()
    {
        return $this->hasMany(VehicleInspection::class)->orderByDesc('inspection_date');
    }

    public function latestInspection()
    {
        return $this->hasOne(VehicleInspection::class)->latestOfMany('inspection_date');
    }

    public function mobileTrackingTrips()
    {
        return $this->hasMany(MobileTrackingTrip::class)->orderByDesc('started_at');
    }

    public function vehicleMonthlyMileage()
    {
        return $this->hasMany(VehicleMonthlyMileage::class)->orderByDesc('year')->orderByDesc('month');
    }

    /** Display name: name or make+model */
    public function getDisplayNameAttribute(): string
    {
        if (!empty($this->name)) {
            return $this->name;
        }
        return trim(($this->make ?? '') . ' ' . ($this->model ?? '')) ?: $this->plate_number ?? __('vehicles.vehicle');
    }
}
