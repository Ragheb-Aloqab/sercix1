@extends('admin.layouts.app')

@section('page_title', __('invoice.invoices_page_title'))
@section('subtitle', __('invoice.invoices_subtitle'))

@section('content')
@include('company.partials.glass-start', ['title' => __('invoice.invoices_page_title')])
    <div class="space-y-6">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4">
                <p class="text-amber-700 dark:text-amber-400 text-sm font-bold">{{ __('invoice.summary_fuel_total') }}</p>
                <p class="text-2xl font-black mt-1 text-amber-700 dark:text-amber-300">{{ number_format($summary['fuel_total'], 2) }} {{ __('company.sar') }}</p>
                <p class="text-xs text-amber-600/80 dark:text-amber-400/80 mt-0.5">{{ __('invoice.summary_count', ['count' => $summary['fuel_count']]) }}</p>
            </div>
            <div class="rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4">
                <p class="text-amber-700 dark:text-amber-400 text-sm font-bold">{{ __('invoice.summary_fuel_avg') }}</p>
                <p class="text-2xl font-black mt-1 text-amber-700 dark:text-amber-300">{{ number_format($summary['fuel_avg'], 2) }} {{ __('company.sar') }}</p>
            </div>
            <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4">
                <p class="text-emerald-700 dark:text-emerald-400 text-sm font-bold">{{ __('invoice.summary_service_total') }}</p>
                <p class="text-2xl font-black mt-1 text-emerald-700 dark:text-emerald-300">{{ number_format($summary['service_total'], 2) }} {{ __('company.sar') }}</p>
                <p class="text-xs text-emerald-600/80 dark:text-emerald-400/80 mt-0.5">{{ __('invoice.summary_count', ['count' => $summary['service_count']]) }}</p>
            </div>
            <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 p-4">
                <p class="text-emerald-700 dark:text-emerald-400 text-sm font-bold">{{ __('invoice.summary_service_avg') }}</p>
                <p class="text-2xl font-black mt-1 text-emerald-700 dark:text-emerald-300">{{ number_format($summary['service_avg'], 2) }} {{ __('company.sar') }}</p>
            </div>
        </div>

        <form method="GET" class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">{{ __('invoice.filter_from_date') }}</label>
                    <input type="date" name="from" value="{{ $from?->format('Y-m-d') ?? request('from') }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-500/50 bg-slate-800/40 text-white min-h-[44px]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">{{ __('invoice.filter_to_date') }}</label>
                    <input type="date" name="to" value="{{ $to?->format('Y-m-d') ?? request('to') }}"
                        class="w-full px-4 py-3 rounded-xl border border-slate-500/50 bg-slate-800/40 text-white min-h-[44px]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">{{ __('common.search') }}</label>
                    <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="{{ __('invoice.invoice_number_label') }}..."
                        class="w-full px-4 py-3 rounded-xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500 min-h-[44px]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">{{ __('invoice.filter_by_plate') }}</label>
                    <select name="vehicle_id" class="w-full px-4 py-3 rounded-xl border border-slate-500/50 bg-slate-800/40 text-white min-h-[44px]">
                        <option value="">{{ __('fuel.all_vehicles') }}</option>
                        @foreach ($vehicles ?? [] as $v)
                            <option value="{{ $v->id }}" @selected(($vehicleId ?? 0) == $v->id)>{{ $v->plate_number }} — {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 mb-1">{{ __('invoice.service') }}</label>
                    <select name="invoice_type" class="w-full px-4 py-3 rounded-xl border border-slate-500/50 bg-slate-800/40 text-white min-h-[44px]">
                        <option value="">{{ __('invoice.all_types') }}</option>
                        <option value="service" @selected(($invoiceType ?? '') === 'service')>{{ __('invoice.service_invoice') }}</option>
                        <option value="fuel" @selected(($invoiceType ?? '') === 'fuel')>{{ __('invoice.fuel_invoice') }}</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold transition-colors">
                        <i class="fa-solid fa-filter me-2"></i>{{ __('common.search') }}
                    </button>
                </div>
            </div>
        </form>

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-red-500/20 text-red-300 border border-red-400/50 mb-6">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[500px]">
                    <thead class="border-b border-slate-600/50">
                        <tr class="text-slate-400">
                            <th class="p-4 text-end font-bold">{{ __('invoice.invoice_number_label') }}</th>
                            <th class="p-4 text-end font-bold">{{ __('invoice.driver_name') }}</th>
                            <th class="p-4 text-end font-bold">{{ __('invoice.date_label') }}</th>
                            <th class="p-4 text-end font-bold">{{ __('invoice.total') }}</th>
                            <th class="p-4 text-end font-bold">{{ __('common.actions') ?? 'إجراء' }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-600/50">
                        @forelse($invoices as $invoice)
                            <tr class="hover:bg-slate-700/30 transition-colors">
                                <td class="p-4 font-bold text-white text-end">{{ $invoice->invoice_number ?? '#' . $invoice->id }}</td>
                                <td class="p-4 text-white text-end">{{ $invoice->driver_name ?? '-' }}</td>
                                <td class="p-4 text-slate-400 text-end">{{ optional($invoice->created_at)->format('Y-m-d') }}</td>
                                <td class="p-4 font-semibold text-white text-end">{{ number_format((float) ($invoice->total ?? 0), 2) }} {{ __('company.sar') }}</td>
                                <td class="p-3 sm:p-4 text-end">
                                    <div class="flex flex-wrap gap-2 justify-end">
                                        <a href="{{ route('company.invoices.show', $invoice) }}"
                                            class="inline-flex items-center justify-center gap-1 px-3 py-2 min-h-[44px] rounded-xl border border-slate-500/50 font-semibold text-white hover:bg-slate-700/50 transition-colors">
                                            <i class="fa-solid fa-eye shrink-0"></i><span>{{ __('invoice.view_details') }}</span>
                                        </a>
                                        <a href="{{ route('company.invoices.pdf', $invoice) }}"
                                            download="invoice-{{ $invoice->invoice_number ?? $invoice->id }}.pdf"
                                            class="inline-flex items-center justify-center gap-1 px-3 py-2 min-h-[44px] rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold transition-colors">
                                            <i class="fa-solid fa-file-pdf shrink-0"></i><span>{{ __('invoice.download_invoice') }}</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-slate-500">{{ __('invoice.no_invoices') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $invoices->links() }}
        </div>

    </div>
@include('company.partials.glass-end')
@endsection
