<div class="space-y-6">
    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <x-summary-card
            :label="__('invoice.summary_fuel_total')"
            :value="number_format($summary['fuel_total'], 2) . ' ' . __('company.sar')"
            :subtext="__('invoice.summary_count', ['count' => $summary['fuel_count']])"
            variant="amber"
        />
        <x-summary-card
            :label="__('invoice.summary_fuel_avg')"
            :value="number_format($summary['fuel_avg'], 2) . ' ' . __('company.sar')"
            variant="amber"
        />
        <x-summary-card
            :label="__('invoice.summary_service_total')"
            :value="number_format($summary['service_total'], 2) . ' ' . __('company.sar')"
            :subtext="__('invoice.summary_count', ['count' => $summary['service_count']])"
            variant="emerald"
        />
        <x-summary-card
            :label="__('invoice.summary_service_avg')"
            :value="number_format($summary['service_avg'], 2) . ' ' . __('company.sar')"
            variant="emerald"
        />
        <x-summary-card
            :label="__('maintenance.invoice_archive') ?? 'Maintenance'"
            :value="number_format($maintenanceSummary['total'] ?? 0, 2) . ' ' . __('company.sar')"
            :subtext="__('invoice.summary_count', ['count' => $maintenanceSummary['count'] ?? 0])"
            variant="sky"
        />
    </div>

    {{-- Filter form (Livewire wire:model for reactive filtering) --}}
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('invoice.filter_from_date') }}</label>
                <input type="date" wire:model.live="from"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-500/50 bg-white dark:bg-slate-800/40 text-slate-900 dark:text-white min-h-[44px] transition-colors duration-300">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('invoice.filter_to_date') }}</label>
                <input type="date" wire:model.live="to"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-500/50 bg-white dark:bg-slate-800/40 text-slate-900 dark:text-white min-h-[44px] transition-colors duration-300">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('common.search') }}</label>
                <input type="text" wire:model.live.debounce.300ms="q" placeholder="{{ __('invoice.invoice_number_label') }}..."
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-500/50 bg-white dark:bg-slate-800/40 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 min-h-[44px] transition-colors duration-300">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('invoice.filter_by_plate') }}</label>
                <select wire:model.live="vehicleId" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-500/50 bg-white dark:bg-slate-800/40 text-slate-900 dark:text-white min-h-[44px] transition-colors duration-300">
                    <option value="">{{ __('fuel.all_vehicles') }}</option>
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->plate_number }} — {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">{{ __('invoice.service') }}</label>
                <select wire:model.live="invoiceType" class="w-full px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-500/50 bg-white dark:bg-slate-800/40 text-slate-900 dark:text-white min-h-[44px] transition-colors duration-300">
                    <option value="">{{ __('invoice.all_types') }}</option>
                    <option value="service">{{ __('invoice.service_invoice') }}</option>
                    <option value="fuel">{{ __('invoice.fuel_invoice') }}</option>
                    <option value="maintenance">{{ __('maintenance.invoice_archive') ?? 'Maintenance Invoices' }}</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Add Invoice buttons --}}
    <div class="flex flex-wrap gap-3 mb-6">
        @if($invoiceType === 'fuel' || $invoiceType === '')
            <livewire:company.fuel-invoice-upload-section />
        @endif
        @if($invoiceType === 'maintenance' || $invoiceType === '')
            <a href="{{ route('company.maintenance-invoices.index') }}"
                class="shrink-0 px-5 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors inline-flex items-center gap-2">
                <i class="fa-solid fa-wrench"></i>
                {{ __('invoice.add_maintenance_invoice') }}
            </a>
        @endif
    </div>

    <x-company.flash />

    {{-- Company-uploaded fuel invoices --}}
    @if(($invoiceType === 'fuel' || $invoiceType === '') && $companyFuelInvoices->isNotEmpty())
        @include('company.invoices.partials.company-fuel-invoices-table', ['companyFuelInvoices' => $companyFuelInvoices])
    @endif

    {{-- Main invoices table --}}
    @include('company.invoices.partials.invoices-table', [
        'invoiceType' => $invoiceType,
        'invoices' => $invoices,
        'maintenanceInvoices' => $maintenanceInvoices,
        'companyMaintenanceInvoices' => $companyMaintenanceInvoices,
    ])

    <div class="mt-6">
        @if($invoiceType === 'maintenance')
            {{ $maintenanceInvoices->links() }}
        @else
            {{ $invoices->links() }}
        @endif
    </div>

    {{-- Maintenance invoices section when viewing all types --}}
    @if($invoiceType === '' && $maintenanceInvoices->isNotEmpty())
        <h3 class="text-lg font-bold text-slate-700 dark:text-slate-300 mt-8 mb-4">{{ __('maintenance.invoice_archive') ?? 'Maintenance Invoices' }}</h3>
        @include('company.invoices.partials.maintenance-invoices-table', ['maintenanceInvoices' => $maintenanceInvoices])
        <div class="mt-4">{{ $maintenanceInvoices->links() }}</div>
    @endif

    {{-- Image preview modal --}}
    @include('company.invoices.partials.image-preview-modal')
</div>
