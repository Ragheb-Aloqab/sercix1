<div>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <a href="{{ route('company.reports.index') }}"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
            <i class="fa-solid fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i> {{ __('reports.back_to_reports') }}
        </a>
        <div class="flex flex-wrap gap-2">
            <x-export-dropdown
                :pdfUrl="route('company.reports.tax.pdf', ['from' => $dateFrom, 'to' => $dateTo, 'vehicle_id' => $vehicleId ?: null])"
                :excelUrl="route('company.reports.tax.excel', ['from' => $dateFrom, 'to' => $dateTo, 'vehicle_id' => $vehicleId ?: null])"
            />
        </div>
    </div>

    {{-- Filters (Livewire: update report without full page reload) --}}
    <div class="rounded-2xl bg-white dark:bg-slate-800/40 border border-slate-200 dark:border-slate-500/30 p-4 sm:p-5 backdrop-blur-sm mb-6 transition-colors duration-300">
        <h3 class="text-sm font-bold text-slate-600 dark:text-slate-400 mb-3">{{ __('reports.filters') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('reports.date_from') ?? 'From' }}</label>
                <input type="date" wire:model.live.debounce.300ms="from"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-white transition-colors duration-300">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('reports.date_to') ?? 'To' }}</label>
                <input type="date" wire:model.live.debounce.300ms="to"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-white transition-colors duration-300">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('company.vehicle') }}</label>
                <select wire:model.live="vehicleId"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 text-slate-900 dark:text-white px-4 py-2 transition-colors duration-300">
                    <option value="">{{ __('company.all_vehicles') }}</option>
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->plate_number }} — {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" wire:click="$refresh" class="w-full px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors duration-300">
                    <i class="fa-solid fa-filter me-2"></i>{{ __('company.apply_filter') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-6">
        <x-report-stat-card
            :label="__('reports.total_invoices')"
            :value="number_format($data['total_invoices'] ?? 0, 0)"
            icon="fa-file-invoice"
            icon-color="sky"
        />
        <x-report-stat-card
            :label="__('reports.total_vat_amount')"
            :value="number_format($data['total_vat_amount'] ?? 0, 2) . ' ' . __('company.sar')"
            icon="fa-percent"
            icon-color="amber"
        />
        <x-report-stat-card
            :label="__('reports.total_including_vat')"
            :value="number_format($data['total_including_vat'] ?? 0, 2) . ' ' . __('company.sar')"
            icon="fa-coins"
            icon-color="emerald"
        />
    </div>

    {{-- Invoices table --}}
    <div class="rounded-2xl bg-white dark:bg-slate-800/40 border border-slate-200 dark:border-slate-500/30 p-6 backdrop-blur-sm transition-colors duration-300">
        <h3 class="text-base font-bold text-slate-700 dark:text-slate-300 mb-4">{{ __('reports.invoice_details') }}</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-slate-600/50 text-slate-600 dark:text-slate-400">
                        <th class="text-start py-3 px-2 font-bold">{{ __('reports.date') ?? 'Date' }}</th>
                        <th class="text-start py-3 px-2 font-bold">{{ __('company.vehicle') }}</th>
                        <th class="text-start py-3 px-2 font-bold">{{ __('maintenance.services') }}</th>
                        <th class="text-end py-3 px-2 font-bold">{{ __('maintenance.invoice_amount') }}</th>
                        <th class="text-end py-3 px-2 font-bold">{{ __('maintenance.vat_amount') }}</th>
                        <th class="text-end py-3 px-2 font-bold">{{ __('maintenance.total_with_tax') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['invoices'] ?? [] as $inv)
                        <tr class="border-b border-slate-200 dark:border-slate-600/50">
                            <td class="py-3 px-2 text-slate-900 dark:text-white">{{ $inv->created_at?->translatedFormat('Y-m-d H:i') }}</td>
                            <td class="py-3 px-2 text-slate-600 dark:text-servx-silver">{{ $inv->vehicle ? ($inv->vehicle->display_name ?? ($inv->vehicle->plate_number . ' — ' . trim(($inv->vehicle->make ?? '') . ' ' . ($inv->vehicle->model ?? '')))) : '—' }}</td>
                            <td class="py-3 px-2 text-slate-600 dark:text-servx-silver text-sm">{{ $inv->services->isNotEmpty() ? $inv->services->pluck('name')->join(', ') : '—' }}</td>
                            <td class="py-3 px-2 text-end text-slate-900 dark:text-white">{{ number_format($inv->original_amount ?? $inv->amount ?? 0, 2) }} {{ __('company.sar') }}</td>
                            <td class="py-3 px-2 text-end text-amber-600 dark:text-amber-400">{{ number_format($inv->vat_amount ?? 0, 2) }} {{ __('company.sar') }}</td>
                            <td class="py-3 px-2 text-end font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($inv->amount ?? 0, 2) }} {{ __('company.sar') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500 dark:text-servx-silver">{{ __('reports.no_invoices_in_period') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
