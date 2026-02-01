@extends('admin.layouts.app')

@section('title', 'الفنيين | SERV.X')
@section('page_title', 'الفنيين')

@section('content')
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
        <div class="p-5 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">إدارة</p>
                <h2 class="text-lg font-black">قائمة الفنيين</h2>
            </div>
            <button class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">
                <i class="fa-solid fa-user-plus me-2"></i> إضافة فني
            </button>
        </div>

        <div class="p-5 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="text-slate-500 dark:text-slate-400">
                    <tr class="text-start">
                        <th class="py-3 font-semibold">الاسم</th>
                        <th class="py-3 font-semibold">الحالة</th>
                        <th class="py-3 font-semibold">آخر موقع</th>
                        <th class="py-3 font-semibold">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800">
                    <tr>
                        <td class="py-4 font-bold">فني #1</td>
                        <td class="py-4"><span
                                class="px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-700 text-xs font-bold">متاح</span>
                        </td>
                        <td class="py-4 text-slate-600 dark:text-slate-300">الرياض - العليا</td>
                        <td class="py-4">
                            <button
                                class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-semibold">
                                تتبع
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-4 font-bold">فني #2</td>
                        <td class="py-4"><span
                                class="px-2.5 py-1 rounded-full bg-amber-500/10 text-amber-800 text-xs font-bold">مشغول</span>
                        </td>
                        <td class="py-4 text-slate-600 dark:text-slate-300">الرياض - النرجس</td>
                        <td class="py-4">
                            <button
                                class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-semibold">
                                تفاصيل
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
