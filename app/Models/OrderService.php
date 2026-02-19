<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderService extends Model
{
    use HasFactory;

    protected $table = 'order_services';

    protected $fillable = [
        'order_id',
        'service_id',
        'custom_service_name',
        'custom_service_description',
        'qty',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->custom_service_name) {
            return $this->custom_service_name;
        }
        return $this->service?->name ?? '-';
    }
}
