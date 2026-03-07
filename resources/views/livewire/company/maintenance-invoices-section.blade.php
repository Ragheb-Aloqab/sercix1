<div>
    {{-- Success notification --}}
    @if (session('invoice_success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-400/50" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            {{ session('invoice_success') }}
        </div>
    @endif

    {{-- Invoices section header with Upload button (top right) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h2 class="dash-section-title mb-0">{{ __('maintenance.add_invoice') }} — {{ __('common.uploaded') }}</h2>
        <a href="{{ route('company.maintenance-invoices.create') }}"
            class="shrink-0 px-5 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors inline-flex items-center gap-2">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            {{ __('maintenance.upload_maintenance_invoice') }}
        </a>
    </div>

    {{-- Company-uploaded invoices list --}}
    @if($companyInvoices->isNotEmpty())
        <div class="dash-card mb-6">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-start text-slate-600 dark:text-servx-silver text-sm border-b border-slate-200 dark:border-slate-600/50">
                            <th class="pb-3 pe-4">{{ __('common.preview') }}</th>
                            <th class="pb-3 pe-4">{{ __('driver.vehicle') }}</th>
                            <th class="pb-3 pe-4">{{ __('maintenance.services') }}</th>
                            <th class="pb-3 pe-4">{{ __('maintenance.final_invoice_amount') }}</th>
                            <th class="pb-3 pe-4">{{ __('maintenance.upload_date') }}</th>
                            <th class="pb-3 pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companyInvoices as $inv)
                            <tr class="border-b border-slate-200 dark:border-slate-600/30 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors duration-300">
                                <td class="py-4 pe-4">
                                    @if($inv->hasInvoiceFile())
                                        @if($inv->isImage())
                                            <button type="button"
                                                @click="$dispatch('open-image-preview', { url: '{{ route('company.maintenance-invoices.company.view', $inv) }}' })"
                                                class="block w-16 h-16 rounded-lg overflow-hidden border border-slate-300 dark:border-slate-600/50 hover:border-sky-500/50 transition-colors cursor-pointer"
                                                title="{{ __('common.view') }}">
                                                <img src="{{ route('company.maintenance-invoices.company.thumbnail', $inv) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                            </button>
                                        @else
                                            <a href="{{ route('company.maintenance-invoices.company.view', $inv) }}" target="_blank"
                                                class="inline-flex w-16 h-16 rounded-lg bg-red-500/20 border border-red-400/50 items-center justify-center hover:bg-red-500/30 dark:hover:bg-red-500/40 transition-colors duration-300"
                                                title="{{ __('common.view') }}">
                                                <i class="fa-solid fa-file-pdf text-2xl text-red-600 dark:text-red-400"></i>
                                            </a>
                                        @endif
                                    @else
                                        <span class="inline-flex w-16 h-16 rounded-lg bg-slate-100 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600/50 items-center justify-center text-slate-400 dark:text-slate-500 text-xs" title="{{ __('maintenance.invoice_file_optional') }}">—</span>
                                    @endif
                                </td>
                                <td class="py-4 pe-4 text-slate-900 dark:text-white">{{ $inv->vehicle?->display_name ?? $inv->vehicle?->plate_number ?? '-' }}</td>
                                <td class="py-4 pe-4 text-slate-600 dark:text-servx-silver text-sm">
                                    @if($inv->services->isNotEmpty())
                                        {{ $inv->services->map(fn($s) => $s->getTranslatedName())->join(', ') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-4 pe-4 text-slate-600 dark:text-servx-silver-light">
                                    @if($inv->amount)
                                        {{ number_format($inv->amount, 2) }} {{ __('company.sar') }}
                                        @if($inv->hasTax())
                                            <span class="ms-1 px-1.5 py-0.5 rounded text-xs bg-sky-500/20 text-sky-600 dark:text-sky-400 border border-sky-400/30" title="{{ __('maintenance.with_tax_vat') }}">VAT</span>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-4 pe-4 text-slate-600 dark:text-servx-silver">{{ $inv->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td class="py-4 pe-4">
                                    <div class="flex flex-wrap gap-2">
                                        @if($inv->hasInvoiceFile())
                                            @if($inv->isImage())
                                                <button type="button" @click="$dispatch('open-image-preview', { url: '{{ route('company.maintenance-invoices.company.view', $inv) }}' })"
                                                    class="px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold">
                                                    <i class="fa-solid fa-eye me-1"></i> {{ __('common.view') }}
                                                </button>
                                            @else
                                                <a href="{{ route('company.maintenance-invoices.company.view', $inv) }}" target="_blank"
                                                    class="px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold">
                                                    <i class="fa-solid fa-eye me-1"></i> {{ __('common.view') }}
                                                </a>
                                            @endif
                                            <a href="{{ route('company.maintenance-invoices.company.download', $inv) }}"
                                                class="px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-600 dark:text-servx-silver-light text-sm font-semibold transition-colors duration-300">
                                                <i class="fa-solid fa-download me-1"></i> {{ __('fleet.download_pdf') }}
                                            </a>
                                        @endif
                                        <button type="button" wire:click="openEditModal({{ $inv->id }})"
                                            class="px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-600 dark:text-servx-silver-light text-sm font-semibold transition-colors duration-300">
                                            <i class="fa-solid fa-pen me-1"></i> {{ __('common.edit') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="dash-card mb-6">
            <p class="text-slate-600 dark:text-servx-silver">{{ __('maintenance.no_invoices') }}</p>
            <p class="text-sm text-slate-500 mt-1">{{ __('maintenance.add_invoice_desc') }}</p>
        </div>
    @endif

    {{-- Upload Modal --}}
    @if($modalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true" role="dialog">
            <div class="flex min-h-full items-center justify-center p-4">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" wire:click="closeModal"></div>

                {{-- Modal --}}
                <div class="relative w-full max-w-lg rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600/50 shadow-2xl p-6 transition-colors duration-300">
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">{{ $editingInvoiceId ? __('maintenance.edit_invoice') : __('maintenance.upload_maintenance_invoice') }}</h3>

                    <form wire:submit.prevent="saveInvoice" @submit.prevent class="space-y-4">
                        {{-- Drag & Drop (hidden when editing) --}}
                        @if(!$editingInvoiceId)
                        <div x-data="{ dragging: false }"
                             @dragover.prevent="dragging = true"
                             @dragleave.prevent="dragging = false"
                             @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                             class="rounded-xl border-2 border-dashed transition-colors px-6 py-8 text-center"
                             :class="dragging ? 'border-sky-500 bg-sky-500/10' : 'border-slate-300 dark:border-slate-600/50 bg-slate-50 dark:bg-slate-800/30'">
                            <input type="file" wire:model="invoice_file" id="modal_invoice_file"
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
                            <label for="modal_invoice_file" class="inline-block px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold cursor-pointer transition-colors">
                                <i class="fa-solid fa-upload me-2"></i>{{ __('common.upload') }}
                            </label>
                        </div>
                        @error('invoice_file')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        @endif

                        {{-- Vehicle select (wire:model ensures it saves on submit) --}}
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('maintenance.invoice_amount') }} ({{ __('company.sar') }})</label>
                                <input type="number" wire:model.live="amount" step="0.01" min="0" class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300" placeholder="0.00">
                            </div>
                        </div>

                        {{-- Services: searchable multi-select with Add Service (translated) --}}
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
                                        {{-- Selected chips --}}
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
                                        {{-- Dropdown trigger + search --}}
                                        <div class="relative">
                                            <input type="text" x-model="search" @focus="open = true"
                                                placeholder="{{ __('maintenance.search_services') }}"
                                                class="w-full px-4 py-2.5 pr-10 bg-transparent text-slate-900 dark:text-servx-silver-light border-0 focus:ring-0 focus:outline-none placeholder-slate-400">
                                            <button type="button" @click="open = !open"
                                                class="absolute end-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-servx-silver">
                                                <i class="fa-solid fa-chevron-down text-sm transition-transform" :class="{ 'rotate-180': open }"></i>
                                            </button>
                                        </div>
                                        {{-- Options list --}}
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

                        {{-- Tax option (appears after amount) --}}
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
                        <div>
                            <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('common.description') }}</label>
                            <input type="text" wire:model="description" maxlength="500" class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300" placeholder="{{ __('common.optional') }}">
                        </div>

                        {{-- Validation errors summary --}}
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
                                @if($editingInvoiceId)
                                    <span wire:loading.remove wire:target="saveInvoice">{{ __('common.update') }}</span>
                                @else
                                    <span wire:loading.remove wire:target="saveInvoice">{{ __('maintenance.add_invoice') }}</span>
                                @endif
                                <span wire:loading wire:target="saveInvoice">{{ __('common.saving') ?: 'Saving...' }}</span>
                            </button>
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-3 rounded-xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-700 dark:text-servx-silver-light font-bold transition-colors duration-300">
                                {{ __('common.cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

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

    {{-- Image preview modal (Alpine) --}}
    <div x-data="{ open: false, url: '' }"
         @open-image-preview.window="open = true; url = $event.detail.url"
         @keydown.escape.window="open = false"
         x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         style="display: none;">
        <div class="fixed inset-0 bg-black/80" @click="open = false"></div>
        <div class="relative max-w-4xl max-h-[90vh] rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-900 shadow-2xl transition-colors duration-300">
            <button type="button" @click="open = false" class="absolute top-3 end-3 z-10 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img :src="url" alt="" class="max-w-full max-h-[90vh] object-contain" @click.stop>
        </div>
    </div>
</div>
