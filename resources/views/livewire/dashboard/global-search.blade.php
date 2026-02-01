<div class="relative hidden sm:block">
    <div class="flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800">
        <i class="fa-solid fa-magnifying-glass text-slate-500 dark:text-slate-400"></i>

        <input type="text" wire:model.live.debounce.400ms="q"
            class="bg-transparent outline-none placeholder:text-slate-400 text-sm w-56"
            placeholder="ابحث عن طلب / شركة..." />
    </div>

    @if (mb_strlen($q) >= 2)
        <div
            class="absolute end-0 mt-2 w-[360px] max-w-[92vw] rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft z-50 overflow-hidden">

            {{-- Orders --}}
            @if ($orders->count())
                <div class="px-4 py-2 text-xs font-bold text-slate-500">الطلبات</div>

                @foreach ($orders as $order)
                    @php
                        // ✅ روابط مختلفة حسب الرول
                        $orderShowRoute = null;

                        if ($role === 'admin') {
                            $orderShowRoute = \Illuminate\Support\Facades\Route::has('admin.orders.show')
                                ? route('admin.orders.show', $order)
                                : null;
                        } elseif ($role === 'company') {
                            $orderShowRoute = \Illuminate\Support\Facades\Route::has('company.orders.show')
                                ? route('company.orders.show', $order)
                                : null;
                        } elseif ($role === 'technician') {
                            $orderShowRoute = \Illuminate\Support\Facades\Route::has('tech.orders.show')
                                ? route('tech.orders.show', $order)
                                : null;
                        }

                        // fallback عام
                        if (!$orderShowRoute) {
                            $orderShowRoute = \Illuminate\Support\Facades\Route::has('dashboard.orders.show')
                                ? route('dashboard.orders.show', $order)
                                : null;
                        }
                    @endphp

                    @if ($orderShowRoute)
                        <a href="{{ $orderShowRoute }}"
                            class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800">
                            طلب #{{ $order->id }}
                        </a>
                    @else
                        <div class="px-4 py-2 text-sm">طلب #{{ $order->id }}</div>
                    @endif
                @endforeach
            @endif

            {{-- Companies (Admin فقط) --}}
            @if ($companies->count())
                <div
                    class="px-4 py-2 text-xs font-bold text-slate-500 border-t border-slate-200/70 dark:border-slate-800">
                    الشركات
                </div>

                @foreach ($companies as $company)
                    @php
                        $companyShowRoute = \Illuminate\Support\Facades\Route::has('admin.companies.show')
                            ? route('admin.companies.show', $company)
                            : null;
                    @endphp

                    @if ($companyShowRoute)
                        <a href="{{ $companyShowRoute }}"
                            class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800">
                            {{ $company->company_name }}
                        </a>
                    @else
                        <div class="px-4 py-2 text-sm">{{ $company->company_name }}</div>
                    @endif
                @endforeach
            @endif

            @if (!$orders->count() && !$companies->count())
                <div class="px-4 py-3 text-sm text-slate-500">لا توجد نتائج</div>
            @endif
        </div>
    @endif
</div>
