<div>
    <a href="{{ route('company.reports.index') }}" class="inline-flex items-center gap-2 text-slate-600 dark:text-servx-silver hover:text-slate-900 dark:hover:text-white mb-6 transition-colors duration-300">
        <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i>
        {{ __('reports.back_to_reports') }}
    </a>

    {{-- Filters --}}
    <div class="dash-card mb-6">
        <h3 class="dash-section-title mb-4">{{ __('reports.filters') ?? 'Filters' }}</h3>
        <form wire:submit.prevent class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('reports.from_date') ?? 'From' }}</label>
                <input type="date" wire:model.live.debounce.300ms="from" class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('reports.to_date') ?? 'To' }}</label>
                <input type="date" wire:model.live.debounce.300ms="to" class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('driver.vehicle') }}</label>
                <select wire:model.live="vehicleId" class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">
                    <option value="">{{ __('fuel.all_vehicles') }}</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->plate_number }} — {{ trim(($v->name ?? '') . ' ' . ($v->make ?? '') . ' ' . ($v->model ?? '')) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('fleet.branch') ?? 'Branch' }}</label>
                <select wire:model.live="branchId" class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">
                    <option value="">{{ __('common.all') ?? 'All' }}</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="dash-card dash-card-kpi p-4">
            <p class="dash-card-title">{{ __('vehicles.total_vehicles') ?? 'Total Vehicles' }}</p>
            <p class="dash-card-value">{{ $summary['total_vehicles'] ?? 0 }}</p>
        </div>
        <div class="dash-card dash-card-kpi p-4">
            <p class="dash-card-title">{{ __('vehicles.total_mileage_this_period') ?? 'Total Mileage (Period)' }}</p>
            <p class="dash-card-value">{{ number_format($summary['total_mileage_this_period'] ?? 0, 1) }} {{ __('common.km') ?? 'km' }}</p>
        </div>
        <div class="dash-card dash-card-kpi p-4">
            <p class="dash-card-title">{{ __('vehicles.avg_mileage_per_vehicle') ?? 'Avg per Vehicle' }}</p>
            <p class="dash-card-value">{{ number_format($summary['average_mileage_per_vehicle'] ?? 0, 1) }} {{ __('common.km') ?? 'km' }}</p>
        </div>
    </div>

    {{-- Export buttons --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ route('company.reports.mileage.pdf', ['from' => $from, 'to' => $to, 'vehicle_id' => $vehicleId ?: null, 'branch_id' => $branchId ?: null]) }}"
           class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 dark:hover:bg-red-500 text-white font-bold inline-flex items-center gap-2 transition-colors duration-300">
            <i class="fa-solid fa-file-pdf"></i>
            {{ __('fleet.export_pdf') }}
        </a>
        <a href="{{ route('company.reports.mileage.excel', ['from' => $from, 'to' => $to, 'vehicle_id' => $vehicleId ?: null, 'branch_id' => $branchId ?: null]) }}"
           class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold inline-flex items-center gap-2">
            <i class="fa-solid fa-file-excel"></i>
            {{ __('fleet.export_excel') }}
        </a>
        <a href="{{ route('company.reports.mileage.pdf', ['from' => $from, 'to' => $to, 'vehicle_id' => $vehicleId ?: null, 'branch_id' => $branchId ?: null, 'queue' => 1]) }}"
           class="px-4 py-2 rounded-xl border border-slate-500/50 hover:bg-slate-700/50 text-servx-silver font-bold inline-flex items-center gap-2"
           title="{{ __('reports.queued_for_generation') }}">
            <i class="fa-solid fa-clock"></i>
            {{ __('reports.generate_in_background') ?? 'Generate PDF in background' }}
        </a>
        <a href="{{ route('company.reports.mileage.excel', ['from' => $from, 'to' => $to, 'vehicle_id' => $vehicleId ?: null, 'branch_id' => $branchId ?: null, 'queue' => 1]) }}"
           class="px-4 py-2 rounded-xl border border-slate-500/50 hover:bg-slate-700/50 text-servx-silver font-bold inline-flex items-center gap-2"
           title="{{ __('reports.queued_for_generation') }}">
            <i class="fa-solid fa-clock"></i>
            {{ __('reports.generate_excel_in_background') ?? 'Generate Excel in background' }}
        </a>
    </div>

    {{-- Table --}}
    <div class="dash-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-start text-servx-silver text-sm border-b border-slate-600/50">
                        <th class="pb-3 pe-4">
                            <button type="button" wire:click="sort('plate_number')" class="font-bold hover:text-slate-900 dark:hover:text-white transition-colors duration-300">
                                {{ __('fleet.plate_number') }}
                                @if($sortBy === 'plate_number')<i class="fa-solid fa-sort-{{ $sortDir === 'asc' ? 'up' : 'down' }} ms-1"></i>@endif
                            </button>
                        </th>
                        <th class="pb-3 pe-4">
                            <button type="button" wire:click="sort('vehicle_name')" class="font-bold hover:text-slate-900 dark:hover:text-white transition-colors duration-300">
                                {{ __('fleet.vehicle_name') }}
                                @if($sortBy === 'vehicle_name')<i class="fa-solid fa-sort-{{ $sortDir === 'asc' ? 'up' : 'down' }} ms-1"></i>@endif
                            </button>
                        </th>
                        <th class="pb-3 pe-4">{{ __('fleet.branch') ?? 'Branch' }}</th>
                        <th class="pb-3 pe-4">
                            <button type="button" wire:click="sort('current_mileage')" class="font-bold hover:text-slate-900 dark:hover:text-white transition-colors duration-300">
                                {{ __('vehicles.current_mileage') ?? 'Current' }}
                                @if($sortBy === 'current_mileage')<i class="fa-solid fa-sort-{{ $sortDir === 'asc' ? 'up' : 'down' }} ms-1"></i>@endif
                            </button>
                        </th>
                        <th class="pb-3 pe-4">
                            <button type="button" wire:click="sort('previous_mileage')" class="font-bold hover:text-slate-900 dark:hover:text-white transition-colors duration-300">
                                {{ __('vehicles.previous_mileage') ?? 'Previous' }}
                                @if($sortBy === 'previous_mileage')<i class="fa-solid fa-sort-{{ $sortDir === 'asc' ? 'up' : 'down' }} ms-1"></i>@endif
                            </button>
                        </th>
                        <th class="pb-3 pe-4">
                            <button type="button" wire:click="sort('total_distance')" class="font-bold hover:text-slate-900 dark:hover:text-white transition-colors duration-300">
                                {{ __('vehicles.total_distance') ?? 'Distance' }}
                                @if($sortBy === 'total_distance')<i class="fa-solid fa-sort-{{ $sortDir === 'asc' ? 'up' : 'down' }} ms-1"></i>@endif
                            </button>
                        </th>
                        <th class="pb-3 pe-4">{{ __('vehicles.last_update_date') ?? 'Last Update' }}</th>
                        <th class="pb-3 pe-4">{{ __('vehicles.status') ?? 'Status' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr class="border-b border-slate-200 dark:border-slate-600/30 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors duration-300">
                            <td class="py-4 pe-4 font-semibold text-slate-900 dark:text-white">{{ $row['plate_number'] }}</td>
                            <td class="py-4 pe-4 text-slate-600 dark:text-servx-silver-light">{{ $row['vehicle_name'] }}</td>
                            <td class="py-4 pe-4 text-slate-500 dark:text-servx-silver">{{ $row['branch_name'] }}</td>
                            <td class="py-4 pe-4 text-slate-900 dark:text-white">{{ number_format($row['current_mileage'], 1) }}</td>
                            <td class="py-4 pe-4 text-slate-500 dark:text-servx-silver">{{ number_format($row['previous_mileage'], 1) }}</td>
                            <td class="py-4 pe-4 font-bold {{ ($row['has_anomaly'] ?? false) ? 'text-amber-500 dark:text-amber-400' : 'text-sky-600 dark:text-sky-400' }}" title="{{ ($row['has_anomaly'] ?? false) ? __('vehicles.mileage_anomaly_tooltip') : '' }}">
                                @if($row['has_anomaly'] ?? false)
                                    — <i class="fa-solid fa-triangle-exclamation text-xs ms-0.5"></i>
                                @else
                                    {{ number_format($row['total_distance'], 1) }} {{ __('common.km') ?? 'km' }}
                                @endif
                            </td>
                            <td class="py-4 pe-4 text-slate-500 dark:text-servx-silver">{{ $row['last_update_date'] }}</td>
                            <td class="py-4 pe-4">
                                @php
                                    $statusClass = match($row['status']) {
                                        'normal' => 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border-emerald-400/50',
                                        'high_usage' => 'bg-amber-500/20 text-amber-800 dark:text-amber-300 border-amber-400/50',
                                        'no_update' => 'bg-red-500/20 text-red-700 dark:text-red-300 border-red-400/50',
                                        'data_anomaly' => 'bg-amber-500/20 text-amber-800 dark:text-amber-300 border-amber-400/50',
                                        default => 'bg-slate-500/20 text-slate-600 dark:text-slate-400 border-slate-400/50',
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-lg text-xs font-bold border {{ $statusClass }}">
                                    {{ __("vehicles.status_{$row['status']}") }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500 dark:text-servx-silver">{{ __('vehicles.no_mileage_data') ?? 'No mileage data for the selected period.' }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
