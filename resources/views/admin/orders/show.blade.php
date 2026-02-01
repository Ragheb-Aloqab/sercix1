@extends('admin.layouts.app')

@section('title', 'تفاصيل الطلب #' . $order->id)

@section('content')
    <div class="space-y-4">

        @if (session('success'))
            <div
                class="p-4 rounded-2xl bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 border border-emerald-500/20">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 text-rose-800 dark:text-rose-300 border border-rose-500/20">
                <p class="font-bold mb-2">يوجد أخطاء:</p>
                <ul class="list-disc ps-5">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">تفاصيل الطلب</p>
                    <h1 class="text-2xl font-black">#{{ $order->id }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        تم الإنشاء: {{ $order->created_at?->format('Y-m-d H:i') }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @include('admin.orders.partials._status_badge', ['status' => $order->status])

                    <a href="{{ route('admin.orders.index') }}"
                        class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">
                        رجوع للقائمة
                    </a>

                    <a href="{{ route('admin.orders.invoice.show', $order) }}"
                        class="px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold">
                        <i class="fa-solid fa-print me-2"></i> الفاتورة
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

            <div class="xl:col-span-2 space-y-4">

                @include('admin.orders.partials._order_summary', ['order' => $order])


                @include('admin.orders.partials._services', ['order' => $order])


                @include('admin.orders.partials._timeline', ['order' => $order])


                @include('admin.orders.partials._attachments', ['order' => $order])

            </div>

            {{-- Right: Actions --}}
            <div class="space-y-4">
                {{-- Assign technician --}}
                @include('admin.orders.partials._assign_technician', ['order' => $order])

                {{-- Change status --}}
                @include('admin.orders.partials._change_status', ['order' => $order])

                {{-- Payment --}}
                @include('admin.orders.partials._payment', ['order' => $order])

                {{-- Quick info --}}
                @include('admin.orders.partials._quick_info', ['order' => $order])
            </div>
        </div>
    </div>
@endsection
