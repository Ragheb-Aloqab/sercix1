@extends('admin.layouts.app')

@section('title', __('maintenance.request') . ' #' . $request->id . ' | Servx Motors')
@section('page_title', __('maintenance.request') . ' #' . $request->id)
@section('subtitle', $request->status_label)

@section('content')
@include('company.partials.glass-start', ['title' => __('maintenance.request') . ' #' . $request->id])
<div class="space-y-6">
    {{-- Request details --}}
    <div class="dash-card">
        <h2 class="dash-section-title">{{ __('maintenance.request_details') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div><span class="text-servx-silver">{{ __('maintenance.company') }}:</span> {{ $request->company?->company_name ?? '-' }}</div>
            <div><span class="text-servx-silver">{{ __('driver.vehicle') }}:</span> {{ $request->vehicle?->plate_number ?? '-' }}</div>
            <div><span class="text-servx-silver">{{ __('vehicles.make_model') ?? 'Make / Model' }}:</span> {{ trim(($request->vehicle?->make ?? '') . ' ' . ($request->vehicle?->model ?? '')) ?: '-' }}</div>
            <div><span class="text-servx-silver">{{ __('vehicles.year') ?? 'Year' }}:</span> {{ $request->vehicle?->year ?? '-' }}</div>
            <div><span class="text-servx-silver">{{ __('driver.maintenance_type') }}:</span> {{ \App\Enums\MaintenanceType::tryFrom($request->maintenance_type)?->label() ?? $request->maintenance_type }}</div>
            <div class="md:col-span-2"><span class="text-servx-silver">{{ __('driver.description') }}:</span> {{ $request->description }}</div>
            @if($request->requestServices->isNotEmpty())
            <div class="md:col-span-2">
                <span class="text-servx-silver block mb-2">{{ __('maintenance.services') }}:</span>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($request->requestServices as $rs)
                        <li>{{ $rs->display_name }} @if($rs->driverProposedService && $rs->driverProposedService->isPending()) <span class="text-amber-400 text-sm">({{ __('maintenance.pending_approval') ?? 'Pending approval' }})</span> @endif</li>
                    @endforeach
                </ul>
            </div>
            @endif
            @if($request->rejection_reason)
                <div class="md:col-span-2 text-rose-400">{{ __('orders.rejection_reason') }}: {{ $request->rejection_reason }}</div>
            @endif
        </div>
    </div>

    {{-- Approved quotation: services with prices and images from maintenance center --}}
    @if($request->approvedQuotation && in_array($request->status, ['center_approved', 'in_progress', 'waiting_for_invoice_approval'], true))
        @php
            $approvedQuote = $request->approvedQuotation;
            $lineItems = $approvedQuote->lineItems ?? collect();
        @endphp
        <div class="dash-card">
            <h2 class="dash-section-title">{{ __('maintenance.quoted_services_by_center') }}</h2>
            @if($request->approvedCenter)
                <p class="text-servx-silver text-sm mb-4">{{ __('maintenance.center_name') }}: <span class="text-white font-semibold">{{ $request->approvedCenter->name }}</span></p>
            @endif
            @if($lineItems->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-start text-servx-silver text-sm border-b border-slate-600/50">
                                <th class="pb-3 pe-4">{{ __('maintenance.service') }}</th>
                                <th class="pb-3 pe-4">{{ __('maintenance.price') }}</th>
                                <th class="pb-3 pe-4">{{ __('maintenance.notes') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lineItems as $item)
                                <tr class="border-b border-slate-600/30 align-top">
                                    <td class="py-4 pe-4 font-medium text-slate-900 dark:text-servx-silver-light">
                                        {{ $item->maintenanceRequestService?->display_name ?? '-' }}
                                    </td>
                                    <td class="py-4 pe-4 font-bold">{{ number_format((float) $item->price, 2) }} {{ __('company.sar') ?? 'ر.س' }}</td>
                                    <td class="py-4 pe-4 text-sm text-servx-silver">{{ $item->notes ? Str::limit($item->notes, 80) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-sm text-servx-silver">{{ __('maintenance.total') }}: <span class="font-bold text-white">{{ number_format($approvedQuote->total_price, 2) }} {{ __('company.sar') ?? 'ر.س' }}</span></p>
                @if($approvedQuote->invoice_image_path)
                    <div class="mt-6 pt-4 border-t border-slate-600/50">
                        <h3 class="font-bold text-servx-silver-light mb-2">{{ __('maintenance.invoice_image_at_end') }}</h3>
                        @php $invoiceImgUrl = asset('storage/' . $approvedQuote->invoice_image_path); @endphp
                        <a href="{{ $invoiceImgUrl }}" target="_blank" rel="noopener" class="inline-block rounded-xl overflow-hidden border border-slate-600/50 hover:border-sky-500/50 transition-colors">
                            <img src="{{ $invoiceImgUrl }}" alt="{{ $approvedQuote->invoice_image_original_name ?? __('common.preview') }}" class="max-h-64 w-auto max-w-full object-contain bg-slate-800/40" loading="lazy">
                        </a>
                        <a href="{{ $invoiceImgUrl }}" target="_blank" rel="noopener" class="text-sky-400 text-sm mt-2 inline-block">{{ __('common.view') }}</a>
                    </div>
                @endif
            @else
                <p class="text-servx-silver">{{ __('maintenance.approved_quote') }}: <span class="font-bold text-white">{{ number_format($approvedQuote->total_price, 2) }} {{ __('company.sar') ?? 'ر.س' }}</span></p>
                @if($approvedQuote->invoice_image_path)
                    <div class="mt-6 pt-4 border-t border-slate-600/50">
                        <h3 class="font-bold text-servx-silver-light mb-2">{{ __('maintenance.invoice_image_at_end') }}</h3>
                        @php $invoiceImgUrl = asset('storage/' . $approvedQuote->invoice_image_path); @endphp
                        <a href="{{ $invoiceImgUrl }}" target="_blank" rel="noopener" class="inline-block rounded-xl overflow-hidden border border-slate-600/50 hover:border-sky-500/50 transition-colors">
                            <img src="{{ $invoiceImgUrl }}" alt="{{ $approvedQuote->invoice_image_original_name ?? __('common.preview') }}" class="max-h-64 w-auto max-w-full object-contain bg-slate-800/40" loading="lazy">
                        </a>
                        <a href="{{ $invoiceImgUrl }}" target="_blank" rel="noopener" class="text-sky-400 text-sm mt-2 inline-block">{{ __('common.view') }}</a>
                    </div>
                @endif
            @endif
        </div>
    @endif

    {{-- NEW_REQUEST: Approve proposed services first, then Reject or Send RFQ --}}
    @if($request->status === 'new_request')
        @php
            $pendingProposed = $request->requestServices->filter(fn ($rs) => $rs->driverProposedService && $rs->driverProposedService->isPending())->pluck('driverProposedService')->unique('id');
            $canSendRfq = !$request->hasPendingProposedServices();
            $activeCenters = \App\Models\MaintenanceCenter::active()->orderBy('name')->get();
        @endphp
        @if($pendingProposed->isNotEmpty())
        <div class="dash-card">
            <h2 class="dash-section-title">{{ __('maintenance.driver_proposed_services_pending') ?? 'Driver-proposed services — pending approval' }}</h2>
            <p class="text-servx-silver text-sm mb-4">{{ __('maintenance.approve_proposed_before_rfq') ?? 'Approve or reject these services before sending RFQ.' }}</p>
            <ul class="space-y-3">
                @foreach($pendingProposed as $proposed)
                    <li class="flex flex-wrap items-center justify-between gap-4 p-4 rounded-xl border border-amber-500/40 bg-amber-500/10">
                        <div>
                            <span class="font-bold text-slate-900 dark:text-servx-silver-light">{{ $proposed->name }}</span>
                            @if($proposed->description)<p class="text-sm text-servx-silver mt-1">{{ $proposed->description }}</p>@endif
                            @if($proposed->image_path)<a href="{{ asset('storage/' . $proposed->image_path) }}" target="_blank" class="text-sky-400 text-sm mt-2 inline-block">{{ __('common.view') }}</a>@endif
                        </div>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('company.maintenance-requests.approve-proposed-service', [$request, $proposed]) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold">{{ __('maintenance.approve') }}</button>
                            </form>
                            <form method="POST" action="{{ route('company.maintenance-requests.reject-proposed-service', [$request, $proposed]) }}" class="inline" onsubmit="return confirm('{{ __('maintenance.confirm_reject_proposed') ?? 'Reject this proposed service?' }}');">
                                @csrf
                                <input type="text" name="rejection_reason" placeholder="{{ __('orders.rejection_reason') }}" class="rounded-lg border border-slate-600/50 bg-slate-800/60 px-3 py-1 text-sm me-2 max-w-[180px]">
                                <button type="submit" class="px-3 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold">{{ __('common.reject') }}</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
        @endif
        <div class="dash-card flex flex-wrap gap-4">
            <form method="POST" action="{{ route('company.maintenance-requests.reject', $request) }}" class="flex-1" onsubmit="return confirm('{{ __('common.confirm_reject') ?? 'تأكيد الرفض؟' }}');">
                @csrf
                <input type="text" name="rejection_reason" required placeholder="{{ __('orders.rejection_reason') }}" class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 mb-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold">{{ __('common.reject') }}</button>
            </form>
            <form method="POST" action="{{ route('company.maintenance-requests.send-rfq', $request) }}" class="flex-1" id="send-rfq-form">
                @csrf
                <input type="hidden" name="broadcast" value="0" id="broadcast-input">
                <div class="mb-3">
                    <label class="text-sm font-bold text-servx-silver-light block mb-2">{{ __('maintenance.rfq_mode') ?? 'Send RFQ to:' }}</label>
                    <div class="flex gap-4 mb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="rfq_mode" value="select" checked class="rounded" data-mode="select">
                            <span>{{ __('maintenance.option_select_centers') ?? 'Option A: Select specific centers' }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="rfq_mode" value="broadcast" class="rounded" data-mode="broadcast">
                            <span>{{ __('maintenance.option_broadcast_all') ?? 'Option B: Broadcast to all active centers' }}</span>
                        </label>
                    </div>
                </div>
                <div class="mb-2" id="centers-select-wrap">
                    <label class="text-sm font-bold text-servx-silver-light">{{ __('maintenance.select_centers') }}</label>
                    <div class="mt-2 space-y-2 max-h-32 overflow-y-auto">
                        @foreach($activeCenters as $c)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="center_ids[]" value="{{ $c->id }}" class="rounded center-checkbox">
                                <span>{{ $c->name }} ({{ $c->phone }}){{ $c->city ? ' · ' . $c->city : '' }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if($activeCenters->isEmpty())
                        <p class="text-amber-400 text-sm">{{ __('maintenance.no_active_centers') ?? 'No active maintenance centers available. Contact admin.' }}</p>
                    @endif
                </div>
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold" {{ !$canSendRfq || $activeCenters->isEmpty() ? 'disabled' : '' }}>{{ __('maintenance.send_rfq') }}</button>
                @if(!$canSendRfq)
                    <p class="text-amber-400 text-sm w-full">{{ __('maintenance.approve_all_proposed_first') ?? 'Approve all driver-proposed services above before sending RFQ.' }}</p>
                @endif
            </form>
        </div>
        <script>
            document.getElementById('send-rfq-form').addEventListener('submit', function(e) {
                var mode = document.querySelector('input[name="rfq_mode"]:checked')?.value;
                var broadcastInput = document.getElementById('broadcast-input');
                broadcastInput.value = mode === 'broadcast' ? '1' : '0';
                if (mode === 'broadcast') {
                    document.querySelectorAll('.center-checkbox').forEach(cb => cb.checked = false);
                }
            });
            document.querySelectorAll('input[name="rfq_mode"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    document.getElementById('centers-select-wrap').style.display = this.value === 'broadcast' ? 'none' : 'block';
                });
            });
        </script>
    @endif

    {{-- QUOTE_SUBMITTED: Compare quotes --}}
    @if($request->status === 'quote_submitted' && $request->quotations->whereNotNull('submitted_at')->isNotEmpty())
        <div class="dash-card">
            <h2 class="dash-section-title">{{ __('maintenance.quote_comparison') }}</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-start text-servx-silver text-sm border-b border-slate-600/50">
                            <th class="pb-3 pe-4">{{ __('maintenance.center_name') }}</th>
                            <th class="pb-3 pe-4">{{ __('maintenance.price') }}</th>
                            <th class="pb-3 pe-4">{{ __('maintenance.duration') }}</th>
                            <th class="pb-3 pe-4">{{ __('maintenance.notes') }}</th>
                            <th class="pb-3 pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($request->quotations->whereNotNull('submitted_at') as $q)
                            <tr class="border-b border-slate-600/30">
                                <td class="py-4 pe-4">{{ $q->maintenanceCenter->name }}</td>
                                <td class="py-4 pe-4 font-bold">{{ number_format($q->total_price, 2) }} {{ __('company.sar') ?? 'ر.س' }}</td>
                                <td class="py-4 pe-4">{{ $q->estimated_duration_minutes ? $q->estimated_duration_minutes . ' ' . __('maintenance.minutes') : '-' }}</td>
                                <td class="py-4 pe-4 text-sm">{{ Str::limit($q->notes, 50) }}</td>
                                <td class="py-4 pe-4">
                                    <form method="POST" action="{{ route('company.maintenance-requests.approve-center', [$request, $q->id]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold">{{ __('maintenance.approve') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <form method="POST" action="{{ route('company.maintenance-requests.reject-all-quotes', $request) }}" class="mt-4" onsubmit="return confirm('{{ __('maintenance.confirm_reject_all') ?? 'رفض الكل وإعادة الطلب؟' }}');">
                @csrf
                @foreach($request->rfqAssignments->pluck('maintenance_center_id') as $cid)
                    <input type="hidden" name="center_ids[]" value="{{ $cid }}">
                @endforeach
                <button type="submit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold">{{ __('maintenance.reject_all_re_request') }}</button>
            </form>
        </div>
    @endif

    {{-- WAITING_FOR_INVOICE_APPROVAL: View invoice & details, then Approve/Reject --}}
    @if($request->status === 'waiting_for_invoice_approval')
        <div class="dash-card">
            <h2 class="dash-section-title">{{ __('maintenance.invoice_approval') }}</h2>
            <p class="text-servx-silver text-sm mb-4">{{ __('maintenance.review_invoice_before_approve') }}</p>

            @if($request->final_invoice_pdf_path)
                {{-- Invoice details summary --}}
                <div class="rounded-xl border border-slate-600/50 bg-slate-800/40 p-4 mb-4">
                    <h3 class="font-bold text-servx-silver-light mb-3">{{ __('maintenance.request_details') }}</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
                        <div><span class="text-servx-silver">{{ __('maintenance.invoice_file_name') }}:</span> <span class="text-white">{{ $request->final_invoice_original_name ?? basename($request->final_invoice_pdf_path) }}</span></div>
                        @if($request->final_invoice_uploaded_at)
                            <div><span class="text-servx-silver">{{ __('maintenance.uploaded_at') }}:</span> <span class="text-white">{{ $request->final_invoice_uploaded_at->format('Y-m-d H:i') }}</span></div>
                        @endif
                        @if($request->approvedCenter)
                            <div><span class="text-servx-silver">{{ __('maintenance.center_name') }}:</span> <span class="text-white">{{ $request->approvedCenter->name }}</span></div>
                        @endif
                        @if($request->approved_quote_amount !== null)
                            <div><span class="text-servx-silver">{{ __('maintenance.approved_quote') }}:</span> <span class="text-white">{{ number_format($request->approved_quote_amount, 2) }} {{ __('company.sar') ?? 'ر.س' }}</span></div>
                        @endif
                        @if($request->final_invoice_amount !== null)
                            <div><span class="text-servx-silver">{{ __('maintenance.final_invoice_amount') }}:</span> <span class="font-bold text-emerald-400">{{ number_format($request->final_invoice_amount, 2) }} {{ __('company.sar') ?? 'ر.س' }}</span></div>
                        @endif
                        @if($request->final_invoice_tax_type)
                            <div><span class="text-servx-silver">{{ __('maintenance.final_invoice_tax_type') }}:</span> <span class="text-white">{{ $request->final_invoice_tax_type === 'with_tax' ? __('maintenance.with_tax_vat') : __('maintenance.without_tax') }}</span></div>
                        @endif
                        @if($request->completion_date)
                            <div><span class="text-servx-silver">{{ __('maintenance.completion_date') }}:</span> <span class="text-white">{{ $request->completion_date->format('Y-m-d') }}</span></div>
                        @endif
                    </div>
                </div>

                {{-- View / Download and embedded preview --}}
                <div class="flex flex-wrap gap-4 mb-4">
                    <a href="{{ route('company.maintenance-invoices.view', $request) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold transition-colors duration-300">
                        <i class="fa-solid fa-external-link-alt"></i> {{ __('maintenance.view_invoice') }}
                    </a>
                    <a href="{{ route('company.maintenance-invoices.download', $request) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 font-semibold text-servx-silver-light transition-colors duration-300">
                        <i class="fa-solid fa-download"></i> {{ __('common.download') }}
                    </a>
                </div>

                {{-- Embedded invoice preview (PDF or image-as-PDF) --}}
                <div class="mb-6">
                    <p class="text-servx-silver text-sm mb-2">{{ __('common.preview') }}:</p>
                    <div class="rounded-xl border border-slate-600/50 bg-slate-900/50 overflow-hidden" style="min-height: 420px;">
                        <iframe src="{{ route('company.maintenance-invoices.view', $request) }}" title="{{ __('maintenance.view_invoice') }}" class="w-full border-0 rounded-xl" style="height: 520px;"></iframe>
                    </div>
                </div>
            @else
                <p class="text-amber-400 mb-4">{{ __('maintenance.no_invoices') ?? 'No invoice file uploaded yet.' }}</p>
            @endif

            {{-- Approve / Reject actions --}}
            <div class="flex flex-wrap gap-4 pt-2 border-t border-slate-600/50">
                <form method="POST" action="{{ route('company.maintenance-requests.approve-invoice', $request) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold" {{ !$request->final_invoice_pdf_path ? 'disabled' : '' }}>{{ __('maintenance.approve_invoice') }}</button>
                </form>
                <form method="POST" action="{{ route('company.maintenance-requests.reject-invoice', $request) }}" class="flex flex-wrap items-center gap-2">
                    @csrf
                    <input type="text" name="rejection_reason" required placeholder="{{ __('orders.rejection_reason') }}" class="rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light placeholder-servx-silver">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold">{{ __('maintenance.reject_invoice') }}</button>
                </form>
            </div>
        </div>
    @endif

    <div>
        <a href="{{ route('company.maintenance-requests.index') }}" class="px-4 py-2 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 font-bold text-servx-silver-light">{{ __('common.back') }}</a>
    </div>
</div>
@include('company.partials.glass-end')
@endsection
