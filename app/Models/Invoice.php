<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
     use HasFactory;
     protected $fillable = [
        'order_id',
        'company_id',
        'invoice_number',
        'subtotal',
        'tax',
        'paid_amount',
    ];
    protected $appends = ['total', 'remaining'];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
    /*
    ارجاع المبلغ المدفوع
    */
    public function getTotalAttribute()
    {
      return round($this->subtotal + $this->tax, 2);
    }
    /*
    ارجاع المبلغ المتبقي
    */
    public function getRemainingAttribute()
    {
      return round($this->total - $this->paid_amount, 2);
    }
}
