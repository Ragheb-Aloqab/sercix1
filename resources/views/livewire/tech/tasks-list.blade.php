<div class="space-y-6" wire:loading.class="opacity-70">
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-900 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            <div class="w-full sm:w-64">
                <label class="text-xs text-slate-500 dark:text-slate-400">{{ __('livewire.status_label') }}</label>
                <select wire:model.live="status"
                        class="mt-1 w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-3 py-2">
                    <option value="">{{ __('livewire.all') }}</option>
                    @foreach ($statuses as $st)
                        <option value="{{ $st }}">{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" wire:click="clearFilters"
                    class="mt-5 sm:mt-6 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                {{ __('livewire.reset_filters') }}
            </button>
        </div>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <h2 class="text-lg font-black">{{ __('livewire.tasks_list') }}</h2>
        <div class="mt-4 space-y-4">
            @forelse ($tasks as $row)
                <div class="relative rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                    <div class="absolute inset-y-0 end-0 {{ $row->bar }}"></div>
                    <div class="p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-sky-500 text-white flex items-center justify-center font-black shadow-soft">
                                <i class="fa-solid fa-wrench"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-black truncate">{{ __('orders.order') }} #{{ $row->order->id }}</p>
                                    <span class="px-3 py-1 rounded-full text-xs font-black {{ $row->badge }}">{{ $row->label }}</span>
                                </div>
                                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                                    {{ __('livewire.company') }}: <span class="font-bold text-slate-700 dark:text-slate-200">{{ $row->companyName }}</span>
                                    @if ($row->companyPhone) <span class="mx-2 text-slate-300">•</span> <span class="font-bold">{{ $row->companyPhone }}</span> @endif
                                </p>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ $row->order->created_at?->format('Y-m-d H:i') }}</p>
                                <div class="mt-3">
                                    <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 mb-1">
                                        <span>{{ __('livewire.task_progress') }}</span>
                                        <span class="font-bold">{{ $row->progress }}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                        <div class="h-full rounded-full bg-slate-900 dark:bg-white/80" style="width: {{ $row->progress }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('tech.tasks.show', $row->order->id) }}"
                               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 hover:opacity-90 text-sm font-black transition">
                                <i class="fa-solid fa-eye"></i> {{ __('livewire.view') }}
                            </a>
                            @if($row->order->status !== 'completed')
                                <button type="button"
                                        wire:click="acceptTask({{ $row->order->id }})"
                                        wire:confirm="{{ __('livewire.accept_confirm') }}"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-black transition">
                                    <i class="fa-solid fa-circle-check"></i> {{ __('livewire.accept') }}
                                </button>
                                <button type="button"
                                        wire:click="rejectTask({{ $row->order->id }})"
                                        wire:confirm="{{ __('livewire.reject_confirm') }}"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-2xl bg-rose-600 text-white hover:bg-rose-700 text-sm font-black transition">
                                    <i class="fa-solid fa-circle-xmark"></i> {{ __('livewire.reject') }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-slate-500">{{ __('livewire.no_tasks_assigned') }}</div>
            @endforelse
        </div>
        <div class="mt-4">
            {{ $tasks->links() }}
        </div>
    </div>
</div>
