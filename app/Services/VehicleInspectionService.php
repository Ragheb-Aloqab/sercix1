<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyInspectionSetting;
use App\Models\Vehicle;
use App\Models\VehicleInspection;
use Carbon\Carbon;

class VehicleInspectionService
{
    /**
     * Get or create company inspection settings.
     */
    public function getOrCreateSettings(Company $company): CompanyInspectionSetting
    {
        return $company->inspectionSettings()->firstOrCreate(
            ['company_id' => $company->id],
            [
                'is_enabled' => false,
                'frequency_type' => CompanyInspectionSetting::FREQUENCY_MONTHLY,
                'deadline_days' => 3,
            ]
        );
    }

    /**
     * Get vehicles pending inspection for a company.
     */
    public function getPendingVehicles(Company $company): \Illuminate\Support\Collection
    {
        $settings = $this->getOrCreateSettings($company);
        if (!$settings->is_enabled) {
            return collect();
        }

        $vehicles = $company->vehicles()->where('is_active', true)->get();
        $pending = collect();

        foreach ($vehicles as $vehicle) {
            $status = $this->getVehicleInspectionStatus($vehicle);
            if (in_array($status['status'], ['pending', 'overdue'])) {
                $pending->push((object) array_merge(['vehicle' => $vehicle], $status));
            }
        }

        return $pending->sortBy(fn ($p) => $p->due_date?->timestamp ?? PHP_INT_MAX)->values();
    }

    /**
     * Get inspection status for a vehicle: compliant, pending, overdue.
     */
    public function getVehicleInspectionStatus(Vehicle $vehicle): array
    {
        $company = $vehicle->company;
        $settings = $this->getOrCreateSettings($company);

        if (!$settings->is_enabled) {
            return ['status' => 'compliant', 'due_date' => null, 'inspection' => null];
        }

        $latest = $vehicle->inspections()->latest('inspection_date')->first();

        if ($latest && $latest->status === VehicleInspection::STATUS_APPROVED) {
            $nextDue = $this->computeNextDueDate($vehicle, $latest->inspection_date, $settings);
            return [
                'status' => $nextDue && $nextDue->isFuture() ? 'compliant' : 'pending',
                'due_date' => $nextDue,
                'inspection' => $latest,
            ];
        }

        $dueDate = $this->getOrCreateDueDate($vehicle, $settings);
        $isOverdue = $dueDate && $dueDate->isPast();

        return [
            'status' => $isOverdue ? 'overdue' : 'pending',
            'due_date' => $dueDate,
            'inspection' => $vehicle->inspections()->where('status', VehicleInspection::STATUS_PENDING)->latest('due_date')->first(),
        ];
    }

    /**
     * Compute next due date based on frequency.
     */
    public function computeNextDueDate(Vehicle $vehicle, Carbon $lastInspectionDate, CompanyInspectionSetting $settings): ?Carbon
    {
        return match ($settings->frequency_type) {
            CompanyInspectionSetting::FREQUENCY_MONTHLY => $lastInspectionDate->copy()->addMonth()->startOfMonth(),
            CompanyInspectionSetting::FREQUENCY_EVERY_X_DAYS => $settings->frequency_days
                ? $lastInspectionDate->copy()->addDays($settings->frequency_days)
                : null,
            default => null,
        };
    }

    /**
     * Get or create pending inspection due date for a vehicle.
     */
    public function getOrCreateDueDate(Vehicle $vehicle, CompanyInspectionSetting $settings): ?Carbon
    {
        $latestApproved = $vehicle->inspections()
            ->where('status', VehicleInspection::STATUS_APPROVED)
            ->latest('inspection_date')
            ->first();

        if ($latestApproved) {
            return $this->computeNextDueDate($vehicle, $latestApproved->inspection_date, $settings);
        }

        $pending = $vehicle->inspections()->where('status', VehicleInspection::STATUS_PENDING)->latest('due_date')->first();
        if ($pending) {
            return $pending->due_date;
        }

        if ($settings->frequency_type === CompanyInspectionSetting::FREQUENCY_MANUAL) {
            return null;
        }

        $base = now()->startOfMonth();
        if ($settings->frequency_type === CompanyInspectionSetting::FREQUENCY_MONTHLY) {
            return $base->copy();
        }
        if ($settings->frequency_type === CompanyInspectionSetting::FREQUENCY_EVERY_X_DAYS && $settings->frequency_days) {
            return now()->addDays($settings->frequency_days);
        }
        return $base->copy();
    }

    /**
     * Create or update pending inspection for a vehicle (scheduled or manual).
     */
    public function createOrUpdateInspection(Vehicle $vehicle, string $requestType = VehicleInspection::REQUEST_SCHEDULED): VehicleInspection
    {
        $settings = $this->getOrCreateSettings($vehicle->company);
        $dueDate = $this->getOrCreateDueDate($vehicle, $settings)
            ?? now()->addDays($settings->deadline_days);

        $existing = $vehicle->inspections()
            ->where('status', VehicleInspection::STATUS_PENDING)
            ->where('request_type', $requestType)
            ->latest('due_date')
            ->first();

        if ($existing) {
            $existing->update(['due_date' => $dueDate]);
            return $existing;
        }

        return VehicleInspection::create([
            'vehicle_id' => $vehicle->id,
            'company_id' => $vehicle->company_id,
            'driver_phone' => $vehicle->driver_phone,
            'driver_name' => $vehicle->driver_name,
            'inspection_date' => now()->toDateString(),
            'due_date' => $dueDate,
            'status' => VehicleInspection::STATUS_PENDING,
            'request_type' => $requestType,
        ]);
    }

    /**
     * Schedule inspections for all vehicles of a company (e.g. monthly job).
     */
    public function scheduleInspectionsForCompany(Company $company): int
    {
        $settings = $this->getOrCreateSettings($company);
        if (!$settings->is_enabled || $settings->frequency_type !== CompanyInspectionSetting::FREQUENCY_MONTHLY) {
            return 0;
        }

        $count = 0;
        foreach ($company->vehicles()->where('is_active', true)->get() as $vehicle) {
            $status = $this->getVehicleInspectionStatus($vehicle);
            if ($status['status'] !== 'compliant' && ($status['due_date'] === null || $status['due_date']->isPast() || $status['due_date']->isCurrentMonth())) {
                $this->createOrUpdateInspection($vehicle, VehicleInspection::REQUEST_SCHEDULED);
                $count++;
            }
        }
        return $count;
    }

    /**
     * Count pending/overdue for dashboard.
     */
    public function getPendingCount(Company $company): int
    {
        return $this->getPendingVehicles($company)->count();
    }

    /**
     * Count overdue for badge.
     */
    public function getOverdueCount(Company $company): int
    {
        return $this->getPendingVehicles($company)->filter(fn ($p) => $p->status === 'overdue')->count();
    }
}
