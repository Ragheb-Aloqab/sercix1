<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-7xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="text-center sm:text-start w-full sm:w-auto">
                <h1 class="dash-page-title">{{ __('admin_dashboard.vehicles_overview') }}</h1>
                <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.vehicles.expiring-documents') }}" class="dash-btn {{ $expiringDocumentsCount > 0 ? 'bg-amber-500/20 text-amber-400 border-amber-500/30 hover:bg-amber-500/30' : 'dash-btn-secondary' }}">
                    <i class="fa-solid fa-file-circle-exclamation"></i>{{ __('vehicles.expiring_documents') }}@if($expiringDocumentsCount > 0) ({{ $expiringDocumentsCount }})@endif
                </a>
                <a href="{{ route('admin.dashboard') }}" class="dash-btn dash-btn-secondary">
                    <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="dash-card">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1 relative">
                    <i class="fa-solid fa-search absolute start-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="{{ __('admin_dashboard.search_vehicles') }}"
                           class="w-full ps-10 pe-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600/50 text-white placeholder-slate-400 focus:border-sky-500/50 focus:ring-1 focus:ring-sky-500/50 transition-colors">
                </div>
                <select wire:model.live="companyId"
                        class="px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600/50 text-white focus:border-sky-500/50">
                    <option value="">{{ __('admin_dashboard.filter_by_company') }}</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="statusFilter"
                        class="px-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600/50 text-white focus:border-sky-500/50">
                    <option value="all">{{ __('admin_dashboard.filter_by_status') }}: {{ __('common.all') }}</option>
                    <option value="active">{{ __('admin_dashboard.active') }}</option>
                    <option value="inactive">{{ __('admin_dashboard.inactive') }}</option>
                </select>
            </div>
        </div>

        {{-- Vehicles Table --}}
        <div class="dash-card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-800/50 border-b border-slate-700">
                        <tr>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('dashboard.companies') }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('vehicles.vehicle') ?? 'Vehicle' }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('vehicles.plate_number') ?? 'Plate' }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('vehicles.driver_name') ?? 'Driver' }}</th>
                            <th class="p-4 text-start font-semibold text-slate-300">{{ __('admin_dashboard.activity_status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($vehicles as $v)
                            <tr class="hover:bg-slate-800/30 transition-colors">
                                <td class="p-4">
                                    <a href="{{ route('admin.companies.show', $v->company) }}" class="text-sky-400 hover:text-sky-300 font-medium">
                                        {{ $v->company?->company_name ?? '-' }}
                                    </a>
                                </td>
                                <td class="p-4 text-white font-medium">{{ $v->display_name }}</td>
                                <td class="p-4 text-slate-300">{{ $v->plate_number ?? '-' }}</td>
                                <td class="p-4 text-slate-300">{{ $v->driver_name ?? '-' }}</td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $v->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-600/50 text-slate-400' }}">
                                        {{ $v->is_active ? __('admin_dashboard.active') : __('admin_dashboard.inactive') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">
                                    {{ __('admin_dashboard.no_activity') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($vehicles->hasPages())
                <div class="p-4 border-t border-slate-700">
                    {{ $vehicles->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
