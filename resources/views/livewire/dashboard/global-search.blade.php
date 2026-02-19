<div class="relative hidden sm:block">
    <div class="flex items-center gap-2 px-3 py-2 min-h-[44px] rounded-xl border border-slate-200 dark:border-slate-800">
        <i class="fa-solid fa-magnifying-glass text-slate-500 dark:text-slate-400 shrink-0"></i>

        <input type="text" wire:model.live.debounce.400ms="q"
            class="bg-transparent outline-none placeholder:text-slate-400 text-sm w-40 sm:w-56 min-w-0"
            placeholder="{{ __('dashboard.search_placeholder') }}" />
    </div>

    @if (mb_strlen($q) >= 2)
        <div
            class="absolute end-0 mt-2 w-[360px] max-w-[92vw] rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft z-50 overflow-hidden">

            {{-- Orders --}}
            @if ($orders->count())
                <div class="px-4 py-2 text-xs font-bold text-slate-500">{{ __('dashboard.orders') }}</div>

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
                        <a href="{{ $orderShowRoute }}" wire:navigate
                            class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800">
                            {{ __('dashboard.order') }} #{{ $order->id }}
                        </a>
                    @else
                        <div class="px-4 py-2 text-sm">{{ __('dashboard.order') }} #{{ $order->id }}</div>
                    @endif
                @endforeach
            @endif

            {{-- Companies (Admin فقط) --}}
            @if ($companies->count())
                <div
                    class="px-4 py-2 text-xs font-bold text-slate-500 border-t border-slate-200/70 dark:border-slate-800">
                    {{ __('dashboard.companies') }}
                </div>

                @foreach ($companies as $company)
                    @php
                        $companyShowRoute = \Illuminate\Support\Facades\Route::has('admin.customers.edit')
                            ? route('admin.customers.edit', $company)
                            : null;
                    @endphp

                    @if ($companyShowRoute)
                        <a href="{{ $companyShowRoute }}" wire:navigate
                            class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800">
                            {{ $company->company_name }}
                        </a>
                    @else
                        <div class="px-4 py-2 text-sm">{{ $company->company_name }}</div>
                    @endif
                @endforeach
            @endif

            @if (!$orders->count() && !$companies->count())
                <div class="px-4 py-3 text-sm text-slate-500">{{ __('dashboard.no_results') }}</div>
            @endif
        </div>
    @endif
</div>
