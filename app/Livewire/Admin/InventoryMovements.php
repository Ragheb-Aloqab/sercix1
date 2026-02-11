<?php

namespace App\Livewire\Admin;

use App\Models\InventoryTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryMovements extends Component
{
    use WithPagination;

    public string $search = '';
    public string $type = '';
    public string $date_from = '';
    public string $date_to = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => ''],
        'date_from' => ['except' => ''],
        'date_to' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function applyFilters(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->type = '';
        $this->date_from = '';
        $this->date_to = '';
        $this->resetPage();
    }

    protected function baseQuery()
    {
        return InventoryTransaction::query()
            ->with(['item', 'creator'])
            ->when($this->search !== '', function ($q) {
                $q->whereHas('item', fn ($qq) => $qq->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%'));
            })
            ->when($this->type !== '', fn ($q) => $q->where('transaction_type', $this->type))
            ->when($this->date_from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->date_to))
            ->latest('created_at');
    }

    public function getStatsProperty(): array
    {
        $base = $this->baseQuery();

        $inTotal = (clone $base)->where('quantity_change', '>', 0)->sum('quantity_change');
        $outTotal = (int) (clone $base)->where('quantity_change', '<', 0)->sum(DB::raw('ABS(quantity_change)'));
        $adjustments = (clone $base)->where('transaction_type', 'adjustment')->count();
        $lastAt = (clone $base)->max('created_at');

        return [
            'in' => (int) $inTotal,
            'out' => (int) $outTotal,
            'adjustments' => $adjustments,
            'last_at' => $lastAt ? Carbon::parse($lastAt)->diffForHumans() : '—',
        ];
    }

    public function render()
    {
        $transactions = $this->baseQuery()->paginate(12);

        return view('livewire.admin.inventory-movements', [
            'transactions' => $transactions,
            'stats' => $this->stats,
        ]);
    }
}
