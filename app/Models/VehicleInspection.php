<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleInspection extends Model
{
    use BelongsToCompany;
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const REQUEST_SCHEDULED = 'scheduled';
    public const REQUEST_MANUAL = 'manual';

    protected $fillable = [
        'vehicle_id',
        'company_id',
        'driver_phone',
        'driver_name',
        'inspection_date',
        'due_date',
        'status',
        'request_type',
        'odometer_reading',
        'latitude',
        'longitude',
        'driver_notes',
        'reviewer_notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'due_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VehicleInspectionPhoto::class)->orderBy('sort_order');
    }

    public function hasRequiredPhotos(): bool
    {
        $required = CompanyInspectionSetting::requiredPhotoTypes();
        $uploaded = $this->photos()->pluck('photo_type')->toArray();
        foreach ($required as $type) {
            if (!in_array($type, $uploaded)) {
                return false;
            }
        }
        return true;
    }

    public function getPhotoByType(string $type): ?VehicleInspectionPhoto
    {
        return $this->photos()->where('photo_type', $type)->first();
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_PENDING && $this->due_date->isPast();
    }

    public function isCompliant(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }
}
