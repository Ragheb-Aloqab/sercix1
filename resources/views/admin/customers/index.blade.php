@extends('admin.layouts.app')

@section('title', 'العملاء | SERV.X')
@section('page_title', 'إدارة العملاء')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-4">
        <form class="flex items-center gap-2" method="GET">
            <input name="q" value="{{ $q }}"
                class="w-72 max-w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                placeholder="بحث بالاسم/الإيميل/الجوال..." />
            <button class="px-3 py-2 rounded-xl border border-slate-200 text-sm font-semibold">بحث</button>
        </form>
        <!--
        <a href="{{ route('admin.customers.create') }}"
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">
            <i class="fa-solid fa-plus ms-2"></i> إضافة عميل
        </a> -->
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="p-3 text-start">الاسم</th>
                    <th class="p-3 text-start">الجوال</th>
                    <th class="p-3 text-start">المدينة</th>
                    <th class="p-3 text-start">الحالة</th>
                    <th class="p-3 text-start">إجراءات</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($customers as $customer)
                
                    <tr>
                        <td class="p-3 font-semibold">
                            {{ $customer->company_name }}
                            <div class="text-xs text-slate-500">{{ $customer->email ?? '' }}</div>
                        </td>
                        <td class="p-3">{{ $customer->phone ?? '—' }}</td>
                        <td class="p-3">{{ $customer->city ?? '—' }}</td>
                        <td class="p-3">

                            @if ($customer->status==="active")
                                <span
                                    class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold">نشط</span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-red-50 text-red-700 text-xs font-bold">موقوف</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.customers.edit', $customer) }}"
                                    class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold">
                                    تعديل
                                </a>

                                <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        class="px-3 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold"
                                        onclick="return confirm('حذف العميل نهائياً؟')">
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-500">لا يوجد عملاء.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
