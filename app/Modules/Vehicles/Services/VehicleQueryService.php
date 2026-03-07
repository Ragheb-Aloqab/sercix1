<?php

namespace App\Modules\Vehicles\Services;

use App\Models\Vehicle;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Vehicle query logic - extracted for testability and reuse.
 * Used by VehiclesList Livewire component.
 */
class VehicleQueryService
{
    public const STATUS_ALL = '';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public function __construct(
        private readonly int $companyId,
        private readonly int $perPage = 25
    ) {}

    public function paginate(
        string $search = '',
        string $status = self::STATUS_ALL,
        ?int $branchId = null
    ): LengthAwarePaginator {
        $query = $this->buildQuery($search, $status, $branchId);

        return $query
            ->with(['branch:id,name'])
            ->latest()
            ->paginate($this->perPage)
            ->withQueryString();
    }

    public function buildQuery(
        string $search = '',
        string $status = self::STATUS_ALL,
        ?int $branchId = null
    ): Builder {
        return Vehicle::query()
            ->where('company_id', $this->companyId)
            ->when($search !== '', $this->applySearch($search))
            ->when($status === self::STATUS_ACTIVE, fn (Builder $q) => $q->where('is_active', true))
            ->when($status === self::STATUS_INACTIVE, fn (Builder $q) => $q->where('is_active', false))
            ->when($branchId > 0, fn (Builder $q) => $q->where('company_branch_id', $branchId));
    }

    private function applySearch(string $search): callable
    {
        return function (Builder $query) use ($search) {
            $query->where(function (Builder $q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('imei', 'like', "%{$search}%");
            });
        };
    }
}
