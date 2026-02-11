{{-- resources/views/admin/services/index.blade.php --}}
@extends('admin.layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto p-6">

        {{-- Header --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black">
                    الخدمات
                    @if (!empty($company))
                        : <span class="text-emerald-700">{{ $company->company_name }}</span>
                    @endif
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    إدارة الخدمات العامة
                    @if (!empty($company))
                        + خدمات الشركة المختارة (company_services)
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.services.create') }}"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                    <i class="fa-solid fa-plus me-2"></i> إضافة خدمة
                </a>
            </div>
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mt-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-5 p-3 rounded-xl bg-red-50 border border-red-200 text-red-800">
                <p class="font-bold mb-2">يوجد أخطاء:</p>
                <ul class="list-disc ms-6">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Filters --}}
        <div class="mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4">
            <form method="GET" action="{{ route('admin.services.index') }}"
                class="grid grid-cols-1 lg:grid-cols-12 gap-3">

                <div class="lg:col-span-6">
                    <label class="text-xs text-slate-500">{{ __('common.search') }}</label>
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="ابحث باسم الخدمة..."
                        class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                </div>

                <div class="lg:col-span-3">
                    <label class="text-xs text-slate-500">الحالة</label>
                    <select name="status"
                        class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                        <option value="all" @selected(($status ?? 'all') === 'all')>الكل</option>
                        <option value="active" @selected(($status ?? 'all') === 'active')>مفعّلة</option>
                        <option value="inactive" @selected(($status ?? 'all') === 'inactive')>معطّلة</option>
                    </select>
                </div>

                <div class="lg:col-span-3 flex items-end gap-2">
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold">
                        <i class="fa-solid fa-magnifying-glass me-2"></i> تطبيق
                    </button>

                    <a href="{{ route('admin.services.index') }}"
                        class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">
                        إعادة
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div
            class="mt-6 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <p class="font-bold">قائمة الخدمات</p>
                <p class="text-sm text-slate-500">
                    {{ $services->total() }} خدمة
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr class="text-slate-600 dark:text-slate-300">
                            <th class="text-start px-4 py-3">#</th>
                            <th class="text-start px-4 py-3">الخدمة</th>
                            <th class="text-start px-4 py-3">السعر الأساسي</th>
                            <th class="text-start px-4 py-3">المدة (دقيقة)</th>
                            <th class="text-start px-4 py-3">الحالة</th>

                            {{-- أعمدة الشركة (pivot) فقط إذا الشركة موجودة --}}
                            @if (!empty($company))
                                <th class="text-start px-4 py-3">سعر الشركة</th>
                                <th class="text-start px-4 py-3">وقت الشركة</th>
                                <th class="text-start px-4 py-3">مفعّلة للشركة</th>
                            @endif

                            <th class="text-end px-4 py-3">إجراءات</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($services as $service)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-950/40">
                                <td class="px-4 py-3 text-slate-500">{{ $service->id }}</td>

                                <td class="px-4 py-3">
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $service->name }}</p>
                                    @if (!empty($service->description))
                                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $service->description }}</p>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    <span
                                        class="font-semibold">{{ number_format((float) ($service->base_price ?? 0), 2) }}</span>
                                    <span class="text-xs text-slate-500">SAR</span>
                                </td>

                                <td class="px-4 py-3">
                                    {{ $service->estimated_minutes ?? ($service->duration_minutes ?? '-') }}
                                </td>

                                <td class="px-4 py-3">
                                    @if ($service->is_active)
                                        <span
                                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> مفعّلة
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 text-red-700 border border-red-200">
                                            <span class="w-2 h-2 rounded-full bg-red-500"></span> معطّلة
                                        </span>
                                    @endif
                                </td>

                                {{-- Pivot info --}}
                                @if (!empty($company))
                                    @php
                                        // إذا استخدمت with(['companies' => fn...]) في الكنترولر:
                                        $pivotCompany = $service->companies->first();
                                        $pivot = $pivotCompany?->pivot;
                                    @endphp

                                    <td class="px-4 py-3">
                                        {{ $pivot?->base_price !== null ? number_format((float) $pivot->base_price, 2) : '—' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ $pivot?->estimated_minutes ?? '—' }}
                                    </td>

                                    <td class="px-4 py-3">
                                        @if (($pivot?->is_enabled ?? false) === true)
                                            <span
                                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-50 text-sky-700 border border-sky-200">
                                                <span class="w-2 h-2 rounded-full bg-sky-500"></span> نعم
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200">
                                                <span class="w-2 h-2 rounded-full bg-slate-400"></span> لا
                                            </span>
                                        @endif
                                    </td>
                                @endif

                                {{-- Actions --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">

                                        {{-- Edit --}}
                                        <a href="{{ route('admin.services.edit', $service) }}"
                                            class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">
                                            <i class="fa-solid fa-pen-to-square me-1"></i> تعديل
                                        </a>

                                        {{-- Disable instead of delete --}}
                                        <form method="POST" action="{{ route('admin.services.toggle', $service) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                class="px-3 py-2 rounded-xl text-white font-semibold
                                                         {{ $service->is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700' }}"
                                                onclick="return confirm('{{ $service->is_active ? 'تأكيد تعطيل الخدمة؟' : 'تأكيد تفعيل الخدمة؟' }}')">

                                                <i
                                                    class="fa-solid {{ $service->is_active ? 'fa-ban' : 'fa-circle-check' }} me-1"></i>
                                                {{ $service->is_active ? 'تعطيل' : 'تفعيل' }}
                                            </button>
                                        </form>
                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('admin.services.destroy', $service) }}">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="px-3 py-2 rounded-xl bg-slate-900 hover:bg-black text-white font-semibold"
                                                onclick="return confirm('⚠️ حذف نهائي؟ لن يمكنك استرجاع الخدمة بعد الحذف.');">
                                                <i class="fa-solid fa-trash me-1"></i> حذف
                                            </button>
                                        </form>


                                        {{-- Company pivot toggle (اختياري) --}}
                                        @if (!empty($company))
                                            {{--
                                            ✅ هذا يحتاج route + controller لعمل toggle على company_services.is_enabled
                                            مثال route:
                                            PATCH dashboard/companies/{company}/services/{service}
                                            name: dashboard.companies.services.update
                                        --}}
                                            @if (\Illuminate\Support\Facades\Route::has('dashboard.companies.services.update'))
                                                <form method="POST"
                                                    action="{{ route('admin.companies.services.update', [$company, $service]) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="is_enabled"
                                                        value="{{ $pivot?->is_enabled ?? false ? 0 : 1 }}">
                                                    <button type="submit"
                                                        class="px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold">
                                                        <i
                                                            class="fa-solid fa-toggle-{{ $pivot?->is_enabled ?? false ? 'on' : 'off' }} me-1"></i>
                                                        {{ $pivot?->is_enabled ?? false ? 'إيقاف للشركة' : 'تفعيل للشركة' }}
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ !empty($company) ? 9 : 6 }}"
                                    class="px-4 py-10 text-center text-slate-500">
                                    لا توجد خدمات مطابقة للبحث.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-800">
                {{ $services->links() }}
            </div>
        </div>

    </div>
@endsection
