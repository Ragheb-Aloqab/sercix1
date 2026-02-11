@extends('admin.layouts.app')

@section('title', 'الفنيين | SERV.X')
@section('page_title', 'إدارة الفنيين')

@section('content')
    <div class="flex items-center justify-between gap-3 mb-4">
        <form class="flex items-center gap-2" method="GET" action="{{ route('admin.technicians.index') }}">
            <input name="q" value="{{ $q }}"
                class="w-72 max-w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"
                placeholder="بحث بالاسم/الإيميل/الجوال..." />
            <button class="px-3 py-2 rounded-xl border border-slate-200 text-sm font-semibold">{{ __('common.search') }}</button>
        </form>

        <a href="{{ route('admin.technicians.create') }}"
            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">
            <i class="fa-solid fa-plus ms-2"></i> إضافة فني
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="p-3 text-start">الاسم</th>
                    <th class="p-3 text-start">الإيميل</th>
                    <th class="p-3 text-start">الجوال</th>
                    <th class="p-3 text-start">الحالة</th>
                    <th class="p-3 text-start">إجراءات</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @forelse($technicians as $t)
                    @php
                        $active = ($t->status === 'active');
                    @endphp

                    <tr>
                        <td class="p-3 font-semibold">{{ $t->name }}</td>
                        <td class="p-3">{{ $t->email }}</td>
                        <td class="p-3">{{ $t->phone ?? '—' }}</td>

                        <td class="p-3">
                            @if ($active)
                                <span class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold">
                                    نشط
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full bg-red-50 text-red-700 text-xs font-bold">
                                    موقوف
                                </span>
                            @endif
                        </td>

                        <td class="p-3">
                            <div class="flex flex-wrap gap-2">

                                <a href="{{ route('admin.technicians.edit', $t) }}"
                                    class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold">
                                    تعديل
                                </a>

                                <form method="POST" action="{{ route('admin.technicians.toggle', $t) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                        class="px-3 py-2 rounded-xl text-white text-xs font-bold
                                        {{ $active ? 'bg-slate-900 hover:bg-black' : 'bg-emerald-600 hover:bg-emerald-700' }}"
                                        onclick="return confirm('{{ $active ? 'تأكيد إيقاف الفني؟' : 'تأكيد تفعيل الفني؟' }}')">
                                        {{ $active ? 'إيقاف' : 'تفعيل' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.technicians.destroy', $t) }}">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                        class="px-3 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold"
                                        onclick="return confirm('⚠️ حذف نهائي؟ لا يمكن التراجع.');">
                                        حذف
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-500">لا يوجد فنيين.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $technicians->links() }}
    </div>
@endsection
