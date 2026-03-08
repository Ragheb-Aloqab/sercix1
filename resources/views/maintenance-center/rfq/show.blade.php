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
                <div><span class="text-slate-600 dark:text-servx-silver">{{ __('maintenance.company') }}:</span> <span class="text-slate-900 dark:text-white">{{ $request->company?->company_name ?? '-' }}</span></div>
                <div><span class="text-slate-600 dark:text-servx-silver">{{ __('driver.vehicle') }}:</span> <span class="text-slate-900 dark:text-white">{{ $request->vehicle?->plate_number ?? '-' }}</span></div>
                <div><span class="text-slate-600 dark:text-servx-silver">{{ __('vehicles.make_model') ?? 'Make / Model' }}:</span> <span class="text-slate-900 dark:text-white">{{ trim(($request->vehicle?->make ?? '') . ' ' . ($request->vehicle?->model ?? '')) ?: '-' }}</span></div>
                <div><span class="text-slate-600 dark:text-servx-silver">{{ __('vehicles.year') ?? 'Year' }}:</span> <span class="text-slate-900 dark:text-white">{{ $request->vehicle?->year ?? '-' }}</span></div>
                <div><span class="text-slate-600 dark:text-servx-silver">{{ __('driver.maintenance_type') }}:</span> <span class="text-slate-900 dark:text-white">{{ \App\Enums\MaintenanceType::tryFrom($request->maintenance_type)?->label() ?? $request->maintenance_type }}</span></div>
                <div class="md:col-span-2"><span class="text-slate-600 dark:text-servx-silver">{{ __('driver.description') }}:</span> <span class="text-slate-900 dark:text-white">{{ $request->description }}</span></div>
                @if($request->requestServices->isNotEmpty())
                <div class="md:col-span-2">
                    <span class="text-slate-600 dark:text-servx-silver block mb-2">{{ __('maintenance.services') }}:</span>
                    <ul class="list-disc list-inside space-y-1 text-slate-900 dark:text-white">
                        @foreach($request->requestServices as $rs)
                            <li>{{ $rs->display_name }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
        </div>

        {{-- WAITING_FOR_QUOTES: Submit quotation --}}
        @if($request->status === 'waiting_for_quotes')
            @php
                $myQuotation = $request->quotations->where('maintenance_center_id', auth('maintenance_center')->id())->first();
                $hasRequestServices = $request->requestServices->isNotEmpty();
            @endphp
            @if(!$myQuotation || !$myQuotation->submitted_at)
                <div class="dash-card">
                    <h2 class="dash-section-title">{{ __('maintenance.submit_quotation') }}</h2>
                    <form method="POST" action="{{ route('maintenance-center.rfq.submit-quotation', $request) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @if($hasRequestServices)
                            <p class="text-slate-600 dark:text-servx-silver text-sm mb-4">{{ __('maintenance.quote_per_service_help') ?? 'Enter price for each service. One optional image can be added at the end for the whole quotation.' }}</p>
                            <div class="space-y-4">
                                @foreach($request->requestServices as $rs)
                                    <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-600/50 bg-slate-50 dark:bg-slate-800/40">
                                        <label class="font-bold text-slate-800 dark:text-servx-silver-light block mb-2">{{ $rs->display_name }}</label>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div>
                                                <label class="text-sm text-slate-600 dark:text-servx-silver">{{ __('maintenance.price') }} ({{ __('company.sar') ?? 'ر.س' }}) *</label>
                                                <input type="number" name="line_items[{{ $rs->id }}][price]" step="0.01" min="0" required value="{{ old("line_items.{$rs->id}.price") }}" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2">
                                            </div>
                                            <div>
                                                <label class="text-sm text-slate-600 dark:text-servx-silver">{{ __('maintenance.notes') }}</label>
                                                <input type="text" name="line_items[{{ $rs->id }}][notes]" maxlength="500" value="{{ old("line_items.{$rs->id}.notes") }}" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.estimated_duration') }} ({{ __('maintenance.minutes') }})</label>
                                <input type="number" name="estimated_duration_minutes" min="1" value="{{ old('estimated_duration_minutes') }}" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2">
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.quotation_pdf') }} ({{ __('common.optional') }})</label>
                                <input type="file" name="quotation_pdf" accept=".pdf" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 file:me-2 file:rounded file:border-0 file:bg-sky-500/20 file:px-3 file:py-1 file:text-sky-400">
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.invoice_image_at_end') }}</label>
                                <p class="text-slate-600 dark:text-servx-silver text-xs mt-1 mb-2">{{ __('maintenance.invoice_image_at_end_help') }}</p>
                                <input type="file" name="invoice_image" accept=".jpg,.jpeg,.png" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 px-4 py-2 file:me-2 file:rounded file:border-0 file:bg-sky-500/20 file:px-3 file:py-1 file:text-sky-400">
                            </div>
                        @else
                            <div>
                                <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.price') }} ({{ __('company.sar') ?? 'ر.س' }}) *</label>
                                <input type="number" name="price" step="0.01" min="0" required value="{{ old('price', $myQuotation?->price) }}" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.estimated_duration') }} ({{ __('maintenance.minutes') }})</label>
                                <input type="number" name="estimated_duration_minutes" min="1" value="{{ old('estimated_duration_minutes', $myQuotation?->estimated_duration_minutes) }}" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.notes') }}</label>
                                <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">{{ old('notes', $myQuotation?->notes) }}</textarea>
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.quotation_pdf') }} ({{ __('common.optional') }})</label>
                                <input type="file" name="quotation_pdf" accept=".pdf" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light file:me-2 file:rounded file:border-0 file:bg-sky-500/20 file:px-3 file:py-1 file:text-sky-400 transition-colors duration-300">
                            </div>
                            <div>
                                <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.invoice_image_at_end') }}</label>
                                <p class="text-slate-600 dark:text-servx-silver text-xs mt-1 mb-2">{{ __('maintenance.invoice_image_at_end_help') }}</p>
                                <input type="file" name="invoice_image" accept=".jpg,.jpeg,.png" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 file:me-2 file:rounded file:border-0 file:bg-sky-500/20 file:px-3 file:py-1 file:text-sky-400">
                            </div>
                        @endif
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
                <h2 class="dash-section-title">{{ __('maintenance.mark_started') ?? 'Start service' }}</h2>
                <p class="text-slate-600 dark:text-servx-silver text-sm mb-4">{{ __('maintenance.start_service_before_invoice') }}</p>
                <form method="POST" action="{{ route('maintenance-center.rfq.start', $request) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">{{ __('maintenance.mark_started') ?? 'بدء العمل' }}</button>
                </form>
            </div>
        @endif

        {{-- IN_PROGRESS: Upload final invoice (only after center has started the service) --}}
        @if($request->status === 'in_progress' && (int)$request->approved_center_id === (int)auth('maintenance_center')->id())
            <div class="dash-card">
                <h2 class="dash-section-title">{{ __('maintenance.upload_final_invoice') }}</h2>
                <p class="text-slate-600 dark:text-servx-silver text-sm mb-4">{{ __('maintenance.upload_invoice_after_start') }}</p>
                <form method="POST" action="{{ route('maintenance-center.rfq.upload-invoice', $request) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.final_invoice') }} (PDF, JPG, JPEG, PNG, WEBP — {{ __('maintenance.invoice_file_accept', ['max' => config('servx.invoice_max_size_mb', 5)]) }}) *</label>
                        <input type="file" name="final_invoice" accept=".pdf,.jpg,.jpeg,.png,.webp" required class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light file:me-2 file:rounded file:border-0 file:bg-sky-500/20 file:px-3 file:py-1 file:text-sky-400 transition-colors duration-300">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.final_invoice_amount') ?? 'Final Invoice Amount' }} ({{ __('company.sar') ?? 'ر.س' }})</label>
                        <input type="number" name="final_invoice_amount" step="0.01" min="0" value="{{ old('final_invoice_amount', $request->approved_quote_amount) }}" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light block mb-2">{{ __('maintenance.final_invoice_tax_type') }}</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="final_invoice_tax_type" value="with_tax" {{ old('final_invoice_tax_type', 'without_tax') === 'with_tax' ? 'checked' : '' }} class="rounded">
                                <span>{{ __('maintenance.with_tax_vat') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="final_invoice_tax_type" value="without_tax" {{ old('final_invoice_tax_type', 'without_tax') === 'without_tax' ? 'checked' : '' }} class="rounded">
                                <span>{{ __('maintenance.without_tax') }}</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-bold text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.completion_date') }}</label>
                        <input type="date" name="completion_date" value="{{ old('completion_date', now()->format('Y-m-d')) }}" class="mt-1 w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">
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
            <a href="{{ route('maintenance-center.dashboard') }}" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 font-bold text-slate-700 dark:text-servx-silver-light transition-colors duration-300">{{ __('common.back') }}</a>
        </div>
    </div>
</div>
@endsection
