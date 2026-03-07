<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Company extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'company_name',
        'phone',
        'email',
        'status',
        'theme_preference',
        'vehicle_quota',
        'password',
        'city',
        'address',
        'tracking_api_key',
        'tracking_base_url',
        'subdomain',
        'primary_color',
        'secondary_color',
        'logo',
        'white_label_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'tracking_api_key',
    ];

    protected $casts = [
        'tracking_api_key' => 'encrypted',
        'white_label_enabled' => 'boolean',
    ];

    /**
     * Get primary color with fallback (for branding).
     */
    public function getResolvedPrimaryColor(): string
    {
        return $this->attributes['primary_color'] ?? '#2563eb';
    }

    /**
     * Get secondary color with fallback (for branding).
     */
    public function getResolvedSecondaryColor(): string
    {
        return $this->attributes['secondary_color'] ?? '#16a34a';
    }

    /**
     * Get logo URL (storage path or null if none).
     */
    public function getLogoUrl(): ?string
    {
        if ($this->logo) {
            return \Illuminate\Support\Facades\Storage::url($this->logo);
        }
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations (كما هي عندك)
    |--------------------------------------------------------------------------
    */
    public function branches()
    {
        return $this->hasMany(\App\Models\CompanyBranch::class);
    }
    public function services()
    {
        return $this->belongsToMany(\App\Models\Service::class, 'company_services')
            ->withPivot(['base_price', 'estimated_minutes', 'is_enabled'])
            ->withTimestamps();
    }


    public function vehicles()
    {
        return $this->hasMany(\App\Models\Vehicle::class);
    }

    public function inspectionSettings()
    {
        return $this->hasOne(\App\Models\CompanyInspectionSetting::class);
    }

    public function vehicleInspections()
    {
        return $this->hasMany(\App\Models\VehicleInspection::class);
    }
    public function orderTemplates()
    {
        return $this->hasMany(\App\Models\OrderTemplate::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function maintenanceCenters()
    {
        return $this->belongsToMany(MaintenanceCenter::class, 'company_maintenance_center')
            ->withTimestamps();
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function vehicleQuotaRequests()
    {
        return $this->hasMany(VehicleQuotaRequest::class);
    }

    /** Effective vehicle quota (null = unlimited) */
    public function getVehicleQuotaLimit(): ?int
    {
        return $this->vehicle_quota;
    }

    /** Whether company can add more vehicles (under quota or has pending/approved request) */
    public function canAddVehicle(): bool
    {
        $quota = $this->getVehicleQuotaLimit();
        if ($quota === null) {
            return true;
        }
        $current = $this->vehicles()->count();
        if ($current < $quota) {
            return true;
        }
        $approvedExtra = $this->vehicleQuotaRequests()
            ->where('status', VehicleQuotaRequest::STATUS_APPROVED)
            ->sum('requested_count');
        return ($current + $approvedExtra) < ($quota + $approvedExtra); // Has approved increase
    }

    /** Current vehicles count vs quota (for display) */
    public function getQuotaUsage(): array
    {
        $current = $this->vehicles()->count();
        $quota = $this->getVehicleQuotaLimit();
        $pending = $this->vehicleQuotaRequests()->where('status', VehicleQuotaRequest::STATUS_PENDING)->count();
        return [
            'current' => $current,
            'quota' => $quota,
            'pending_requests' => $pending,
            'at_limit' => $quota !== null && $current >= $quota,
            'usage_percent' => $quota > 0 ? min(100, round(($current / $quota) * 100, 1)) : 0,
        ];
    }

    /** Check if company has pending quota request */
    public function hasPendingQuotaRequest(): bool
    {
        return $this->vehicleQuotaRequests()->where('status', VehicleQuotaRequest::STATUS_PENDING)->exists();
    }

    public function otpVerifications()
    {
        return $this->hasMany(OtpVerification::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function companyMaintenanceInvoices()
    {
        return $this->hasMany(CompanyMaintenanceInvoice::class);
    }
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    /*
    |--------------------------------------------------------------------------
    | Fleet / cost analytics — delegated to CompanyAnalyticsService
    |--------------------------------------------------------------------------
    */

    public function analytics(): \App\Services\CompanyAnalyticsService
    {
        return \App\Services\CompanyAnalyticsService::for($this);
    }

    public function maintenanceCost(): float
    {
        return $this->analytics()->maintenanceCost();
    }

    public function fuelsCost(): float
    {
        return $this->analytics()->fuelsCost();
    }

    public function otherCost(): float
    {
        return $this->analytics()->otherCost();
    }

    public function getFuelCostsSummary(?\Carbon\Carbon $dateFrom = null, ?\Carbon\Carbon $dateTo = null, ?int $vehicleId = null): array
    {
        return $this->analytics()->getFuelCostsSummary($dateFrom, $dateTo, $vehicleId);
    }

    public function getMaintenanceCostsSummary(?\Carbon\Carbon $dateFrom = null, ?\Carbon\Carbon $dateTo = null, ?int $vehicleId = null): array
    {
        return $this->analytics()->getMaintenanceCostsSummary($dateFrom, $dateTo, $vehicleId);
    }

    public function totalActualCost(): float
    {
        return $this->analytics()->totalActualCost();
    }

    public function dailyCost(): float
    {
        return $this->analytics()->dailyCost();
    }

    public function monthlyCost(): float
    {
        return $this->analytics()->monthlyCost();
    }

    public function dailyProgressPercentage(): float
    {
        return $this->analytics()->dailyProgressPercentage();
    }

    public function monthlyProgressPercentage(): float
    {
        return $this->analytics()->monthlyProgressPercentage();
    }

    public function lastSevenMonthsComparison(): array
    {
        return $this->analytics()->lastSevenMonthsComparison();
    }

    public function lastSevenMonthsPercentage(): float
    {
        return $this->analytics()->lastSevenMonthsPercentage();
    }

    public function getTopVehiclesByServiceConsumptionAndCost()
    {
        return $this->analytics()->getTopVehiclesByServiceConsumptionAndCost();
    }

    public function getTop5VehiclesSummary(): array
    {
        return $this->analytics()->getTop5VehiclesSummary();
    }

    public function maintenanceCostIndicator(): array
    {
        return $this->analytics()->maintenanceCostIndicator();
    }

    public function fuelConsumptionIndicator(): array
    {
        return $this->analytics()->fuelConsumptionIndicator();
    }

    public function fuelCostByMonth(): array
    {
        return $this->analytics()->fuelCostByMonth();
    }

    public function operatingCostIndicator(): array
    {
        return $this->analytics()->operatingCostIndicator();
    }
}
