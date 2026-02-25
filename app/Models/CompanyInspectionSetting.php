<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyInspectionSetting extends Model
{
    public const FREQUENCY_MONTHLY = 'monthly';
    public const FREQUENCY_EVERY_X_DAYS = 'every_x_days';
    public const FREQUENCY_MANUAL = 'manual';

    protected $fillable = [
        'company_id',
        'is_enabled',
        'frequency_type',
        'frequency_days',
        'deadline_days',
        'block_if_overdue',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'block_if_overdue' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public static function requiredPhotoTypes(): array
    {
        return ['front', 'rear', 'left_side', 'right_side', 'interior', 'odometer'];
    }

    public static function optionalPhotoTypes(): array
    {
        return ['other'];
    }
}
