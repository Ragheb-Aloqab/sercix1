@extends('admin.layouts.app')

@section('title', 'المركبات | SERV.X')
@section('page_title', 'المركبات')
@section('subtitle', 'إدارة مركبات الشركة')

@section('content')
    <div class="space-y-6">

        {{-- Header actions --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <form method="GET" action="{{ route('company.vehicles.index') }}" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="بحث: لوحة / ماركة / موديل / VIN"
                    class="w-full lg:w-96 px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900" />
                <button class="px-4 py-3 rounded-2xl bg-slate-900 hover:bg-black text-white font-bold">
                    بحث
                </button>
                @if (!empty($q))
                    <a href="{{ route('company.vehicles.index') }}"
                        class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold">
                        مسح
                    </a>
                @endif
            </form>

            <a href="{{ route('company.vehicles.create') }}"
                class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                <i class="fa-solid fa-plus me-2"></i> إضافة مركبة
            </a>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 text-rose-800 border border-rose-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Table --}}
        <div
            class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
            <div class="p-5 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black">قائمة المركبات</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">إجمالي: {{ $vehicles->total() }}</p>
                </div>
            </div>

            <div class="p-5">
                @if ($vehicles->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-slate-500 dark:text-slate-400">
                                    <th class="text-start py-2">اللوحة</th>
                                    <th class="text-start py-2">المركبة</th>
                                    <th class="text-start py-2">الفرع</th>
                                    <th class="text-start py-2">الحالة</th>
                                    <th class="text-end py-2">إجراءات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800">
                                @foreach ($vehicles as $v)
                                    <tr>
                                        <td class="py-3 font-bold">
                                            {{ $v->plate_number }}
                                        </td>
                                        <td class="py-3">
                                            <div class="font-semibold">
                                                {{ $v->brand ?? '-' }} {{ $v->model ?? '' }}
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                                سنة: {{ $v->year ?? '-' }} — VIN: {{ $v->vin ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            {{ $v->branch?->name ?? '-' }}
                                        </td>
                                        <td class="py-3">
                                            @if ($v->is_active)
                                                <span
                                                    class="px-2 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                                                    نشط
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 py-1 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold dark:bg-slate-800 dark:border-slate-700">
                                                    غير نشط
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-end">
                                            <a href="{{ route('company.vehicles.edit', $v->id) }}"
                                                class="px-3 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 inline-flex items-center gap-2">
                                                <i class="fa-solid fa-pen"></i> تعديل
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $vehicles->links() }}
                    </div>
                @else
                    <p class="text-slate-500">لا توجد مركبات حالياً.</p>
                @endif
            </div>
        </div>

    </div>
@endsection
