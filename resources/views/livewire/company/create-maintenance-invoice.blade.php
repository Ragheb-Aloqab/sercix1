<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('company.maintenance-invoices.index') }}"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-600 dark:text-servx-silver-light font-semibold transition-colors duration-300">
            <i class="fa-solid fa-arrow-left"></i>
            {{ __('common.back') }}
        </a>
    </div>

    <div class="dash-card max-w-2xl">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-6">{{ __('maintenance.upload_maintenance_invoice') }}</h2>

        <form wire:submit.prevent="save" class="space-y-6">
            {{-- File upload --}}
            <div x-data="{ dragging: false }"
                 @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                 class="rounded-xl border-2 border-dashed transition-colors px-6 py-8 text-center"
                 :class="dragging ? 'border-sky-500 bg-sky-500/10' : 'border-slate-300 dark:border-slate-600/50 bg-slate-50 dark:bg-slate-800/30'">
                <input type="file" wire:model="invoice_file" id="create_invoice_file"
                       accept=".pdf,.jpg,.jpeg,.png,.webp"
                       class="hidden"
                       x-ref="fileInput">
                <p class="text-slate-600 dark:text-servx-silver-light mb-2">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-sky-600 dark:text-sky-400"></i>
                </p>
                <p class="text-slate-600 dark:text-servx-silver-light text-sm mb-1">{{ __('maintenance.invoice_file_optional') }}</p>
                <p class="text-slate-500 text-xs mb-1">{{ __('maintenance.invoice_file_accept', ['max' => $maxFileMb]) }}</p>
                <p class="text-slate-500 text-xs mb-3">
                    @if($invoice_file)
                        <span class="text-sky-400">{{ $invoice_file->getClientOriginalName() }}</span>
                        <span class="text-slate-500 dark:text-servx-silver">({{ number_format($invoice_file->getSize() / 1024, 1) }} KB)</span>
                    @else
                        {{ __('common.choose_file') }}
                    @endif
                </p>
                <label for="create_invoice_file" class="inline-block px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold cursor-pointer transition-colors">
                    <i class="fa-solid fa-upload me-2"></i>{{ __('common.upload') }}
                </label>
            </div>
            @error('invoice_file')
                <p class="text-sm text-red-400">{{ $message }}</p>
            @enderror

            {{-- Vehicle --}}
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('driver.vehicle') }}</label>
                <select wire:model="vehicle_id" name="vehicle_id"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">
                    <option value="">{{ __('fuel.all_vehicles') }}</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}">{{ $v->display_name }} ({{ $v->plate_number ?? '-' }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Amount --}}
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('maintenance.invoice_amount') }} ({{ __('company.sar') }})</label>
                <input type="number" wire:model.live="amount" step="0.01" min="0"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300"
                    placeholder="0.00">
            </div>

            {{-- Services: searchable multi-select with translated names --}}
            <div x-data="{
                open: false,
                search: '',
                services: {{ Js::from($services->map(fn($s) => ['id' => $s->id, 'name' => $s->getTranslatedName(), 'nameOriginal' => $s->name])->values()) }},
                get filtered() { const q = this.search.toLowerCase().trim(); return this.services.filter(s => !q || s.name.toLowerCase().includes(q) || (s.nameOriginal && s.nameOriginal.toLowerCase().includes(q))); },
                get noMatch() { return this.open && this.search && this.filtered.length === 0; }
            }" x-on:click.outside="open = false" class="relative">
                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('maintenance.services') }}</label>
                <div class="flex gap-2 flex-wrap">
                    <div class="flex-1 min-w-0">
                        <div class="rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 overflow-hidden">
                            @if(count($service_ids) > 0)
                                <div class="flex flex-wrap gap-1.5 p-2 border-b border-slate-200 dark:border-slate-600/50">
                                    @foreach($services->whereIn('id', $service_ids) as $s)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-sky-500/20 text-sky-700 dark:text-sky-300 text-sm">
                                            {{ $s->getTranslatedName() }}
                                            <button type="button" wire:click="removeService({{ $s->id }})"
                                                class="hover:text-red-500 transition-colors" title="{{ __('common.remove') }}">
                                                <i class="fa-solid fa-xmark text-xs"></i>
                                            </button>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="relative">
                                <input type="text" x-model="search" @focus="open = true"
                                    placeholder="{{ __('maintenance.search_services') }}"
                                    class="w-full px-4 py-2.5 pr-10 bg-transparent text-slate-900 dark:text-servx-silver-light border-0 focus:ring-0 focus:outline-none placeholder-slate-400">
                                <button type="button" @click="open = !open"
                                    class="absolute end-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-servx-silver">
                                    <i class="fa-solid fa-chevron-down text-sm transition-transform" :class="{ 'rotate-180': open }"></i>
                                </button>
                            </div>
                            <div x-show="open" x-transition
                                class="max-h-48 overflow-y-auto border-t border-slate-200 dark:border-slate-600/50">
                                <template x-for="s in filtered" :key="s.id">
                                    <label class="flex items-center gap-2 px-4 py-2.5 hover:bg-slate-100 dark:hover:bg-slate-700/50 cursor-pointer border-b border-slate-100 dark:border-slate-700/50 last:border-0">
                                        <input type="checkbox" :value="s.id"
                                            class="w-4 h-4 rounded border-slate-400 text-sky-600 focus:ring-sky-500"
                                            :checked="($wire.service_ids || []).includes(s.id) || ($wire.service_ids || []).includes(String(s.id))"
                                            @change="const ids = $wire.service_ids || []; const next = $el.checked ? [...ids, s.id] : ids.filter(id => id != s.id && id != s.id.toString()); $wire.set('service_ids', next)">
                                        <span class="text-slate-700 dark:text-servx-silver-light" x-text="s.name"></span>
                                    </label>
                                </template>
                                <p x-show="noMatch" class="px-4 py-3 text-slate-500 text-sm">
                                    {{ __('maintenance.no_services_match') }}
                                </p>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ __('maintenance.select_services') }}</p>
                    </div>
                    <button type="button" wire:click="openAddServiceModal"
                        class="shrink-0 self-start px-3 py-2.5 rounded-xl border border-dashed border-sky-500/50 hover:bg-sky-500/10 text-sky-600 dark:text-sky-400 transition-colors inline-flex items-center gap-1.5"
                        title="{{ __('maintenance.add_service') }}">
                        <i class="fa-solid fa-plus"></i>
                        <span class="text-sm font-semibold">{{ __('maintenance.add_service') }}</span>
                    </button>
                </div>
                @error('service_ids')
                    <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tax option --}}
            <div x-data="{
                get amount() { return parseFloat($wire.amount) || 0; },
                get taxType() { return $wire.tax_type || 'without_tax'; },
                get vat() { return (this.amount * 0.15).toFixed(2); },
                get total() { return (this.amount * 1.15).toFixed(2); },
                get showVatSummary() { return this.taxType === 'with_tax' && this.amount > 0; }
            }">
                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-2">{{ __('maintenance.tax_option') }}</label>
                <div class="flex flex-wrap gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="tax_type" value="without_tax" class="w-4 h-4 rounded-full border-slate-400 text-sky-600 focus:ring-sky-500">
                        <span class="text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.without_tax') }}</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model.live="tax_type" value="with_tax" class="w-4 h-4 rounded-full border-slate-400 text-sky-600 focus:ring-sky-500">
                        <span class="text-slate-700 dark:text-servx-silver-light">{{ __('maintenance.with_tax_vat') }}</span>
                    </label>
                </div>
                <template x-if="showVatSummary">
                    <div class="mt-3 p-3 rounded-xl bg-sky-500/10 border border-sky-400/30" x-transition x-cloak>
                        <p class="text-sm text-slate-700 dark:text-servx-silver-light">
                            <span class="font-semibold">{{ __('maintenance.vat_amount') }} (15%):</span>
                            <span class="font-bold text-sky-600 dark:text-sky-400" x-text="vat + ' {{ __('company.sar') }}'"></span>
                        </p>
                        <p class="text-sm text-slate-700 dark:text-servx-silver-light mt-1">
                            <span class="font-semibold">{{ __('maintenance.total_with_tax') }}:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="total + ' {{ __('company.sar') }}'"></span>
                        </p>
                    </div>
                </template>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('common.description') }}</label>
                <input type="text" wire:model="description" maxlength="500"
                    class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300"
                    placeholder="{{ __('common.optional') }}">
            </div>

            @if($errors->any())
                <div class="p-3 rounded-xl bg-red-500/10 border border-red-400/30 text-red-600 dark:text-red-400 text-sm">
                    @foreach($errors->all() as $err)
                        <p>{{ $err }}</p>
                    @endforeach
                </div>
            @endif

            <div class="flex gap-3 pt-2">
                <button type="submit" wire:loading.attr="disabled"
                    class="flex-1 px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold transition-colors">
                    <span wire:loading.remove wire:target="save">{{ __('maintenance.add_invoice') }}</span>
                    <span wire:loading wire:target="save">{{ __('common.saving') ?: 'Saving...' }}</span>
                </button>
                <a href="{{ route('company.maintenance-invoices.index') }}"
                    class="px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-700 dark:text-servx-silver-light font-bold transition-colors duration-300 text-center">
                    {{ __('common.cancel') }}
                </a>
            </div>
        </form>
    </div>

    {{-- Add Service Modal --}}
    @if($addServiceModalOpen)
        <div class="fixed inset-0 z-[55] overflow-y-auto" aria-modal="true" role="dialog">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" wire:click="closeAddServiceModal"></div>
                <div class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600/50 shadow-2xl p-6">
                    <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-4">{{ __('maintenance.add_service') }}</h4>
                    <form wire:submit.prevent="addNewService" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('maintenance.service_name') }}</label>
                            <input type="text" wire:model="newServiceName" maxlength="255"
                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light"
                                placeholder="{{ __('maintenance.service_name') }}">
                            @error('newServiceName')
                                <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold">
                                {{ __('maintenance.add') }}
                            </button>
                            <button type="button" wire:click="closeAddServiceModal"
                                class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 font-bold">
                                {{ __('common.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
