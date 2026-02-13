<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Company;

class GlobalSearch extends Component
{
    public string $q = '';

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
                // ✅ عدّل اسم العمود حسب مشروعك: company_id أو customer_id ...
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

        return view('livewire.dashboard.global-search', [
            'orders' => $orders,
            'companies' => $companies,
            'role' => $role,
        ]);
    }
}
