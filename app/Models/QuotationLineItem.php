<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationLineItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'maintenance_request_service_id',
        'price',
        'image_path',
        'original_image_name',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function maintenanceRequestService(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequestService::class, 'maintenance_request_service_id');
    }
}
