<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $table = 'inventory_transactions';

    protected $fillable = [
        'inventory_item_id',
        'transaction_type',
        'quantity_change',
        'new_quantity',
        'unit_price',
        'related_order_type',
        'reference_number',
        'notes',
        'related_order_id',
        'created_by',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // الصنف المرتبط بالحركة
    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    // الطلب المرتبط بالحركة (اختياري)
    public function order()
    {
        return $this->belongsTo(Order::class, 'related_order_id');
    }

    // المستخدم الذي قام بالحركة
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (اختياري)
    |--------------------------------------------------------------------------
    */

    // هل الحركة دخول؟
    public function isIn(): bool
    {
        return $this->transaction_type === 'in';
    }

    // هل الحركة خروج؟
    public function isOut(): bool
    {
        return $this->transaction_type === 'out';
    }
}