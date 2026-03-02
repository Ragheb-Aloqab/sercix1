<?php

namespace App\Services;

use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ExpiryMonitoringService
{
    public const STATUS_VALID = 'valid';
    public const STATUS_EXPIRING_SOON = 'expiring_soon';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_MISSING = 'missing';

    public const DOC_REGISTRATION = 'registration';
    public const DOC_INSURANCE = 'insurance';

    public const ALERT_DAYS_BEFORE = 10;

    /**
     * Get expiry status for a date.
     */
    public function getStatusForDate(?Carbon $date): string
    {
        if (!$date) {
            return self::STATUS_MISSING;
        }
        $days = $this->daysRemaining($date);
        if ($days === null) {
            return self::STATUS_MISSING;
        }
        if ($days < 0) {
            return self::STATUS_EXPIRED;
        }
        if ($days <= self::ALERT_DAYS_BEFORE) {
            return self::STATUS_EXPIRING_SOON;
        }
        return self::STATUS_VALID;
    }

    /**
     * Get days remaining (negative = expired).
     */
    public function daysRemaining(?Carbon $date): ?int
    {
        if (!$date) {
            return null;
        }
        return (int) Carbon::today()->startOfDay()->diffInDays($date->startOfDay(), false);
    }

    /**
     * Get document status for a vehicle.
     */
    public function getVehicleDocumentStatus(Vehicle $vehicle): array
    {
        return [
            self::DOC_REGISTRATION => [
                'status' => $this->getStatusForDate($vehicle->registration_expiry_date),
                'date' => $vehicle->registration_expiry_date,
                'days_remaining' => $this->daysRemaining($vehicle->registration_expiry_date),
                'has_document' => !empty($vehicle->registration_document_path),
            ],
            self::DOC_INSURANCE => [
                'status' => $this->getStatusForDate($vehicle->insurance_expiry_date),
                'date' => $vehicle->insurance_expiry_date,
                'days_remaining' => $this->daysRemaining($vehicle->insurance_expiry_date),
                'has_document' => !empty($vehicle->insurance_document_path),
            ],
        ];
    }

    /**
     * Get all expiring items (expiring soon or expired) for a company.
     */
    public function getExpiringForCompany(int $companyId, ?string $filter = null): Collection
    {
        $vehicles = Vehicle::query()
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->whereNotNull('registration_expiry_date')
                    ->orWhereNotNull('insurance_expiry_date');
            })
            ->with('company:id,company_name')
            ->get();

        $items = collect();
        foreach ($vehicles as $v) {
            $status = $this->getVehicleDocumentStatus($v);
            foreach ([self::DOC_REGISTRATION, self::DOC_INSURANCE] as $type) {
                $s = $status[$type];
                if ($s['status'] === self::STATUS_MISSING) {
                    continue;
                }
                if ($filter === 'expired' && $s['status'] !== self::STATUS_EXPIRED) {
                    continue;
                }
                if ($filter === 'expiring_soon' && $s['status'] !== self::STATUS_EXPIRING_SOON) {
                    continue;
                }
                if ($s['status'] === self::STATUS_VALID) {
                    continue;
                }
                $items->push((object) [
                    'vehicle' => $v,
                    'type' => $type,
                    'status' => $s['status'],
                    'date' => $s['date'],
                    'days_remaining' => $s['days_remaining'],
                    'has_document' => $s['has_document'],
                ]);
            }
        }
        return $items->sortBy(fn ($i) => $i->days_remaining ?? 999);
    }

    /**
     * Get all expiring items across all companies (for admin).
     */
    public function getExpiringForAdmin(?int $companyId = null, ?string $filter = null): Collection
    {
        $query = Vehicle::query()
            ->where(function ($q) {
                $q->whereNotNull('registration_expiry_date')
                    ->orWhereNotNull('insurance_expiry_date');
            })
            ->with('company:id,company_name');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $vehicles = $query->get();
        $items = collect();
        foreach ($vehicles as $v) {
            $status = $this->getVehicleDocumentStatus($v);
            foreach ([self::DOC_REGISTRATION, self::DOC_INSURANCE] as $type) {
                $s = $status[$type];
                if ($s['status'] === self::STATUS_MISSING) {
                    continue;
                }
                if ($filter === 'expired' && $s['status'] !== self::STATUS_EXPIRED) {
                    continue;
                }
                if ($filter === 'expiring_soon' && $s['status'] !== self::STATUS_EXPIRING_SOON) {
                    continue;
                }
                if ($s['status'] === self::STATUS_VALID) {
                    continue;
                }
                $items->push((object) [
                    'vehicle' => $v,
                    'type' => $type,
                    'status' => $s['status'],
                    'date' => $s['date'],
                    'days_remaining' => $s['days_remaining'],
                    'has_document' => $s['has_document'],
                ]);
            }
        }
        return $items->sortBy(fn ($i) => $i->days_remaining ?? 999);
    }

    /**
     * Count expiring/expired items for badge.
     * Uses optimized query when only count is needed (e.g. mobile dashboard).
     */
    public function countExpiringForCompany(int $companyId): int
    {
        $today = Carbon::today();
        $threshold = $today->copy()->addDays(self::ALERT_DAYS_BEFORE);

        $count = \Illuminate\Support\Facades\DB::table('vehicles')
            ->where('company_id', $companyId)
            ->where(function ($q) use ($today, $threshold) {
                $q->where(function ($q) use ($today, $threshold) {
                    $q->whereNotNull('registration_expiry_date')
                        ->where('registration_expiry_date', '<=', $threshold);
                })->orWhere(function ($q) use ($today, $threshold) {
                    $q->whereNotNull('insurance_expiry_date')
                        ->where('insurance_expiry_date', '<=', $threshold);
                });
            })
            ->selectRaw('
                (CASE WHEN registration_expiry_date IS NOT NULL AND registration_expiry_date <= ? THEN 1 ELSE 0 END) +
                (CASE WHEN insurance_expiry_date IS NOT NULL AND insurance_expiry_date <= ? THEN 1 ELSE 0 END)
                as cnt
            ', [$threshold, $threshold])
            ->get()
            ->sum(fn ($r) => (int) ($r->cnt ?? 0));

        return (int) $count;
    }

    public function countExpiringForAdmin(?int $companyId = null): int
    {
        return $this->getExpiringForAdmin($companyId)->count();
    }

    /**
     * Get CSS class for status badge.
     */
    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            self::STATUS_VALID => 'bg-emerald-500/30 text-emerald-300 border-emerald-400/50',
            self::STATUS_EXPIRING_SOON => 'bg-amber-500/30 text-amber-300 border-amber-400/50',
            self::STATUS_EXPIRED => 'bg-red-500/30 text-red-300 border-red-400/50',
            default => 'bg-slate-600/30 text-slate-400 border-slate-500/50',
        };
    }
}
