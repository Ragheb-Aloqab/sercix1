<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;
    protected $table = 'inventory_items';

    protected $fillable = [
        'name',
        'sku',
        'category',
        'unit_price',
        'qty',
        'min_qty',
        'is_active'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
