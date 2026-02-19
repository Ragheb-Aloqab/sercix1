<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id',
        'vehicle_id',
        'technician_id',
        'status',
        'scheduled_at',
        'city',
        'address',
        'lat',
        'lng',
        'notes',
        'rejection_reason',
        'requested_by_name',
        'driver_phone',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function statusLogs()
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }



    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }
    public function services()
    {
        return $this->belongsToMany(\App\Models\Service::class, 'order_services')
            ->withPivot(['qty', 'unit_price', 'total_price', 'custom_service_name', 'custom_service_description'])
            ->withTimestamps();
    }
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function beforePhotos()
    {
        return $this->hasMany(Attachment::class)->where('type', 'before_photo');
    }

    public function afterPhotos()
    {
        return $this->hasMany(Attachment::class)->where('type', 'after_photo');
    }

    /**
     * إجمالي الطلب من خدمات البيفوت (للعرض عندما لا يوجد عمود total_amount في الجدول).
     */
    public function getTotalAmountAttribute(): float
    {
        $items = $this->relationLoaded('orderServices') ? $this->orderServices : $this->orderServices()->get();
        if ($items->isEmpty() && $this->relationLoaded('services')) {
            $items = $this->services;
        }
        if ($items->isEmpty()) {
            return 0.0;
        }
        return (float) $items->sum(function ($s) {
            $qty = (float) ($s->qty ?? $s->pivot->qty ?? 0);
            $unit = (float) ($s->unit_price ?? $s->pivot->unit_price ?? 0) ?: (float) ($s->base_price ?? 0);
            return (float) ($s->total_price ?? $s->pivot->total_price ?? ($qty * $unit));
        });
    }

    public function orderServices()
    {
        return $this->hasMany(OrderService::class);
    }

    public function driverInvoice()
    {
        return $this->hasOne(Attachment::class)->where('type', 'driver_invoice');
    }

    /** Quotation invoice uploaded by driver at request submission (required for company approval) */
    public function quotationInvoice()
    {
        return $this->hasOne(Attachment::class)->where('type', 'quotation_invoice');
    }

    public function hasQuotationInvoice(): bool
    {
        return $this->quotationInvoice()->exists();
    }
}
