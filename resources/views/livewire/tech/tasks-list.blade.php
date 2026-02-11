<div class="space-y-6" wire:loading.class="opacity-70">
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-900 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            <div class="w-full sm:w-64">
                <label class="text-xs text-slate-500 dark:text-slate-400">الحالة</label>
                <select wire:model.live="status"
                        class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-2">
                    <option value="">الكل</option>
                    @foreach ($statuses as $st)
                        <option value="{{ $st }}">{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" wire:click="clearFilters"
                    class="mt-5 sm:mt-6 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                إعادة ضبط
            </button>
        </div>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <h2 class="text-lg font-black">قائمة المهام</h2>
        <div class="mt-4 space-y-4">
            @forelse ($tasks as $o)
                @php
                    $status = strtolower((string) $o->status);
                    $map = [
                        'pending'     => ['بانتظار',   'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300', 'w-2 bg-amber-400'],
                        'in_progress' => ['قيد التنفيذ','bg-sky-100 text-sky-800 dark:bg-sky-500/15 dark:text-sky-300',     'w-2 bg-sky-400'],
                        'completed'   => ['مكتملة',    'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-slate-300','w-2 bg-emerald-400'],
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
                <div class="relative rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="absolute inset-y-0 end-0 {{ $bar }}"></div>
                    <div class="p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-sky-500 text-white flex items-center justify-center font-black shadow-soft">
                                <i class="fa-solid fa-wrench"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-black truncate">طلب #{{ $o->id }}</p>
                                    <span class="px-3 py-1 rounded-full text-xs font-black {{ $badge }}">{{ $label }}</span>
                                </div>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                    الشركة: <span class="font-bold text-slate-700 dark:text-slate-200">{{ $companyName }}</span>
                                    @if ($companyPhone) <span class="mx-2 text-slate-300">•</span> <span class="font-bold">{{ $companyPhone }}</span> @endif
                                </p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $o->created_at?->format('Y-m-d H:i') }}</p>
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
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('tech.tasks.show', $o->id) }}"
                               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 hover:opacity-90 text-sm font-black transition">
                                <i class="fa-solid fa-eye"></i> عرض
                            </a>
                            @if($status !== 'completed')
                                <button type="button"
                                        wire:click="acceptTask({{ $o->id }})"
                                        wire:confirm="متأكد من قبول المهمة؟"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-black transition">
                                    <i class="fa-solid fa-circle-check"></i> قبول
                                </button>
                                <button type="button"
                                        wire:click="rejectTask({{ $o->id }})"
                                        wire:confirm="متأكد من رفض المهمة؟"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-rose-600 text-white hover:bg-rose-700 text-sm font-black transition">
                                    <i class="fa-solid fa-circle-xmark"></i> رفض
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-slate-500">لا توجد مهام مسندة لك حاليًا</div>
            @endforelse
        </div>
        <div class="mt-4">
            {{ $tasks->links() }}
        </div>
    </div>
</div>
