@extends('layouts.driver')

@section('title', __('orders.order') . ' #' . $order->id)

@section('content')
<div class="max-w-2xl mx-auto w-full">
    <h1 class="text-2xl font-black mb-6">طلب #{{ $order->id }}</h1>

    <div class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6 space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-slate-500">{{ __('orders.status_label') }}</span>
            <span class="px-3 py-1 rounded-xl text-sm font-semibold
                @if($order->status === 'pending_approval') bg-amber-100 text-amber-800
                @elseif($order->status === 'rejected') bg-rose-100 text-rose-800
                @elseif($order->status === 'completed') bg-emerald-100 text-emerald-800
                @else bg-sky-100 text-sky-800 @endif">{{ $statusLabel }}</span>
        </div>
        @if ($order->status === 'rejected' && $order->rejection_reason)
            <p class="text-sm text-rose-600">{{ __('orders.rejection_reason') }}: {{ $order->rejection_reason }}</p>
        @endif
        <div class="flex items-center justify-between">
            <span class="text-slate-500">{{ __('orders.service') }}</span>
            <span class="font-bold">{{ $serviceName }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-500">{{ __('orders.amount_required') }}</span>
            <span class="font-bold">{{ number_format((float) $amount, 2) }} {{ __('company.sar') }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-slate-500">{{ __('driver.vehicle') }}</span>
            <span class="font-bold">{{ $order->vehicle?->plate_number ?? '-' }}</span>
        </div>
    </div>

    {{-- Actions based on status --}}
    @if ($order->status === 'approved')
        <form method="POST" action="{{ route('driver.request.start', $order) }}" class="mt-6">
            @csrf
            <button type="submit" class="w-full rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold py-3">
                <i class="fa-solid fa-play me-2"></i>{{ __('driver.start_service') }}
            </button>
        </form>
    @endif

    @if ($order->status === 'in_progress')
        <div class="mt-6 rounded-3xl bg-white border border-slate-200 shadow-soft p-6">
            <h3 class="font-black text-lg mb-3">{{ __('driver.upload_invoice') }}</h3>
            <p class="text-sm text-slate-500 mb-3">{{ __('driver.upload_invoice_help') }}</p>
            <form method="POST" action="{{ route('driver.request.invoice', $order) }}" enctype="multipart/form-data">
                @csrf
                <input type="file" name="invoice" accept=".pdf,.jpg,.jpeg,.png" required class="block w-full text-sm text-slate-500 file:me-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-sky-50 file:text-sky-700 file:font-semibold">
                @error('invoice')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                <button type="submit" class="mt-3 w-full rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-extrabold py-3">
                    <i class="fa-solid fa-upload me-2"></i>{{ __('driver.upload_invoice_btn') }}
                </button>
            </form>
        </div>
    @endif

    @if ($driverInvoice)
        <div class="mt-6 rounded-3xl bg-white border border-slate-200 shadow-soft p-6">
            <h3 class="font-black text-lg mb-3">{{ __('orders.driver_invoice') }}</h3>
            <a href="{{ asset('storage/' . $driverInvoice->file_path) }}" target="_blank" class="inline-flex items-center gap-2 text-sky-600 hover:underline font-semibold">
                <i class="fa-solid fa-file-pdf"></i> {{ $driverInvoice->original_name ?? __('orders.view_invoice') }}
            </a>
        </div>
    @endif

    <div class="mt-8">
        <a href="{{ route('driver.dashboard') }}" class="px-6 py-3 rounded-2xl border border-slate-200 font-bold inline-block">
            <i class="fa-solid fa-arrow-right me-2"></i>{{ __('orders.back') }}
        </a>
    </div>
</div>
@endsection
