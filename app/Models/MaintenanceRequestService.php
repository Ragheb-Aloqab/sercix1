<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceRequestService extends Model
{
    protected $fillable = [
        'maintenance_request_id',
        'service_id',
        'driver_proposed_service_id',
        'sort_order',
    ];

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function driverProposedService(): BelongsTo
    {
        return $this->belongsTo(DriverProposedService::class, 'driver_proposed_service_id');
    }

    public function quotationLineItems(): HasMany
    {
        return $this->hasMany(QuotationLineItem::class, 'maintenance_request_service_id');
    }

    /** Display name: from predefined service or from driver-proposed. */
    public function getDisplayNameAttribute(): string
    {
        if ($this->service_id) {
            return $this->service?->getTranslatedName() ?? $this->service?->name ?? '';
        }
        return $this->driverProposedService?->name ?? '';
    }

    /** Whether this line needs company approval (driver-proposed and still pending). */
    public function needsApproval(): bool
    {
        return $this->driver_proposed_service_id !== null
            && $this->driverProposedService?->isPending();
    }
}
