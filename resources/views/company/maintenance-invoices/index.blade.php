@extends('admin.layouts.app')

@section('title', __('maintenance.invoice_archive') ?? 'Invoice Archive')
@section('page_title', __('maintenance.invoice_archive') ?? 'Invoice Archive')
@section('subtitle', __('maintenance.invoice_archive_desc') ?? 'Final invoices from maintenance centers')

@section('content')
@include('company.partials.glass-start', ['title' => __('fleet.maintenance_invoices')])

{{-- Summary --}}
<div class="dash-card dash-card-kpi mb-6">
    <p class="dash-card-title">{{ __('fleet.total_maintenance_cost') }}</p>
    <p class="dash-card-value">{{ number_format(($totalMaintenanceCost ?? 0) + ($totalCompanyInvoicesCost ?? 0), 2) }} {{ __('company.sar') }}</p>
        <p class="text-xs text-slate-500 dark:text-servx-silver mt-1">{{ __('maintenance.invoice_archive_desc') }}</p>
</div>

{{-- Company-uploaded invoices section with Upload button & modal (Livewire) --}}
<livewire:company.maintenance-invoices-section />

{{-- Invoices from maintenance centers --}}
<div class="dash-card">
    <h2 class="dash-section-title mb-4">{{ __('maintenance.invoice_archive') }} — {{ __('maintenance.center_name') }}</h2>
    @if($requests->isEmpty())
        <p class="text-slate-600 dark:text-servx-silver mb-4">{{ __('maintenance.no_invoices') ?? 'No invoices yet.' }}</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-start text-slate-600 dark:text-servx-silver text-sm border-b border-slate-200 dark:border-slate-600/50">
                        <th class="pb-3 pe-4">#</th>
                        <th class="pb-3 pe-4">{{ __('maintenance.center_name') }}</th>
                        <th class="pb-3 pe-4">{{ __('driver.vehicle') }}</th>
                        <th class="pb-3 pe-4">{{ __('maintenance.approved_quote') ?? 'Approved Quote' }}</th>
                        <th class="pb-3 pe-4">{{ __('maintenance.final_invoice_amount') ?? 'Final Invoice' }}</th>
                        <th class="pb-3 pe-4">{{ __('maintenance.upload_date') ?? 'Upload Date' }}</th>
                        <th class="pb-3 pe-4">{{ __('fleet.payment_status') }}</th>
                        <th class="pb-3 pe-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                        <tr class="border-b border-slate-200 dark:border-slate-600/30 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors duration-300">
                            <td class="py-6 pe-4 font-bold text-slate-900 dark:text-white">{{ $req->id }}</td>
                            <td class="py-6 pe-4 text-slate-900 dark:text-servx-silver-light">{{ $req->approvedCenter?->name ?? '-' }}</td>
                            <td class="py-6 pe-4 text-slate-900 dark:text-servx-silver-light">{{ $req->vehicle?->plate_number ?? '-' }}</td>
                            <td class="py-6 pe-4 text-slate-600 dark:text-servx-silver">{{ $req->approved_quote_amount ? number_format($req->approved_quote_amount, 2) . ' ' . (__('company.sar') ?? 'ر.س') : '-' }}</td>
                            <td class="py-6 pe-4 text-slate-600 dark:text-servx-silver">{{ $req->final_invoice_amount ? number_format($req->final_invoice_amount, 2) . ' ' . (__('company.sar') ?? 'ر.س') : '-' }}</td>
                            <td class="py-6 pe-4 text-slate-600 dark:text-servx-silver">{{ $req->final_invoice_uploaded_at?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td class="py-6 pe-4">
                                @php $paid = $req->status === 'closed' && $req->invoice_approved_at; @endphp
                                <span class="px-2 py-1 rounded-xl text-xs font-bold {{ $paid ? 'bg-emerald-500/30 text-emerald-700 dark:text-emerald-300 border border-emerald-400/50' : 'bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-400/50' }}">
                                    {{ $paid ? __('fleet.paid') : __('fleet.unpaid') }}
                                </span>
                            </td>
                            <td class="py-6 pe-4">
                                <div class="flex gap-2">
                                    <a href="{{ route('company.maintenance-invoices.view', $req) }}" target="_blank" class="px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 dark:hover:bg-sky-500 text-white text-sm font-semibold transition-colors duration-300">
                                        <i class="fa-solid fa-eye me-1"></i> {{ __('common.view') }}
                                    </a>
                                    <a href="{{ route('company.maintenance-invoices.download', $req) }}" class="px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-600 dark:text-servx-silver-light text-sm font-semibold transition-colors duration-300">
                                        <i class="fa-solid fa-download me-1"></i> {{ __('fleet.download_pdf') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $requests->links() }}</div>
    @endif
</div>

<div class="mt-4">
    <a href="{{ route('company.maintenance-requests.index') }}" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 font-bold text-slate-700 dark:text-servx-silver-light transition-colors duration-300">{{ __('common.back') }}</a>
</div>
@include('company.partials.glass-end')
@endsection
