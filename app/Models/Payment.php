<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'status',
        'amount',
        'tap_charge_id',
        'tap_reference',
        'tap_payload',
        'paid_at',
        'bank_account_id',
        'sender_name',
        'receipt_path',
        'note',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'paid_at'     => 'datetime',
        'reviewed_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
        'tap_payload' => 'array',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return \App\Models\Company|null
     */
    public function getCompanyAttribute()
    {
        return $this->order?->company;
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id', 'order_id');
    }
}
