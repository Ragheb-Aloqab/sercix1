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
            @if($request->rejection_reason)
                <div class="md:col-span-2 text-rose-400">{{ __('orders.rejection_reason') }}: {{ $request->rejection_reason }}</div>
            @endif
        </div>
    </div>

    {{-- NEW_REQUEST: Reject or Send RFQ --}}
    @if($request->status === 'new_request')
        @php
            $activeCenters = \App\Models\MaintenanceCenter::active()->orderBy('name')->get();
        @endphp
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
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold" {{ $activeCenters->isEmpty() ? 'disabled' : '' }}>{{ __('maintenance.send_rfq') }}</button>
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
                                <td class="py-4 pe-4 font-bold">{{ number_format($q->price, 2) }} {{ __('company.sar') ?? 'ر.س' }}</td>
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

    {{-- WAITING_FOR_INVOICE_APPROVAL: Approve/Reject invoice --}}
    @if($request->status === 'waiting_for_invoice_approval')
        <div class="dash-card">
            <h2 class="dash-section-title">{{ __('maintenance.invoice_approval') }}</h2>
            @if($request->final_invoice_pdf_path)
                <div class="flex flex-wrap gap-4 mb-4">
                    <a href="{{ route('company.maintenance-invoices.view', $request) }}" target="_blank" class="inline-flex items-center gap-2 text-sky-400 hover:text-sky-300 font-semibold">
                        <i class="fa-solid fa-file-pdf"></i> {{ __('maintenance.view_invoice') }}
                    </a>
                    <a href="{{ route('company.maintenance-invoices.download', $request) }}" class="inline-flex items-center gap-2 text-servx-silver-light hover:text-white font-semibold">
                        <i class="fa-solid fa-download"></i> {{ __('common.download') }}
                    </a>
                </div>
                @if($request->approved_quote_amount || $request->final_invoice_amount)
                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                        @if($request->approved_quote_amount)
                            <div><span class="text-servx-silver">{{ __('maintenance.approved_quote') ?? 'Approved Quote' }}:</span> {{ number_format($request->approved_quote_amount, 2) }} {{ __('company.sar') ?? 'ر.س' }}</div>
                        @endif
                        @if($request->final_invoice_amount)
                            <div><span class="text-servx-silver">{{ __('maintenance.final_invoice_amount') ?? 'Final Invoice' }}:</span> {{ number_format($request->final_invoice_amount, 2) }} {{ __('company.sar') ?? 'ر.س' }}</div>
                        @endif
                    </div>
                @endif
            @endif
            <div class="flex gap-4">
                <form method="POST" action="{{ route('company.maintenance-requests.approve-invoice', $request) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">{{ __('maintenance.approve_invoice') }}</button>
                </form>
                <form method="POST" action="{{ route('company.maintenance-requests.reject-invoice', $request) }}">
                    @csrf
                    <input type="text" name="rejection_reason" required placeholder="{{ __('orders.rejection_reason') }}" class="rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 me-2">
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
