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
    ارجاع إجمالي الفاتورة (من الحقول أو من الطلب إذا كانت قديمة ولم تُملأ)
    */
    public function getTotalAttribute()
    {
        $sum = (float) $this->subtotal + (float) $this->tax;
        if ($sum > 0) {
            return round($sum, 2);
        }
        if ($this->relationLoaded('order') && $this->order) {
            return round((float) $this->order->total_amount, 2);
        }
        $this->loadMissing('order');
        return $this->order ? round((float) $this->order->total_amount, 2) : 0.0;
    }
    /*
    ارجاع المبلغ المتبقي
    */
    public function getRemainingAttribute()
    {
      return round($this->total - $this->paid_amount, 2);
    }
}
