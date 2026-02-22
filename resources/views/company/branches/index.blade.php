@extends('admin.layouts.app')

@section('title', 'فروع الشركة | SERV.X')
@section('page_title', __('dashboard.branches'))

@section('content')
@include('company.partials.glass-start', ['title' => __('dashboard.branches')])
    <div class="space-y-6">

        @if (session('success'))
            <div class="rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50 px-4 py-3 mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-400 mt-1">إدارة الفروع — {{ $company->company_name ?? '' }}</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('company.branches.create') }}"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold transition-colors">
                        <i class="fa-solid fa-plus me-2"></i> إضافة فرع
                    </a>
                </div>
            </div>

            <form method="GET" class="mt-4 flex flex-col sm:flex-row gap-2">
                <input type="text" name="q" value="{{ $q }}"
                    placeholder="ابحث بالاسم/المدينة/الحي/الهاتف/العنوان..."
                    class="w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500" />
                <button class="px-4 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors">
                    بحث
                </button>

                @if ($q)
                    <a href="{{ route('company.branches.index') }}"
                        class="px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold text-center hover:bg-slate-700/50 transition-colors">
                        إلغاء
                    </a>
                @endif
            </form>
        </div>

        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 overflow-hidden">
            <div class="p-5 border-b border-slate-600/50 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-300">قائمة الفروع</h2>
                <p class="text-sm text-slate-500">عدد النتائج: {{ $branches->total() }}</p>
            </div>

            <div class="p-5">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-slate-400 border-b border-slate-600/50">
                                <th class="py-3 text-end font-bold">الاسم</th>
                                <th class="py-3 text-end font-bold">المدينة/الحي</th>
                                <th class="py-3 text-end font-bold">الهاتف</th>
                                <th class="py-3 text-end font-bold">افتراضي</th>
                                <th class="py-3 text-end font-bold">نشط</th>
                                <th class="py-3 text-start font-bold">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-600/50">
                            @forelse($branches as $branch)
                                <tr class="hover:bg-slate-700/30 transition-colors">
                                    <td class="py-4 text-end">
                                        <div class="font-bold text-white">{{ $branch->name }}</div>
                                        @if ($branch->address_line)
                                            <div class="text-xs text-slate-500 mt-1">
                                                {{ $branch->address_line }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="py-4 text-end">
                                        <div class="text-white">{{ $branch->city ?? '-' }}</div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            {{ $branch->district ?? '-' }}</div>
                                    </td>

                                    <td class="py-4 text-end">
                                        <div class="text-white">{{ $branch->phone ?? '-' }}</div>
                                        <div class="text-xs text-slate-500 mt-1">
                                            {{ $branch->email ?? '' }}</div>
                                    </td>

                                    <td class="py-4 text-end">
                                        @if ($branch->is_default)
                                            <span class="px-3 py-1 rounded-xl bg-emerald-500/30 text-emerald-300 border border-emerald-400/50 text-xs font-bold">Default</span>
                                        @else
                                            <span class="px-3 py-1 rounded-xl border border-slate-500/50 text-slate-400 text-xs font-bold">—</span>
                                        @endif
                                    </td>

                                    <td class="py-4 text-end">
                                        @if ($branch->is_active)
                                            <span class="px-3 py-1 rounded-xl bg-sky-500/30 text-sky-300 border border-sky-400/50 text-xs font-bold">Active</span>
                                        @else
                                            <span class="px-3 py-1 rounded-xl bg-red-500/30 text-red-300 border border-red-400/50 text-xs font-bold">Inactive</span>
                                        @endif
                                    </td>

                                    <td class="py-4">
                                        <a href="{{ route('company.branches.edit', $branch) }}"
                                            class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-slate-500/50 text-white font-semibold hover:bg-slate-700/50 transition-colors">
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
@include('company.partials.glass-end')
@endsection
