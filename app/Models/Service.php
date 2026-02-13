<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
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
    public function inventory()
    {
        return $this->hasOne(InventoryItem::class);
    }
}
