<div>
    {{-- Header actions --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-6 sm:mb-8">
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto flex-wrap">
            <input type="text" wire:model.live.debounce.300ms="q" placeholder="{{ __('vehicles.search_placeholder') }}"
                class="w-full lg:w-80 px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-500/50 bg-white dark:bg-slate-800/40 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 transition-colors duration-300" />
            <select wire:model.live="status" class="px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-500/50 bg-white dark:bg-slate-800/40 text-slate-900 dark:text-white min-h-[44px] transition-colors duration-300">
                <option value="">{{ __('vehicles.all_statuses') ?? 'All' }}</option>
                <option value="active">{{ __('vehicles.active') }}</option>
                <option value="inactive">{{ __('vehicles.inactive') }}</option>
            </select>
            @if($branches->isNotEmpty())
                <select wire:model.live="branchId" class="px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-500/50 bg-white dark:bg-slate-800/40 text-slate-900 dark:text-white min-h-[44px] transition-colors duration-300">
                    <option value="">{{ __('dashboard.branches') ?? 'All Branches' }}</option>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            @endif
            @if($q || $status || $branchId)
                <button wire:click="$set('q', ''); $set('status', ''); $set('branchId', '')"
                    class="px-4 py-3 rounded-2xl border border-slate-300 dark:border-slate-500/50 text-slate-700 dark:text-white font-bold hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-300">
                    {{ __('vehicles.clear') }}
                </button>
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('company.vehicles.create') }}"
                class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 dark:hover:bg-emerald-500 text-white font-bold transition-colors duration-300">
                <i class="fa-solid fa-plus me-2"></i> {{ __('vehicles.add_vehicle') }}
            </a>
        </div>
    </div>

    {{-- Quota usage --}}
    @if($quotaUsage['quota'] && $quotaUsage['at_limit'])
        <x-company-alert type="warning">
            <p class="font-bold">{{ __('admin_dashboard.quota_limit_reached') }}</p>
            <p class="text-sm mt-1">{{ __('admin_dashboard.quota_usage') }}: {{ $quotaUsage['current'] }} / {{ $quotaUsage['quota'] }}</p>
            @if(!auth('company')->user()->hasPendingQuotaRequest())
                <a href="{{ route('company.vehicles.quota-request') }}" class="inline-block mt-2 px-4 py-2 rounded-xl bg-amber-500/30 hover:bg-amber-500/50 font-bold">
                    {{ __('admin_dashboard.quota_request') }}
                </a>
            @else
                <p class="text-sm mt-2 opacity-80">{{ __('admin_dashboard.quota_request_pending') }}</p>
            @endif
        </x-company-alert>
    @endif

    <x-company.flash />

    {{-- Table --}}
    <x-company.table>
        <x-slot name="header">
            <h2 class="text-base font-bold text-slate-700 dark:text-slate-300">{{ __('vehicles.vehicles_list') }}</h2>
            <p class="text-sm text-slate-500 dark:text-slate-500">{{ __('vehicles.total') }}: {{ $vehicles->total() }}</p>
        </x-slot>

        @if ($vehicles->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[720px]">
                    <thead>
                        <tr class="text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-600/50">
                            <th class="text-end py-3 px-2 font-bold">{{ __('fleet.plate_number') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('fleet.vehicle_name') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('fleet.model') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('vehicles.current_mileage') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('vehicles.previous_mileage') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('vehicles.total_distance') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('fleet.status') }}</th>
                            <th class="text-end py-3 px-2 font-bold">{{ __('fleet.assigned_driver') }}</th>
                            <th class="text-start py-3 px-2 font-bold">{{ __('vehicles.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-600/50">
                        @foreach ($vehicles as $v)
                            @php
                                $docStatus = $expiryService->getVehicleDocumentStatus($v);
                                $regStatus = $docStatus['registration']['status'];
                                $insStatus = $docStatus['insurance']['status'];
                                $hasWarning = in_array($regStatus, ['expiring_soon', 'expired']) || in_array($insStatus, ['expiring_soon', 'expired']);
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors duration-300 {{ $hasWarning ? 'bg-amber-500/5' : '' }}">
                                <td class="py-3 px-2 font-bold text-slate-900 dark:text-white text-end">
                                    <a href="{{ route('company.vehicles.show', $v) }}" class="text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 inline-flex items-center gap-1 transition-colors duration-300">
                                        @if ($hasWarning)
                                            <i class="fa-solid fa-triangle-exclamation text-amber-500 dark:text-amber-400 text-xs" title="{{ __('vehicles.expiring_soon') }}"></i>
                                        @endif
                                        {{ $v->plate_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-2 text-end">
                                    <a href="{{ route('company.vehicles.show', $v) }}" class="block hover:opacity-80 font-semibold text-slate-900 dark:text-white transition-colors duration-300">
                                        {{ $v->display_name }}
                                    </a>
                                </td>
                                <td class="py-3 px-2 text-end text-slate-600 dark:text-slate-400">
                                    {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: ($v->year ?? '—') }}
                                </td>
                                @php $ms = $mileageSummaries[$v->id] ?? ['current_mileage' => 0, 'previous_mileage' => null, 'total_distance' => 0]; @endphp
                                <td class="py-3 px-2 text-end text-slate-900 dark:text-white font-semibold">{{ number_format($ms['current_mileage'], 1) }}</td>
                                <td class="py-3 px-2 text-end text-slate-600 dark:text-slate-400">{{ $ms['previous_mileage'] !== null ? number_format($ms['previous_mileage'], 1) : '—' }}</td>
                                <td class="py-3 px-2 text-end text-emerald-600 dark:text-emerald-400 font-bold">{{ number_format($ms['total_distance'], 1) }} {{ __('common.km') }}</td>
                                <td class="py-3 px-2 text-end">
                                    @if ($v->is_active)
                                        <span class="px-2 py-1 rounded-xl bg-emerald-500/30 text-emerald-700 dark:text-emerald-300 border border-emerald-400/50 text-xs font-bold">
                                            {{ __('vehicles.active') }}
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-xl bg-slate-200 dark:bg-slate-600/30 text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-slate-500/50 text-xs font-bold">
                                            {{ __('vehicles.inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-end text-slate-600 dark:text-slate-400">
                                    {{ $v->driver_name ?? '—' }}
                                </td>
                                <td class="py-3 px-2">
                                    <div class="flex flex-wrap gap-2 justify-start">
                                        <a href="{{ route('company.vehicles.show', $v) }}"
                                            class="px-3 py-2 rounded-2xl bg-sky-600 hover:bg-sky-700 dark:hover:bg-sky-500 text-white font-bold inline-flex items-center gap-2 transition-colors duration-300">
                                            <i class="fa-solid fa-eye shrink-0"></i> {{ __('fleet.view') }}
                                        </a>
                                        <a href="{{ route('company.vehicles.edit', $v->id) }}"
                                            class="px-3 py-2 rounded-2xl border border-slate-300 dark:border-slate-500/50 text-slate-700 dark:text-white font-bold hover:bg-slate-100 dark:hover:bg-slate-700/50 inline-flex items-center gap-2 transition-colors duration-300">
                                            <i class="fa-solid fa-pen shrink-0"></i> {{ __('fleet.edit') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $vehicles->links() }}
            </div>
        @else
            <p class="text-slate-500 dark:text-slate-500 py-8 text-end">{{ __('vehicles.no_vehicles') }}</p>
        @endif
    </x-company.table>
</div>
