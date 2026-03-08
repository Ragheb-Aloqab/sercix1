<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_request_id',
        'maintenance_center_id',
        'price',
        'estimated_duration_minutes',
        'notes',
        'quotation_pdf_path',
        'original_pdf_name',
        'invoice_image_path',
        'invoice_image_original_name',
        'submitted_by',
        'submitted_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function maintenanceCenter()
    {
        return $this->belongsTo(MaintenanceCenter::class);
    }

    public function lineItems()
    {
        return $this->hasMany(QuotationLineItem::class)->orderBy('id');
    }

    /** Total price from line items when present, else legacy single price. */
    public function getTotalPriceAttribute(): float
    {
        if ($this->relationLoaded('lineItems') && $this->lineItems->isNotEmpty()) {
            return (float) $this->lineItems->sum('price');
        }
        return (float) ($this->price ?? 0);
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }
}
