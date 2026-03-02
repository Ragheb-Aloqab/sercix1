@extends('admin.layouts.app')

@section('title', __('maintenance.rfq') ?? 'طلب عرض' . ' #' . $request->id)
@section('page_title', __('maintenance.rfq') ?? 'طلب عرض' . ' #' . $request->id)
@section('subtitle', $request->status_label)

@section('content')
<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-7xl mx-auto space-y-6">
        <div class="dash-card">
            <h2 class="dash-section-title">{{ __('maintenance.request_details') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><span class="text-servx-silver">{{ __('maintenance.company') }}:</span> {{ $request->company?->company_name ?? '-' }}</div>
                <div><span class="text-servx-silver">{{ __('driver.vehicle') }}:</span> {{ $request->vehicle?->plate_number ?? '-' }}</div>
                <div><span class="text-servx-silver">{{ __('vehicles.make_model') ?? 'Make / Model' }}:</span> {{ trim(($request->vehicle?->make ?? '') . ' ' . ($request->vehicle?->model ?? '')) ?: '-' }}</div>
                <div><span class="text-servx-silver">{{ __('vehicles.year') ?? 'Year' }}:</span> {{ $request->vehicle?->year ?? '-' }}</div>
                <div><span class="text-servx-silver">{{ __('driver.maintenance_type') }}:</span> {{ \App\Enums\MaintenanceType::tryFrom($request->maintenance_type)?->label() ?? $request->maintenance_type }}</div>
                <div class="md:col-span-2"><span class="text-servx-silver">{{ __('driver.description') }}:</span> {{ $request->description }}</div>
            </div>
        </div>

        {{-- WAITING_FOR_QUOTES: Submit quotation --}}
        @if($request->status === 'waiting_for_quotes')
            @php $myQuotation = $request->quotations->where('maintenance_center_id', auth('maintenance_center')->id())->first(); @endphp
            @if(!$myQuotation || !$myQuotation->submitted_at)
                <div class="dash-card">
                    <h2 class="dash-section-title">{{ __('maintenance.submit_quotation') }}</h2>
                    <form method="POST" action="{{ route('maintenance-center.rfq.submit-quotation', $request) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="text-sm font-bold text-servx-silver-light">{{ __('maintenance.price') }} ({{ __('company.sar') ?? 'ر.س' }}) *</label>
                            <input type="number" name="price" step="0.01" min="0" required value="{{ old('price', $myQuotation?->price) }}" class="mt-1 w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-servx-silver-light">{{ __('maintenance.estimated_duration') }} ({{ __('maintenance.minutes') }})</label>
                            <input type="number" name="estimated_duration_minutes" min="1" value="{{ old('estimated_duration_minutes', $myQuotation?->estimated_duration_minutes) }}" class="mt-1 w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light">
                        </div>
                        <div>
                            <label class="text-sm font-bold text-servx-silver-light">{{ __('maintenance.notes') }}</label>
                            <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light">{{ old('notes', $myQuotation?->notes) }}</textarea>
                        </div>
                        <div>
                            <label class="text-sm font-bold text-servx-silver-light">{{ __('maintenance.quotation_pdf') }} ({{ __('common.optional') }})</label>
                            <input type="file" name="quotation_pdf" accept=".pdf" class="mt-1 w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light file:me-2 file:rounded file:border-0 file:bg-sky-500/20 file:px-3 file:py-1 file:text-sky-400">
                        </div>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">{{ __('maintenance.submit_quotation') }}</button>
                    </form>
                </div>
            @else
                <div class="dash-card border-emerald-500/30 bg-emerald-500/5">
                    <p class="text-emerald-400">{{ __('messages.quotation_submitted') ?? 'تم إرسال عرضك.' }}</p>
                </div>
            @endif
        @endif

        {{-- CENTER_APPROVED: Mark as started --}}
        @if($request->status === 'center_approved' && (int)$request->approved_center_id === (int)auth('maintenance_center')->id())
            <div class="dash-card">
                <form method="POST" action="{{ route('maintenance-center.rfq.start', $request) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">{{ __('maintenance.mark_started') ?? 'بدء العمل' }}</button>
                </form>
            </div>
        @endif

        {{-- IN_PROGRESS: Upload final invoice --}}
        @if($request->status === 'in_progress' && (int)$request->approved_center_id === (int)auth('maintenance_center')->id())
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('maintenance.upload_final_invoice') }}</h2>
                <form method="POST" action="{{ route('maintenance-center.rfq.upload-invoice', $request) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-bold text-servx-silver-light">{{ __('maintenance.final_invoice') }} (PDF, JPG, PNG) *</label>
                        <input type="file" name="final_invoice" accept=".pdf,.jpg,.jpeg,.png" required class="mt-1 w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light file:me-2 file:rounded file:border-0 file:bg-sky-500/20 file:px-3 file:py-1 file:text-sky-400">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-servx-silver-light">{{ __('maintenance.final_invoice_amount') ?? 'Final Invoice Amount' }} ({{ __('company.sar') ?? 'ر.س' }})</label>
                        <input type="number" name="final_invoice_amount" step="0.01" min="0" value="{{ old('final_invoice_amount', $request->approved_quote_amount) }}" class="mt-1 w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-servx-silver-light">{{ __('maintenance.completion_date') }}</label>
                        <input type="date" name="completion_date" value="{{ old('completion_date', now()->format('Y-m-d')) }}" class="mt-1 w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">{{ __('maintenance.submit_invoice') ?? 'إرسال الفاتورة' }}</button>
                </form>
            </div>
        @endif

        @if (session('success'))
            <div class="dash-card border-emerald-500/30 bg-emerald-500/5">
                <p class="text-emerald-400">{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="dash-card border-rose-500/30 bg-rose-500/5">
                <p class="text-rose-400">{{ session('error') }}</p>
            </div>
        @endif

        <div>
            <a href="{{ route('maintenance-center.dashboard') }}" class="px-4 py-2 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 font-bold text-servx-silver-light">{{ __('common.back') }}</a>
        </div>
    </div>
</div>
@endsection
