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
        'make',
        'model',
        'year',
        'plate_number',
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
}
