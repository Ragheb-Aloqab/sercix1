<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    /**
     * Get the translated display name for this service.
     * Uses maintenance.predefined_services.{slug} — falls back to raw name if no translation.
     */
    public function getTranslatedName(): string
    {
        $key = 'maintenance.predefined_services.' . Str::slug($this->name, '_');
        $trans = __($key);
        return $trans !== $key ? $trans : $this->name;
    }
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'base_price',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_services')
            ->withPivot(['qty', 'unit_price', 'total_price'])
            ->withTimestamps();
    }
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_services')
            ->withPivot(['base_price', 'estimated_minutes', 'is_enabled'])
            ->withTimestamps();
    }

    public function companyMaintenanceInvoices()
    {
        return $this->belongsToMany(CompanyMaintenanceInvoice::class, 'company_maintenance_invoice_service')
            ->withTimestamps();
    }
}
