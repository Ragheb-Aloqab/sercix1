<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'company_id',
        'method',
        'status',
        'amount',
        'tap_charge_id',
        'tap_reference',
        'tap_payload',
        'paid_at',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'paid_at'    => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'tap_payload'=> 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'order_id');
    }
}
