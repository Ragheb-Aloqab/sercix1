@extends('admin.layouts.app')

@section('title', __('common.edit') . ' — ' . __('invoice.add_fuel_invoice') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('common.edit') . ' — ' . __('invoice.add_fuel_invoice'))
@section('subtitle', __('invoice.add_fuel_invoice'))

@section('content')
@include('company.partials.glass-start', ['title' => __('common.edit') . ' — ' . __('invoice.add_fuel_invoice')])

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-red-500/20 text-red-300 border border-red-400/50">
            <ul class="list-disc ms-5 text-sm space-y-1">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('company.fuel-invoices.update', $companyFuelInvoice) }}" enctype="multipart/form-data"
        class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 sm:p-6 backdrop-blur-sm space-y-4">
        @csrf
        @method('PATCH')

        <div class="flex items-center justify-between mb-6">
            <h2 class="text-lg font-black text-white">{{ __('common.edit') }} — {{ __('invoice.add_fuel_invoice') }}</h2>
            <a href="{{ route('company.fuel.index', request()->only(['from', 'to', 'vehicle_id'])) }}"
                class="px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
                {{ __('common.cancel') }}
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-bold text-slate-400">{{ __('driver.vehicle') }} *</label>
                <select name="vehicle_id" required class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected(old('vehicle_id', $companyFuelInvoice->vehicle_id) == $v->id)>
                            {{ $v->plate_number }} — {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-bold text-slate-400">{{ __('maintenance.final_invoice_amount') }} ({{ __('company.sar') }})</label>
                <input type="number" name="amount" step="0.01" min="0" value="{{ old('amount', $companyFuelInvoice->amount) }}"
                    class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white" placeholder="0.00">
            </div>
        </div>

        <div>
            <label class="text-sm font-bold text-slate-400">{{ __('common.description') }}</label>
            <input type="text" name="description" maxlength="500" value="{{ old('description', $companyFuelInvoice->description) }}"
                class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white" placeholder="{{ __('common.optional') }}">
        </div>

        <div>
            <label class="text-sm font-bold text-slate-400">{{ __('invoice.upload_file_label') }} — {{ __('common.optional') }}</label>
            <p class="text-xs text-slate-500 mt-1 mb-2">{{ __('maintenance.invoice_file_accept', ['max' => $maxFileMb]) }}</p>
            @if($companyFuelInvoice->invoice_file)
                <p class="text-slate-400 text-sm mb-2">{{ $companyFuelInvoice->original_filename ?? basename($companyFuelInvoice->invoice_file) }}</p>
            @endif
            <input type="file" name="invoice_file" accept=".pdf,.jpg,.jpeg,.png,.webp"
                class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white file:me-2 file:rounded-xl file:border-0 file:bg-amber-500/20 file:px-4 file:py-2 file:font-bold file:text-amber-400">
        </div>

        <div class="flex flex-wrap gap-3 pt-4">
            <button type="submit" class="px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition-colors">
                <i class="fa-solid fa-check me-2"></i>{{ __('common.save') }}
            </button>
            <a href="{{ route('company.fuel.index', request()->only(['from', 'to', 'vehicle_id'])) }}"
                class="px-6 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold hover:border-slate-400/50 transition-colors">
                {{ __('common.cancel') }}
            </a>
        </div>
    </form>

@include('company.partials.glass-end')
@endsection
