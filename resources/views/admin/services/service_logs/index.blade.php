@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-black">سجل الخدمات</h1>
            <p class="text-sm text-slate-500">الخدمات المنفّذة وغير المنفّذة حسب حالة الطلب</p>
        </div>
    </div>

    <div class="mt-6 bg-white border rounded-2xl p-4">
        <form method="GET" class="grid grid-cols-1 lg:grid-cols-12 gap-3">
            <div class="lg:col-span-7">
                <label class="text-xs text-slate-500">بحث</label>
                <input name="q" value="{{ $q }}" class="mt-1 w-full rounded-xl border-slate-200" placeholder="اسم الخدمة أو لوحة السيارة">
            </div>

            <div class="lg:col-span-3">
                <label class="text-xs text-slate-500">الحالة</label>
                <select name="done" class="mt-1 w-full rounded-xl border-slate-200">
                    <option value="all" @selected($done==='all')>الكل</option>
                    <option value="done" @selected($done==='done')>تمت</option>
                    <option value="not_done" @selected($done==='not_done')>لم تتم</option>
                </select>
            </div>

            <div class="lg:col-span-2 flex items-end">
                <button class="w-full px-4 py-2 rounded-xl bg-slate-900 text-white font-semibold">تطبيق</button>
            </div>
        </form>
    </div>

    <div class="mt-6 bg-white border rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="text-start px-4 py-3">الخدمة</th>
                        <th class="text-start px-4 py-3">الطلب</th>
                        <th class="text-start px-4 py-3">الشركة</th>
                        <th class="text-start px-4 py-3">السيارة</th>
                        <th class="text-start px-4 py-3">الحالة</th>
                        <th class="text-end px-4 py-3">تفاصيل</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($rows as $row)
                        @php($isDone = $row->order?->status === 'completed')
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-bold">{{ $row->service?->name }}</td>
                            <td class="px-4 py-3">#{{ $row->order_id }}</td>
                            <td class="px-4 py-3">{{ $row->order?->company?->company_name }}</td>
                            <td class="px-4 py-3">
                                {{ $row->order?->vehicle?->make }} {{ $row->order?->vehicle?->model }}
                                - {{ $row->order?->vehicle?->plate_number }}
                            </td>
                            <td class="px-4 py-3">
                                @if($isDone)
                                    <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border">تمت</span>
                                @else
                                    <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 border">لم تتم</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end">
                                <a href="{{ route('dashboard.service_logs.show', $row) }}"
                                   class="px-3 py-2 rounded-xl border hover:bg-slate-50 font-semibold">
                                   عرض
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t">
            {{ $rows->links() }}
        </div>
    </div>
</div>
@endsection
