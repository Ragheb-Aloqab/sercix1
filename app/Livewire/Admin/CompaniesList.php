<?php

namespace App\Livewire\Admin;

use App\Models\Company;
use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithPagination;

class CompaniesList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    protected $queryString = ['search' => ['except' => '']];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getCompaniesProperty()
    {
        return Company::query()
            ->when($this->search, fn ($q) => $q->where(function ($query) {
                $query->where('company_name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            }))
            ->withCount(['vehicles', 'orders'])
            ->selectRaw('(SELECT COALESCE(COUNT(DISTINCT driver_phone), 0) FROM vehicles WHERE vehicles.company_id = companies.id AND driver_phone IS NOT NULL) as drivers_count')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(25);
    }

    public function render()
    {
        return view('livewire.admin.companies-list', [
            'companies' => $this->companies,
        ]);
    }
}
