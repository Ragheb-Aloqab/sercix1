<div class="relative hidden sm:block">
    <div class="flex items-center gap-2 px-3 py-2 min-h-[44px] rounded-xl {{ auth('company')->check() ? 'border border-slate-500/50' : 'border border-slate-200 dark:border-slate-800' }}">
        <i class="fa-solid fa-magnifying-glass shrink-0 {{ auth('company')->check() ? 'text-slate-500' : 'text-slate-500 dark:text-slate-400' }}"></i>

        <input type="text" wire:model.live.debounce.400ms="q"
            class="bg-transparent outline-none text-sm w-40 sm:w-56 min-w-0 {{ auth('company')->check() ? 'placeholder:text-slate-500 text-slate-200' : 'placeholder:text-slate-400' }}"
            placeholder="{{ __('dashboard.search_placeholder') }}" />
    </div>

    @if (mb_strlen($q) >= 2)
        <div
            class="absolute end-0 mt-2 w-[360px] max-w-[92vw] rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft z-50 overflow-hidden">

            {{-- Orders --}}
            @if ($ordersWithRoutes->count())
                <div class="px-4 py-2 text-xs font-bold text-slate-500">{{ __('dashboard.orders') }}</div>

                @foreach ($ordersWithRoutes as $row)
                    @if ($row->orderShowRoute)
                        <a href="{{ $row->orderShowRoute }}" wire:navigate
                            class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800">
                            {{ __('dashboard.order') }} #{{ $row->order->id }}
                        </a>
                    @else
                        <div class="px-4 py-2 text-sm">{{ __('dashboard.order') }} #{{ $row->order->id }}</div>
                    @endif
                @endforeach
            @endif

            {{-- Companies (Admin فقط) --}}
            @if ($companiesWithRoutes->count())
                <div
                    class="px-4 py-2 text-xs font-bold text-slate-500 border-t border-slate-200/70 dark:border-slate-800">
                    {{ __('dashboard.companies') }}
                </div>

                @foreach ($companiesWithRoutes as $row)
                    @if ($row->companyShowRoute)
                        <a href="{{ $row->companyShowRoute }}" wire:navigate
                            class="block px-4 py-2 text-sm hover:bg-slate-100 dark:hover:bg-slate-800">
                            {{ $row->company->company_name }}
                        </a>
                    @else
                        <div class="px-4 py-2 text-sm">{{ $row->company->company_name }}</div>
                    @endif
                @endforeach
            @endif

            @if (!$ordersWithRoutes->count() && !$companiesWithRoutes->count())
                <div class="px-4 py-3 text-sm text-slate-500">{{ __('dashboard.no_results') }}</div>
            @endif
        </div>
    @endif
</div>
