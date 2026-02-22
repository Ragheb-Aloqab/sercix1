<div class="space-y-4">
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 border border-emerald-500/20">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 text-rose-800 dark:text-rose-300 border border-rose-500/20">
            <p class="font-bold mb-2">{{ __('livewire.errors_title') }}</p>
            <ul class="list-disc ps-5">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('livewire.order_details') }}</p>
                <h1 class="text-2xl font-black">#{{ $order->id }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ __('livewire.created_at') }}: {{ $order->created_at?->format('Y-m-d H:i') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @include('admin.orders.partials._status_badge', ['status' => $order->status])
                <a href="{{ route('admin.orders.index') }}"
                   class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">
                    {{ __('livewire.back_to_list') }}
                </a>
                <a href="{{ route('admin.orders.invoice.show', $order) }}"
                   class="px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold">
                    <i class="fa-solid fa-print me-2"></i> {{ __('livewire.invoice') }}
                </a>
                @if (!$order->invoice)
                    <button type="button" wire:click="createInvoice"
                            class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                        {{ __('livewire.create_invoice') }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="xl:col-span-2 space-y-4">
            @include('admin.orders.partials._order_summary', ['order' => $order])
            @include('admin.orders.partials._services', ['order' => $order])
            @include('admin.orders.partials._timeline', ['order' => $order])

            {{-- Attachments: before/after images removed; signature/other kept for reference --}}
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
                <div class="p-5 border-b border-slate-200/70 dark:border-slate-800">
                    <h2 class="text-lg font-black">{{ __('livewire.attachments') }}</h2>
                </div>
                <div class="p-5 space-y-4">
                    <form wire:submit="uploadAttachment" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <select wire:model="attachment_type"
                                class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                            <option value="signature">{{ __('livewire.signature') }}</option>
                            <option value="other">{{ __('livewire.other') }}</option>
                        </select>
                        <input type="file" wire:model="attachment_file" accept="image/*"
                               class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
                        <button type="submit" class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                            {{ __('livewire.upload') }}
                        </button>
                    </form>
                    <div>
                        <h3 class="font-black mb-3">{{ __('livewire.signature_other') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @forelse($others as $att)
                                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                                    <div class="flex justify-between items-center">
                                        <p class="font-bold">{{ $att->type }}</p>
                                        <button type="button" wire:click="deleteAttachment({{ $att->id }})" class="text-rose-600 font-bold text-sm">{{ __('common.delete') }}</button>
                                    </div>
                                    <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="text-sky-600 font-semibold text-sm">{{ __('livewire.open_file') }}</a>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">{{ __('livewire.no_other_attachments') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            {{-- Technician assignment removed - tasks remain unassigned --}}

            {{-- Change status (Livewire) --}}
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <h2 class="text-lg font-black mb-3">{{ __('livewire.change_status') }}</h2>
                <form wire:submit="changeStatus" class="space-y-3">
                    <select wire:model="to_status"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        @foreach (\App\Support\OrderStatus::ALL as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                    <textarea wire:model="status_note" rows="3"
                              class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                              placeholder="{{ __('livewire.reason_optional') }}"></textarea>
                    <button type="submit" class="w-full px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold">{{ __('livewire.update') }}</button>
                </form>
            </div>

            @include('admin.orders.partials._quick_info', ['order' => $order])
        </div>
    </div>
</div>
