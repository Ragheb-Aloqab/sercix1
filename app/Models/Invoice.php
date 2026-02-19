<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    public const TYPE_SERVICE = 'service';
    public const TYPE_FUEL = 'fuel';

    protected $fillable = [
        'order_id',
        'fuel_refill_id',
        'company_id',
        'invoice_type',
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

    public function fuelRefill()
    {
        return $this->belongsTo(FuelRefill::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Payments for this invoice's order (shared order_id).
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id', 'order_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function isFuel(): bool
    {
        return $this->invoice_type === self::TYPE_FUEL;
    }

    public function isService(): bool
    {
        return $this->invoice_type === self::TYPE_SERVICE;
    }

    /** Driver name: from order (requested_by_name) or vehicle (driver_name) for fuel */
    public function getDriverNameAttribute(): ?string
    {
        if ($this->order_id && $this->relationLoaded('order')) {
            return $this->order->requested_by_name ?? $this->order->vehicle?->driver_name;
        }
        if ($this->fuel_refill_id && $this->relationLoaded('fuelRefill')) {
            return $this->fuelRefill->vehicle?->driver_name;
        }
        $this->loadMissing(['order.vehicle', 'fuelRefill.vehicle']);
        return $this->order?->requested_by_name ?? $this->order?->vehicle?->driver_name ?? $this->fuelRefill?->vehicle?->driver_name;
    }

    /** Driver phone: from order or vehicle */
    public function getDriverPhoneAttribute(): ?string
    {
        if ($this->order_id && $this->relationLoaded('order')) {
            return $this->order->driver_phone ?? $this->order->vehicle?->driver_phone;
        }
        if ($this->fuel_refill_id && $this->relationLoaded('fuelRefill')) {
            return $this->fuelRefill->vehicle?->driver_phone ?? $this->fuelRefill->logged_by_phone;
        }
        $this->loadMissing(['order.vehicle', 'fuelRefill.vehicle']);
        return $this->order?->driver_phone ?? $this->order?->vehicle?->driver_phone ?? $this->fuelRefill?->vehicle?->driver_phone ?? $this->fuelRefill?->logged_by_phone;
    }

    /** Vehicle for this invoice */
    public function getVehicleAttribute()
    {
        if ($this->order_id && $this->relationLoaded('order')) {
            return $this->order->vehicle;
        }
        if ($this->fuel_refill_id && $this->relationLoaded('fuelRefill')) {
            return $this->fuelRefill->vehicle;
        }
        $this->loadMissing(['order.vehicle', 'fuelRefill.vehicle']);
        return $this->order?->vehicle ?? $this->fuelRefill?->vehicle;
    }

    /** Service type label: vehicle service or fuel (petrol/diesel) */
    public function getServiceTypeLabelAttribute(): string
    {
        if ($this->isFuel() && $this->fuelRefill) {
            $type = $this->fuelRefill->fuel_type ?? 'petrol';
            return __('fuel.' . $type) ?: ucfirst($type);
        }
        return __('invoice.vehicle_service') ?: 'Vehicle Service';
    }

    /** Uploaded invoice image path: driver_invoice attachment or fuel receipt */
    public function getInvoiceImagePathAttribute(): ?string
    {
        if ($this->order_id) {
            $att = $this->order->attachments()->where('type', 'driver_invoice')->first();
            return $att?->file_path;
        }
        if ($this->fuel_refill_id && $this->fuelRefill) {
            return $this->fuelRefill->receipt_path;
        }
        return null;
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
        if ($this->order_id && $this->relationLoaded('order') && $this->order) {
            return round((float) $this->order->total_amount, 2);
        }
        if ($this->fuel_refill_id && $this->relationLoaded('fuelRefill') && $this->fuelRefill) {
            return round((float) $this->fuelRefill->cost, 2);
        }
        $this->loadMissing(['order', 'fuelRefill']);
        if ($this->order) {
            return round((float) $this->order->total_amount, 2);
        }
        if ($this->fuelRefill) {
            return round((float) $this->fuelRefill->cost, 2);
        }
        return 0.0;
    }

    /*
    ارجاع المبلغ المتبقي
    */
    public function getRemainingAttribute()
    {
        return round($this->total - ($this->paid_amount ?? 0), 2);
    }
}
