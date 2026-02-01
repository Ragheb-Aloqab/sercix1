@extends('admin.layouts.app')

@section('title', 'لوحة الفني | SERV.X')
@section('page_title', 'لوحة تحكم الفني')

@section('content')
    <div class="space-y-6">

        {{-- Alerts --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl border border-emerald-200/70 bg-emerald-50 text-emerald-800 flex items-start gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="flex-1">
                    <p class="font-black">تمت العملية بنجاح</p>
                    <p class="text-sm mt-1">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl border border-rose-200/70 bg-rose-50 text-rose-800 flex items-start gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="flex-1">
                    <p class="font-black">حدث خطأ</p>
                    <p class="text-sm mt-1">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @php
                $kpiCard = 'rounded-3xl bg-white/80 dark:bg-slate-900/70 backdrop-blur border border-slate-200/70 dark:border-slate-800 shadow-soft p-5';
                $kpiLabel = 'text-sm text-slate-500 dark:text-slate-400';
                $kpiValue = 'text-3xl font-black mt-1 tracking-tight';
            @endphp

            <div class="{{ $kpiCard }}">
                <div class="flex items-center justify-between">
                    <p class="{{ $kpiLabel }}">مهام اليوم</p>
                    <span class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 flex items-center justify-center">
                    <i class="fa-solid fa-bolt"></i>
                </span>
                </div>
                <p class="{{ $kpiValue }}">{{ $kpis['today_tasks'] }}</p>
                <p class="text-xs text-slate-500 mt-1">المهام المطلوب تنفيذها اليوم</p>
            </div>

            <div class="{{ $kpiCard }}">
                <div class="flex items-center justify-between">
                    <p class="{{ $kpiLabel }}">بانتظار</p>
                    <span class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 flex items-center justify-center">
                    <i class="fa-solid fa-hourglass-half"></i>
                </span>
                </div>
                <p class="{{ $kpiValue }}">{{ $kpis['pending'] }}</p>
                <p class="text-xs text-slate-500 mt-1">لم يتم بدءها بعد</p>
            </div>

            <div class="{{ $kpiCard }}">
                <div class="flex items-center justify-between">
                    <p class="{{ $kpiLabel }}">قيد التنفيذ</p>
                    <span class="w-11 h-11 rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300 flex items-center justify-center">
                    <i class="fa-solid fa-person-walking"></i>
                </span>
                </div>
                <p class="{{ $kpiValue }}">{{ $kpis['in_progress'] }}</p>
                <p class="text-xs text-slate-500 mt-1">العمل جارٍ عليها</p>
            </div>

            <div class="{{ $kpiCard }}">
                <div class="flex items-center justify-between">
                    <p class="{{ $kpiLabel }}">مكتملة</p>
                    <span class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-800 dark:bg-white/10 dark:text-white flex items-center justify-center">
                    <i class="fa-solid fa-check"></i>
                </span>
                </div>
                <p class="{{ $kpiValue }}">{{ $kpis['completed'] }}</p>
                <p class="text-xs text-slate-500 mt-1">تم إنهاؤها بنجاح</p>
            </div>
        </div>

        {{-- Latest Tasks --}}
        <div class="rounded-3xl bg-white/80 dark:bg-slate-900/70 backdrop-blur border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
            <div class="p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-black">آخر المهام</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">تابع مهامك الأخيرة ونفّذها بسرعة</p>
                </div>

                <a href="{{ route('tech.tasks.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl border border-slate-200 dark:border-slate-800
                      hover:bg-slate-100 dark:hover:bg-slate-800 text-sm font-bold transition">
                    <i class="fa-solid fa-list-check"></i>
                    كل المهام
                </a>
            </div>

            <div class="border-t border-slate-200/70 dark:border-slate-800">
                @forelse ($latestTasks as $o)
                    @php
                        $status = strtolower((string) $o->status);

                        $map = [
                            'pending'     => ['بانتظار',   'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300', 'w-2 bg-amber-400'],
                            'in_progress' => ['قيد التنفيذ','bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',     'w-2 bg-sky-400'],
                            'completed'   => ['مكتملة',    'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300','w-2 bg-emerald-400'],
                            'cancelled'   => ['ملغاة',     'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',  'w-2 bg-rose-400'],
                            'rejected'    => ['مرفوضة',    'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',  'w-2 bg-rose-400'],
                        ];

                        $label = $map[$status][0] ?? ($o->status ?? '—');
                        $badge = $map[$status][1] ?? 'bg-slate-100 text-slate-800 dark:bg-white/10 dark:text-white';
                        $bar   = $map[$status][2] ?? 'w-2 bg-slate-300';

                        $progress = match ($status) {
                            'pending' => 20,
                            'in_progress' => 60,
                            'completed' => 100,
                            default => 35,
                        };

                        $companyName = $o->company?->company_name ?? '-';
                        $companyPhone = $o->company?->phone ?? null;
                    @endphp

                    <div class="relative">
                        <div class="absolute inset-y-0 end-0 {{ $bar }}"></div>

                        <div class="p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                            {{-- Left / Info --}}
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-sky-500 text-white flex items-center justify-center font-black shadow-soft">
                                    <i class="fa-solid fa-wrench"></i>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-black truncate">طلب #{{ $o->id }}</p>
                                        <span class="px-3 py-1 rounded-full text-xs font-black {{ $badge }}">{{ $label }}</span>
                                    </div>

                                    {{-- الشركة والهاتف --}}
                                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                        الشركة: <span class="font-bold text-slate-700 dark:text-slate-200">{{ $companyName }}</span>
                                        @if ($companyPhone)
                                            <span class="mx-2 text-slate-300">•</span>
                                            <span class="font-bold">{{ $companyPhone }}</span>
                                        @endif
                                    </p>

                                    {{-- التاريخ والوقت --}}
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">
                                        {{ $o->created_at?->format('Y-m-d H:i') }}
                                    </p>

                                    {{-- شريط التقدم --}}
                                    <div class="mt-3">
                                        <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                                            <span>تقدم المهمة</span>
                                            <span class="font-bold">{{ $progress }}%</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                            <div class="h-full rounded-full bg-slate-900 dark:bg-white/80" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Right / Actions --}}
                            {{-- Right / Actions --}}
                            <div class="flex flex-wrap items-center justify-end gap-2">
                                {{-- View --}}
                                <a href="{{ route('tech.tasks.show', $o->id) }}"
                                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl
          bg-slate-900 text-white dark:bg-white dark:text-slate-900
          hover:opacity-90 text-sm font-black transition">
                                    <i class="fa-solid fa-eye"></i>
                                    عرض
                                </a>

                                @if($status !== 'completed')
                                    {{-- Accept --}}
                                    <form method="POST" action="{{ route('tech.tasks.accept', $o->id) }}"
                                          onsubmit="return confirm('متأكد من قبول المهمة؟')" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl
                           bg-emerald-600 text-white hover:bg-emerald-700
                           dark:bg-emerald-500 dark:hover:bg-emerald-600
                           text-sm font-black transition">
                                            <i class="fa-solid fa-circle-check"></i>
                                            قبول
                                        </button>
                                    </form>

                                    {{-- Reject --}}
                                    <form method="POST" action="{{ route('tech.tasks.reject', $o->id) }}"
                                          onsubmit="return confirm('متأكد من رفض المهمة؟')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl
                           bg-rose-600 text-white hover:bg-rose-700
                           dark:bg-rose-500 dark:hover:bg-rose-600
                           text-sm font-black transition">
                                            <i class="fa-solid fa-circle-xmark"></i>
                                            رفض
                                        </button>
                                    </form>
                                @endif
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <div class="mx-auto w-16 h-16 rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-200">
                            <i class="fa-solid fa-inbox text-2xl"></i>
                        </div>
                        <p class="mt-4 font-black">لا توجد مهام مسندة لك حاليًا</p>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">عند إسناد أي مهمة ستظهر هنا مباشرة</p>

                        <a href="{{ route('tech.tasks.index') }}"
                           class="mt-5 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl
                  border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-800
                  text-sm font-black transition">
                            <i class="fa-solid fa-arrow-left-long"></i>
                            الذهاب إلى كل المهام
                        </a>
                    </div>
                @endforelse
            </div>

        </div>

    </div>
@endsection
