@extends('admin.layouts.app')

@section('page_title', 'الفواتير')
@section('subtitle', 'فواتير الشركة ')

@section('content')
    <div class="space-y-6">

        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">{{ __('common.search') }}</label>
                <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="{{ __('invoice.invoice_number_label') }}..."
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent min-h-[44px]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">{{ __('invoice.filter_by_plate') }}</label>
                <select name="vehicle_id" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent min-h-[44px]">
                    <option value="">{{ __('fuel.all_vehicles') }}</option>
                    @foreach ($vehicles ?? [] as $v)
                        <option value="{{ $v->id }}" @selected(($vehicleId ?? 0) == $v->id)>{{ $v->plate_number }} — {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">{{ __('invoice.service') }}</label>
                <select name="invoice_type" class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-transparent min-h-[44px]">
                    <option value="">{{ __('invoice.all_types') }}</option>
                    <option value="service" @selected(($invoiceType ?? '') === 'service')>{{ __('invoice.service_invoice') }}</option>
                    <option value="fuel" @selected(($invoiceType ?? '') === 'fuel')>{{ __('invoice.fuel_invoice') }}</option>
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-1">
                <button type="submit" class="w-full px-4 py-3 min-h-[44px] rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-semibold">{{ __('common.search') }}</button>
            </div>
        </form>

        @if (session('error'))
            <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-800 font-semibold text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div
            class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
            <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                <table class="w-full text-sm min-w-[500px]">
                    <thead class="bg-slate-100 dark:bg-slate-800">
                        <tr>
                            <th class="p-4 text-start">{{ __('invoice.invoice_number_label') }}</th>
                            <th class="p-4 text-start">{{ __('invoice.driver_name') }}</th>
                            <th class="p-4 text-start">{{ __('invoice.date_label') }}</th>
                            <th class="p-4 text-start">{{ __('invoice.total') }}</th>
                            <th class="p-4 text-start">{{ __('common.actions') ?? 'إجراء' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr class="border-t border-slate-200 dark:border-slate-800">
                                <td class="p-4 font-bold">{{ $invoice->invoice_number ?? '#' . $invoice->id }}</td>
                                <td class="p-4">{{ $invoice->driver_name ?? '-' }}</td>
                                <td class="p-4 text-slate-500">{{ optional($invoice->created_at)->format('Y-m-d') }}</td>
                                <td class="p-4 font-semibold">{{ number_format((float) ($invoice->total ?? 0), 2) }} SAR</td>
                                <td class="p-4">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('company.invoices.show', $invoice) }}"
                                            class="inline-flex items-center gap-1 px-3 py-2 min-h-[40px] rounded-xl border border-slate-200 dark:border-slate-800 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800">
                                            <i class="fa-solid fa-eye"></i>{{ __('invoice.view_details') }}
                                        </a>
                                        <a href="{{ route('company.invoices.pdf', $invoice) }}"
                                            download="invoice-{{ $invoice->invoice_number ?? $invoice->id }}.pdf"
                                            class="inline-flex items-center gap-1 px-3 py-2 min-h-[40px] rounded-xl border border-slate-200 dark:border-slate-800 font-semibold hover:bg-slate-50 dark:hover:bg-slate-800">
                                            <i class="fa-solid fa-file-pdf"></i>{{ __('invoice.download_invoice') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-slate-500"> لا يوجد فواتير </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $invoices->links() }}
        </div>

    </div>
@endsection
