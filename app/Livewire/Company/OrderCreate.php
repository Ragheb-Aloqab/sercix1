<?php

namespace App\Livewire\Company;

use App\Models\Order;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OrderCreate extends Component
{
    public $vehicle_id = '';
    public $service_ids = [];
    public $company_branch_id = '';
    public string $notes = '';

    public $services;
    public $branches;
    public $vehicles;

    public function mount(): void
    {
        $company = auth('company')->user();

        $this->services = Service::query()
            ->select('services.*')
            ->leftJoin('company_services as cs', function ($join) use ($company) {
                $join->on('cs.service_id', '=', 'services.id')
                    ->where('cs.company_id', '=', $company->id);
            })
            ->addSelect([
                'cs.base_price as pivot_base_price',
                'cs.estimated_minutes as pivot_estimated_minutes',
                'cs.is_enabled as pivot_is_enabled',
            ])
            ->where(function ($q) {
                $q->whereNull('cs.is_enabled')->orWhere('cs.is_enabled', 1);
            })
            ->orderBy('services.name')
            ->get();

        $this->branches = $company->branches()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $this->vehicles = $company->vehicles()->orderByDesc('id')->get();
    }

    protected function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'integer'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'company_branch_id' => ['nullable', 'integer', 'exists:company_branches,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save()
    {
        $this->validate();

        $company = auth('company')->user();

        abort_unless(
            $company->vehicles()->where('id', $this->vehicle_id)->exists(),
            403,
            'Invalid vehicle.'
        );

        if ($this->company_branch_id !== '') {
            abort_unless(
                $company->branches()->where('id', $this->company_branch_id)->exists(),
                403,
                'Invalid branch.'
            );
        }

        $services = Service::query()
            ->select('services.*')
            ->leftJoin('company_services as cs', function ($join) use ($company) {
                $join->on('cs.service_id', '=', 'services.id')
                    ->where('cs.company_id', '=', $company->id);
            })
            ->addSelect(['cs.base_price as pivot_base_price', 'cs.is_enabled as pivot_is_enabled'])
            ->whereIn('services.id', $this->service_ids)
            ->where(function ($q) {
                $q->whereNull('cs.is_enabled')->orWhere('cs.is_enabled', 1);
            })
            ->get();

        abort_unless(
            $services->count() === count($this->service_ids),
            403,
            'One or more services are not enabled.'
        );

        $amount = (float) $services->sum(fn ($s) => (float) ($s->pivot_base_price ?? $s->base_price ?? 0));

        $order = DB::transaction(function () use ($company, $services) {
            $order = Order::create([
                'company_id' => $company->id,
                'vehicle_id' => (int) $this->vehicle_id,
                'status' => \App\Support\OrderStatus::APPROVED,
                'notes' => $this->notes ?: null,
            ]);

            $syncData = [];
            foreach ($services as $s) {
                $qty = 1;
                $unitPrice = (float) ($s->pivot_base_price ?? $s->base_price ?? 0);
                $syncData[$s->id] = [
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'total_price' => $qty * $unitPrice,
                ];
            }
            $order->services()->sync($syncData);

            return $order;
        });

        session()->flash('success', 'Order created successfully.');

        return $this->redirect(route('company.orders.show', $order->id), navigate: true);
    }

    public function render()
    {
        $servicesWithDisplay = $this->services->map(function ($service) {
            $price = $service->pivot_base_price ?? $service->base_price ?? null;
            $minutes = $service->pivot_estimated_minutes ?? null;
            return (object) ['service' => $service, 'price' => $price, 'minutes' => $minutes];
        });

        return view('livewire.company.order-create', [
            'servicesWithDisplay' => $servicesWithDisplay,
            'branches' => $this->branches,
            'vehicles' => $this->vehicles,
        ]);
    }
}
