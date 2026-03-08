<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverProposedService extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'image_path',
        'original_image_name',
        'status',
        'requested_by_driver_phone',
        'requested_at',
        'approved_at',
        'approved_by',
        'rejection_reason',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function approverCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'approved_by');
    }

    public function maintenanceRequestServices(): HasMany
    {
        return $this->hasMany(MaintenanceRequestService::class, 'driver_proposed_service_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
