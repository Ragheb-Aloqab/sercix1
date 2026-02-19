<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Company;

class GlobalSearch extends Component
{
    public string $q = '';

    private function getOrderShowRoute(?string $role, Order $order): ?string
    {
        if ($role === 'admin') {
            return \Illuminate\Support\Facades\Route::has('admin.orders.show')
                ? route('admin.orders.show', $order)
                : null;
        }
        if ($role === 'company') {
            return \Illuminate\Support\Facades\Route::has('company.orders.show')
                ? route('company.orders.show', $order)
                : null;
        }
        if ($role === 'technician') {
            return \Illuminate\Support\Facades\Route::has('tech.orders.show')
                ? route('tech.orders.show', $order)
                : null;
        }
        return null;
    }

    private function actor(): array
    {
        // company guard
        if (Auth::guard('company')->check()) {
            return ['type' => 'company', 'role' => 'company', 'model' => Auth::guard('company')->user()];
        }

        // web guard
        if (Auth::guard('web')->check()) {
            $u = Auth::guard('web')->user();
            return ['type' => 'user', 'role' => ($u->role ?? null), 'model' => $u];
        }

        return ['type' => null, 'role' => null, 'model' => null];
    }

    public function render()
    {
        $orders = collect();
        $companies = collect();

        $actor = $this->actor();
        $role  = $actor['role'];
        $user  = $actor['model'];

        if (mb_strlen($this->q) >= 2 && $user) {
            $q = trim($this->q);

            // -------------------------
            // Orders query (role-based)
            // -------------------------
            $ordersQuery = Order::query()->latest();

            if (is_numeric($q)) {
                $ordersQuery->where('id', (int) $q);
            } else {
                $ordersQuery->where(function ($query) use ($q) {
                    $query->where('address', 'like', "%{$q}%")
                        ->orWhere('city', 'like', "%{$q}%")
                        ->orWhere('notes', 'like', "%{$q}%")
                        ->orWhere('requested_by_name', 'like', "%{$q}%")
                        ->orWhereHas('company', fn ($cq) => $cq->where('company_name', 'like', "%{$q}%"));
                });
            }

            if ($role === 'company') {
                
                $ordersQuery->where('company_id', $user->getKey());
            }

            if ($role === 'technician') {
                $ordersQuery->where('technician_id', $user->getKey());
            }

            $orders = $ordersQuery->limit(5)->get();

            // -------------------------
            // Companies query (admin فقط)
            // -------------------------
            if ($role === 'admin') {
                $companies = Company::query()
                    ->where(function ($query) use ($q) {
                        $query->where('company_name', 'like', "%{$q}%")
                            ->orWhere('phone', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    })
                    ->latest()
                    ->limit(5)
                    ->get();
            }
        }

        $ordersWithRoutes = $orders->map(function ($order) use ($role) {
            $orderShowRoute = $this->getOrderShowRoute($role, $order);
            return (object) ['order' => $order, 'orderShowRoute' => $orderShowRoute];
        });

        $companiesWithRoutes = $companies->map(function ($company) {
            $companyShowRoute = \Illuminate\Support\Facades\Route::has('admin.customers.edit')
                ? route('admin.customers.edit', $company)
                : null;
            return (object) ['company' => $company, 'companyShowRoute' => $companyShowRoute];
        });

        return view('livewire.dashboard.global-search', [
            'ordersWithRoutes' => $ordersWithRoutes,
            'companiesWithRoutes' => $companiesWithRoutes,
            'role' => $role,
        ]);
    }
}
