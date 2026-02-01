@extends('admin.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto p-6 space-y-6">
        @php
            $o = $orderService->order;
            $v = $o?->vehicle;
            $isDone = $o?->status === 'completed';
            $attachments = $o?->attachments ?? collect();
        @endphp

        <div class="bg-white border rounded-2xl p-5">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-black">{{ $orderService->service?->name }}</h1>
                    <p class="text-sm text-slate-500 mt-1">طلب #{{ $orderService->order_id }}</p>
                </div>
                <span
                    class="px-3 py-1 rounded-full border {{ $isDone ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $isDone ? 'تمت' : 'لم تتم' }}
                </span>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div class="bg-white border rounded-2xl p-5">
                <h2 class="font-black mb-3">بيانات السيارة</h2>
                <p>النوع: <b>{{ $v?->type ?? '-' }}</b></p>
                <p>الماركة/الموديل: <b>{{ $v?->make }} {{ $v?->model }}</b></p>
                <p>السنة: <b>{{ $v?->year ?? '-' }}</b></p>
                <p>اللوحة: <b>{{ $v?->plate_number ?? '-' }}</b></p>
            </div>

            <div class="bg-white border rounded-2xl p-5">
                <h2 class="font-black mb-3">ملخص الطلب</h2>
                <p>الشركة: <b>{{ $o?->company?->company_name }}</b></p>
                <p>الحالة: <b>{{ $o?->status }}</b></p>
                <p>العنوان: <b>{{ $o?->city }} - {{ $o?->address }}</b></p>
            </div>
        </div>

        <div class="bg-white border rounded-2xl p-5">
            <h2 class="font-black mb-3">الصور / المرفقات</h2>

            @if ($attachments->isEmpty())
                <p class="text-slate-500">لا يوجد صور مرفوعة لهذا الطلب.</p>
            @else
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach ($attachments as $att)
                        <a href="{{ asset($att->file_path) }}" target="_blank" class="block">
                            <img src="{{ asset($att->file_path) }}" class="w-full h-32 object-cover rounded-xl border">
                            <p class="text-xs text-slate-500 mt-1 line-clamp-1">{{ $att->original_name }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection
