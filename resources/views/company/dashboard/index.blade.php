@extends('admin.layouts.app')

@section('title', 'لوحة تحكم الشركة | ' . ($siteName ?? 'SERV.X'))
@section('page_title', 'لوحة تحكم الشركة')

@section('content')
    <div class="space-y-6">
        {{-- Welcome --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-6">
            <h1 class="text-2xl font-black">
                مرحبًا 👋 {{ $company->company_name }}
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                هذه لوحة تحكم شركتك – يمكنك متابعة الطلبات، الفواتير، الفروع، والخدمات.
            </p>
        </div>

        {{-- Quick Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <p class="text-sm text-slate-500">الطلبات</p>
                <p class="text-3xl font-black mt-2">{{ $company->orders->count() }}</p>
            </div>
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <p class="text-sm text-slate-500">الفواتير</p>
                <p class="text-3xl font-black mt-2">{{ $company->invoices->count() }}</p>
            </div>
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <p class="text-sm text-slate-500">الفروع</p>
                <p class="text-3xl font-black mt-2">{{ $company->branches->count() }}</p>
            </div>
        </div>

        {{-- Fleet overview cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-6 border border-slate-200/70 dark:border-slate-800 border-e-4 border-e-sky-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">عدد السيارات</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100 mt-2">{{ $company->vehicles()->count() }}</h3>
                        <p class="text-emerald-600 text-sm mt-2 flex items-center gap-1">
                            <span class="bg-emerald-100 dark:bg-emerald-900/30 rounded-full p-1">
                                <i class="fa-solid fa-arrow-up text-emerald-600 text-xs"></i>
                            </span>
                            الأسطول بالكامل
                        </p>
                    </div>
                    <div class="bg-sky-100 dark:bg-sky-900/30 p-3 rounded-lg">
                        <i class="fa-solid fa-truck-fast text-sky-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-6 border border-slate-200/70 dark:border-slate-800 border-e-4 border-e-orange-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">إجمالي تكلفة الصيانة</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100 mt-2">{{ number_format($company->maintenanceCost()) }} <span class="text-lg">ر.س</span></h3>
                        @if($company->vehicles()->count() > 0)
                            <p class="text-slate-600 dark:text-slate-300 text-sm mt-2">متوسط التكلفة لكل سيارة {{ number_format($company->maintenanceCost() / $company->vehicles()->count(), 0) }} ر.س</p>
                        @endif
                    </div>
                    <div class="bg-orange-100 dark:bg-orange-900/30 p-3 rounded-lg">
                        <i class="fa-solid fa-wrench text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-6 border border-slate-200/70 dark:border-slate-800 border-e-4 border-e-emerald-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">إجمالي تكلفة المحروقات</p>
                        <h3 class="text-3xl font-bold text-slate-800 dark:text-slate-100 mt-2">{{ number_format($company->fuelsCost()) }} <span class="text-lg">ر.س</span></h3>
                        <div class="mt-4">
                            <p class="text-slate-500 dark:text-slate-400 text-sm">إجمالي التكلفة</p>
                            <h4 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($company->totalActualCost()) }} <span class="text-lg">ر.س</span></h4>
                        </div>
                        @if($company->otherCost() > 0)
                            <div class="mt-4">
                                <p class="text-slate-500 dark:text-slate-400 text-sm">أخرى</p>
                                <h4 class="text-2xl font-bold text-slate-800 dark:text-slate-100">{{ number_format($company->otherCost()) }} <span class="text-lg">ر.س</span></h4>
                            </div>
                        @endif
                    </div>
                    <div class="bg-emerald-100 dark:bg-emerald-900/30 p-3 rounded-lg">
                        <i class="fa-solid fa-gas-pump text-emerald-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-6 border border-slate-200/70 dark:border-slate-800 border-e-4 border-e-violet-500">
                <div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">مقارنة التكلفة</p>
                    <div class="mt-4 space-y-4">
                        <div>
                            <div class="flex justify-between">
                                <span class="text-slate-700 dark:text-slate-300">التكلفة اليومية</span>
                                <span class="font-bold">{{ number_format($company->dailyCost()) }} <span class="text-sm">ر.س</span></span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 mt-1">
                                <div class="bg-violet-600 h-2 rounded-full" style="width: {{ min($company->dailyProgressPercentage(), 100) }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between">
                                <span class="text-slate-700 dark:text-slate-300">التكلفة الشهرية</span>
                                <span class="font-bold">{{ number_format($company->monthlyCost()) }} <span class="text-sm">ألف ر.س</span></span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 mt-1">
                                <div class="bg-sky-600 h-2 rounded-full" style="width: {{ min($company->monthlyProgressPercentage(), 100) }}%"></div>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                            <p class="text-slate-700 dark:text-slate-300 text-sm">مقارنة بـ 7 أشهر سابقة</p>
                            @php
                                $percentage = $company->lastSevenMonthsPercentage();
                                $limit = 5;
                            @endphp
                            @if($percentage > $limit)
                                <p class="text-emerald-600 text-sm mt-1 flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-up"></i>
                                    أعلى بنسبة {{ number_format($percentage, 2) }}%
                                </p>
                            @elseif($percentage < -$limit)
                                <p class="text-red-600 text-sm mt-1 flex items-center gap-1">
                                    <i class="fa-solid fa-arrow-down"></i>
                                    أقل بنسبة {{ number_format(abs($percentage), 2) }}%
                                </p>
                            @else
                                <p class="text-slate-500 text-sm mt-1">مستقر (±{{ number_format(abs($percentage), 2) }}%)</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Top 5 vehicles by cost --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-6 border border-slate-200/70 dark:border-slate-800">
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-6">أعلى خمس سيارات تكلفة</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-end">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700">
                                <th class="pb-3 text-slate-600 dark:text-slate-400 font-medium">رقم السيارة</th>
                                <th class="pb-3 text-slate-600 dark:text-slate-400 font-medium">النوع</th>
                                <th class="pb-3 text-slate-600 dark:text-slate-400 font-medium">التكلفة</th>
                                <th class="pb-3 text-slate-600 dark:text-slate-400 font-medium">النسبة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($company->getTopVehiclesByServiceConsumptionAndCost() as $v)
                                <tr class="border-b border-slate-100 dark:border-slate-800 last:border-0">
                                    <td class="py-4">
                                        <div class="font-medium text-slate-900 dark:text-slate-100">{{ $v->make }} {{ $v->model }}</div>
                                        <div class="text-xs text-slate-500">{{ $v->plate_number }}</div>
                                    </td>
                                    <td class="py-4 text-slate-600 dark:text-slate-400">{{ $v->services_count }} خدمة</td>
                                    <td class="py-4 font-bold text-slate-900 dark:text-slate-100">{{ number_format($v->total_service_cost, 2) }} ر.س</td>
                                    <td class="py-4">
                                        <span class="bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 text-xs font-semibold px-2.5 py-0.5 rounded">
                                            {{ number_format($v->percentage, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-500">لا توجد بيانات لعرضها حالياً</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @php $summary = $company->getTop5VehiclesSummary(); @endphp
                <div class="mt-4 text-sm text-slate-500">
                    <p>
                        مجموع تكلفة الخمس سيارات:
                        {{ number_format($summary['top_total'], 2) }} ر.س
                        ({{ $summary['ui_percentage'] }}% من إجمالي التكلفة)
                    </p>
                </div>
            </div>

            {{-- Fleet performance indicators --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl shadow-md p-6 border border-slate-200/70 dark:border-slate-800">
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-100 mb-6">مؤشرات أداء الأسطول</h2>
                @php
                    $indicatorUI = function ($direction) {
                        return match($direction) {
                            'up' => ['textClass' => 'text-green-600', 'barClass' => 'bg-green-600', 'text' => 'أعلى من المعتاد', 'icon' => '↑'],
                            'down' => ['textClass' => 'text-red-600', 'barClass' => 'bg-red-600', 'text' => 'أقل من المعتاد', 'icon' => '↓'],
                            default => ['textClass' => 'text-sky-600', 'barClass' => 'bg-blue-600', 'text' => 'مستقر', 'icon' => '→'],
                        };
                    };
                    $maintenance = $company->maintenanceCostIndicator();
                    $fuel = $company->fuelConsumptionIndicator();
                    $operating = $company->operatingCostIndicator();
                    $mUI = $indicatorUI($maintenance['direction']);
                    $fUI = $indicatorUI($fuel['direction']);
                    $oUI = $indicatorUI($operating['direction']);
                @endphp
                <div class="space-y-6">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-medium text-slate-700 dark:text-slate-300">تكلفة الصيانة</h3>
                            <span class="{{ $mUI['textClass'] }} text-sm font-bold flex items-center gap-1">
                                {{ $mUI['text'] }} <span>{{ $mUI['icon'] }}</span>
                            </span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-4">
                            <div class="{{ $mUI['barClass'] }} h-4 rounded-full" style="width: {{ min($maintenance['percent'], 100) }}%"></div>
                        </div>
                        <p class="text-slate-500 text-sm mt-2">
                            {{ $mUI['text'] }} من المعدل المعتاد بنسبة {{ $maintenance['percent'] }}%
                        </p>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-medium text-slate-700 dark:text-slate-300">استهلاك الوقود</h3>
                            <span class="{{ $fUI['textClass'] }} text-sm font-bold flex items-center gap-1">
                                {{ $fUI['text'] }} {{ $fUI['icon'] }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-4">
                            <div class="{{ $fUI['barClass'] }} h-4 rounded-full" style="width: {{ min($fuel['percent'], 100) }}%"></div>
                        </div>
                        <p class="text-slate-500 text-sm mt-2">
                            {{ $fUI['text'] }} من المعدل المعتاد بنسبة {{ $fuel['percent'] }}%
                        </p>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="font-medium text-slate-700 dark:text-slate-300">التكلفة التشغيلية الإجمالية</h3>
                            <span class="{{ $oUI['textClass'] }} text-sm font-bold flex items-center gap-1">
                                {{ $oUI['text'] }} {{ $oUI['icon'] }}
                            </span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-4">
                            <div class="{{ $oUI['barClass'] }} h-4 rounded-full" style="width: {{ min($operating['percent'], 100) }}%"></div>
                        </div>
                        <p class="text-slate-500 text-sm mt-2">
                            {{ $oUI['text'] }} من المعدل المعتاد بنسبة {{ $operating['percent'] }}%
                        </p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl mt-6">
                        <h4 class="font-medium text-slate-700 dark:text-slate-300 mb-2">ملخص المؤشرات</h4>
                        <ul class="text-slate-600 dark:text-slate-400 text-sm space-y-1">
                            <li class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                                <span>أداء الصيانة {{ $maintenance['direction'] === 'down' ? 'جيد' : 'يتطلب تحسين' }}</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                <span>استهلاك الوقود يحتاج لمراجعة وتحسين الكفاءة</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-sky-500 rounded-full"></span>
                                <span>التكلفة التشغيلية الإجمالية في المعدل المقبول</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <footer class="text-center text-slate-500 dark:text-slate-400 text-sm py-4">
            لوحة تحكم أسطول السيارات — آخر تحديث: {{ now()->format('Y-m-d') }}
        </footer>

        {{-- Quick Actions --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-6">
            <h2 class="text-lg font-black mb-4">إجراءات سريعة</h2>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('company.orders.index') }}" class="px-4 py-3 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-bold">
                    <i class="fa-solid fa-receipt me-2"></i> الطلبات
                </a>
                <a href="{{ route('company.invoices.index') }}" class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                    <i class="fa-solid fa-file-invoice me-2"></i> الفواتير
                </a>
                <a href="{{ route('company.branches.index') }}" class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold hover:bg-slate-50 dark:hover:bg-slate-800">
                    <i class="fa-solid fa-code-branch me-2"></i> الفروع
                </a>
            </div>
        </div>
    </div>
@endsection
