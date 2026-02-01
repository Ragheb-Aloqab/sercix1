@extends('admin.layouts.app')

@section('title', 'المخزون | SERV.X')
@section('page_title', 'إدارة المخزون')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-4">
        <form class="flex items-center gap-2" method="GET">
            <input name="q" value="{{ $q }}"
                class="w-72 max-w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                placeholder="بحث بالاسم/sku/التصنيف..." />
            <button class="px-3 py-2 rounded-xl border border-slate-200 text-sm font-semibold">بحث</button>
        </form>

        <a href="{{ route('admin.inventory.create') }}"
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">
            <i class="fa-solid fa-plus ms-2"></i> إضافة عنصر
        </a>
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
                    <th class="p-3 text-start">العنصر</th>
                    <th class="p-3 text-start">SKU</th>
                    <th class="p-3 text-start">التصنيف</th>
                    <th class="p-3 text-start">الكمية</th>
                    <th class="p-3 text-start">السعر</th>
                    <th class="p-3 text-start">الحالة</th>
                    <th class="p-3 text-start">إجراءات</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($items as $it)
                    <tr>
                        <td class="p-3 font-semibold">{{ $it->name }}</td>
                        <td class="p-3">{{ $it->sku ?? '—' }}</td>
                        <td class="p-3">{{ $it->category ?? '—' }}</td>
                        <td class="p-3">
                            <span class="font-bold">{{ $it->qty }}</span>
                            @if ($it->min_qty && $it->qty <= $it->min_qty)
                                <span class="ms-2 text-xs font-bold text-red-600">Low</span>
                            @endif
                        </td>
                        <td class="p-3">{{ number_format((float) $it->unit_price, 2) }} SAR</td>
                        <td class="p-3">
                            @if ($it->is_active)
                                <span
                                    class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold">نشط</span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-red-50 text-red-700 text-xs font-bold">موقوف</span>
                            @endif
                        </td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.inventory.edit', $it) }}"
                                    class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold">
                                    تعديل
                                </a>

                                <form method="POST" action="{{ route('admin.inventory.destroy', $it) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        class="px-3 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold"
                                        onclick="return confirm('حذف العنصر نهائياً؟')">
                                        حذف
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-slate-500">لا يوجد عناصر.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
@endsection
