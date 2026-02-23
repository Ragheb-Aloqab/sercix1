<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
        'company_id',
        'company_branch_id',
        'type',
        'name',
        'make',
        'model',
        'year',
        'plate_number',
        'imei',
        'color',
        'image_path',
        'driver_name',
        'driver_phone',
        'is_active',
    ];

  
    use HasFactory;
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
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

    /** Display name: name or make+model */
    public function getDisplayNameAttribute(): string
    {
        if (!empty($this->name)) {
            return $this->name;
        }
        return trim(($this->make ?? '') . ' ' . ($this->model ?? '')) ?: $this->plate_number ?? __('vehicles.vehicle');
    }
}
