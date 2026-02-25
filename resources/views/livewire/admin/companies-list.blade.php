<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-7xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-center sm:text-start w-full sm:w-auto">
                <h1 class="dash-page-title">{{ __('admin_dashboard.companies_overview') }}</h1>
                <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="dash-btn dash-btn-secondary">
                <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
            </a>
        </div>

        {{-- Search --}}
        <div class="dash-card">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 relative">
                    <i class="fa-solid fa-search absolute start-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="{{ __('admin_dashboard.filter_by_company') }}"
                           class="w-full ps-10 pe-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600/50 text-white placeholder-slate-400 focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/50 transition-colors">
                </div>
            </div>
        </div>

        {{-- Companies Table (responsive: cards on mobile) --}}
        <div class="dash-card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-800/50 border-b border-slate-700">
                        <tr>
                            <th class="p-4 text-start font-semibold text-slate-300">
                                <button wire:click="sortBy('company_name')" class="flex items-center gap-1 hover:text-white transition-colors">
                                    {{ __('dashboard.companies') }}
                                    @if($sortField === 'company_name')
                                        <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('admin_dashboard.vehicles_count') }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('admin_dashboard.drivers_count') }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('admin_dashboard.orders_count') }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('admin_dashboard.subscription_status') }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">
                                <button wire:click="sortBy('created_at')" class="flex items-center gap-1 hover:text-white transition-colors">
                                    {{ __('admin_dashboard.created_date') }}
                                    @if($sortField === 'created_at')
                                        <i class="fa-solid fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-xs"></i>
                                    @endif
                                </button>
                            </th>
                            <th class="p-4 text-end font-semibold text-slate-300">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($companies as $company)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="p-4">
                                    <div>
                                        <p class="font-semibold text-white">{{ $company->company_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $company->email ?? '-' }}</p>
                                    </div>
                                </td>
                                <td class="p-4 text-slate-300">{{ $company->vehicles_count ?? 0 }}</td>
                                <td class="p-4 text-slate-300">{{ $company->drivers_count ?? 0 }}</td>
                                <td class="p-4 text-slate-300">{{ $company->orders_count ?? 0 }}</td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-slate-700/50 text-slate-300">
                                        {{ __('admin_dashboard.subscription_n_a') }}
                                    </span>
                                </td>
                                <td class="p-4 text-slate-400 text-xs">{{ $company->created_at?->format('Y-m-d') ?? '-' }}</td>
                                <td class="p-4 text-end">
                                    <a href="{{ route('admin.companies.show', $company) }}" class="dash-btn dash-btn-primary !py-2 !px-3 text-sm">
                                        <i class="fa-solid fa-eye"></i>{{ __('admin_dashboard.view_details') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-400">
                                    {{ __('admin_dashboard.no_companies') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($companies->hasPages())
                <div class="p-4 border-t border-slate-700">
                    {{ $companies->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
