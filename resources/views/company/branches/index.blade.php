@extends('admin.layouts.app')

@section('title', 'فروع الشركة | SERV.X')
@section('page_title', 'فروع الشركة')

@section('content')
    <div class="space-y-6">

        {{-- Flash --}}
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        {{-- Header + Actions --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-black">
                        فروع الشركة
                        <span class="text-emerald-700">{{ $company->company_name ?? '' }}</span>
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        إدارة الفروع (إضافة/تعديل/تعيين الافتراضي)
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('company.branches.create') }}"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                        <i class="fa-solid fa-plus me-2"></i> إضافة فرع
                    </a>
                </div>
            </div>

            {{-- Search --}}
            <form method="GET" class="mt-4 flex flex-col sm:flex-row gap-2">
                <input type="text" name="q" value="{{ $q }}"
                    placeholder="ابحث بالاسم/المدينة/الحي/الهاتف/العنوان..."
                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
                <button class="px-4 py-3 rounded-2xl bg-slate-900 hover:bg-black text-white font-bold">
                    بحث
                </button>

                @if ($q)
                    <a href="{{ route('company.branches.index') }}"
                        class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold text-center">
                        إلغاء
                    </a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div
            class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
            <div class="p-5 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black">قائمة الفروع</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">عدد النتائج: {{ $branches->total() }}</p>
                </div>
            </div>

            <div class="p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-start text-slate-500 dark:text-slate-400">
                                <th class="py-3">الاسم</th>
                                <th class="py-3">المدينة/الحي</th>
                                <th class="py-3">الهاتف</th>
                                <th class="py-3">افتراضي</th>
                                <th class="py-3">نشط</th>
                                <th class="py-3 text-end">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800">
                            @forelse($branches as $branch)
                                <tr>
                                    <td class="py-4">
                                        <div class="font-bold">{{ $branch->name }}</div>
                                        @if ($branch->address_line)
                                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                {{ $branch->address_line }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="py-4">
                                        <div>{{ $branch->city ?? '-' }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            {{ $branch->district ?? '-' }}</div>
                                    </td>

                                    <td class="py-4">
                                        <div>{{ $branch->phone ?? '-' }}</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                            {{ $branch->email ?? '' }}</div>
                                    </td>

                                    <td class="py-4">
                                        @if ($branch->is_default)
                                            <span
                                                class="px-3 py-1 rounded-xl bg-emerald-600 text-white text-xs font-bold">Default</span>
                                        @else
                                            <span
                                                class="px-3 py-1 rounded-xl border border-slate-200 dark:border-slate-800 text-xs font-bold">—</span>
                                        @endif
                                    </td>

                                    <td class="py-4">
                                        @if ($branch->is_active)
                                            <span
                                                class="px-3 py-1 rounded-xl bg-sky-600 text-white text-xs font-bold">Active</span>
                                        @else
                                            <span
                                                class="px-3 py-1 rounded-xl bg-rose-600 text-white text-xs font-bold">Inactive</span>
                                        @endif
                                    </td>

                                    <td class="py-4 text-end">
                                        <a href="{{ route('company.branches.edit', $branch) }}"
                                            class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">
                                            <i class="fa-solid fa-pen-to-square me-2"></i> تعديل
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-500">
                                        لا توجد فروع حالياً.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $branches->links() }}
                </div>
            </div>
        </div>

    </div>
@endsection
