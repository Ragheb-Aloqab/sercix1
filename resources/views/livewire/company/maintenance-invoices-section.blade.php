<div>
    {{-- Success notification --}}
    @if (session('invoice_success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-400/50" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            {{ session('invoice_success') }}
        </div>
    @endif

    {{-- Invoices section header with Upload button (opens modal) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h2 class="dash-section-title mb-0">{{ __('maintenance.add_invoice') }} — {{ __('common.uploaded') }}</h2>
        <button type="button" wire:click="openModal"
            class="shrink-0 px-5 py-3 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors inline-flex items-center gap-2">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            {{ __('maintenance.upload_maintenance_invoice') }}
        </button>
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
                                        <button type="button" wire:click="openEditModal({{ $inv->id }})"
                                            class="px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-600 dark:text-servx-silver-light text-sm font-semibold transition-colors duration-300">
                                            <i class="fa-solid fa-pen me-1"></i> {{ __('common.edit') }}
                                        </button>
                                        <form method="POST" action="{{ route('company.maintenance-invoices.company.destroy', $inv) }}" class="inline" onsubmit="return confirm({{ json_encode(__('maintenance.confirm_delete_invoice')) }});">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-2 rounded-xl border border-rose-500/50 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 text-sm font-semibold transition-colors duration-300">
                                                <i class="fa-solid fa-trash-can me-1"></i> {{ __('common.delete') }}
                                            </button>
                                        </form>
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
                <div class="relative w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600/50 shadow-2xl p-6 transition-colors duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $editingInvoiceId ? __('maintenance.edit_invoice') : __('maintenance.add_invoice') }}</h3>
                        <button type="button" wire:click="closeModal" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-500 dark:text-servx-silver transition-colors">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveInvoice" @submit.prevent class="space-y-6">
                        {{-- 1. Choose Vehicle (required) --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('maintenance.choose_vehicle') }} <span class="text-red-500">*</span></label>
                            <select wire:model="vehicle_id" name="vehicle_id"
                                class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2.5 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">
                                <option value="">{{ __('maintenance.select_vehicle') }}</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}">{{ $v->display_name }} ({{ $v->plate_number ?? '-' }})</option>
                                @endforeach
                            </select>
                            @error('vehicle_id')
                                <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 2. Service Type --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-2">{{ __('maintenance.service_type') }}</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(\App\Models\CompanyMaintenanceInvoice::serviceTypes() as $type)
                                    <label class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border cursor-pointer transition-colors
                                        {{ $service_type === $type ? 'bg-sky-500/20 border-sky-500/50 text-sky-700 dark:text-sky-300' : 'border-slate-300 dark:border-slate-600/50 hover:bg-slate-100 dark:hover:bg-slate-700/50 text-slate-700 dark:text-servx-silver-light' }}">
                                        <input type="radio" wire:model.live="service_type" value="{{ $type }}" class="sr-only">
                                        <span class="font-semibold text-sm">{{ __('maintenance.service_type_' . $type) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- 3. Service amount: line items table (add & edit) --}}
                        <div>
                                <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-2">{{ __('maintenance.service_amount') }}</label>
                                <div class="rounded-xl border border-slate-300 dark:border-slate-600/50 overflow-hidden">
                                    <table class="w-full text-start">
                                        <thead>
                                            <tr class="text-slate-600 dark:text-servx-silver text-sm border-b border-slate-200 dark:border-slate-600/50 bg-slate-50 dark:bg-slate-800/50">
                                                <th class="px-4 py-3 font-semibold">{{ __('maintenance.service') }}</th>
                                                <th class="px-4 py-3 font-semibold w-28">{{ __('maintenance.price') }} ({{ __('company.sar') }})</th>
                                                <th class="w-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lineItems as $index => $line)
                                                <tr class="border-b border-slate-100 dark:border-slate-700/50 last:border-0">
                                                    <td class="px-4 py-2">
                                                        <select wire:model.live="lineItems.{{ $index }}.service_id"
                                                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm text-slate-900 dark:text-servx-silver-light">
                                                            <option value="">{{ __('maintenance.select_service') }}</option>
                                                            @foreach($services as $s)
                                                                <option value="{{ $s->id }}">{{ $s->getTranslatedName() }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="px-4 py-2">
                                                        <input type="number" wire:model.live="lineItems.{{ $index }}.price" step="0.01" min="0"
                                                            class="w-full rounded-lg border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-3 py-2 text-sm text-slate-900 dark:text-servx-silver-light" placeholder="0">
                                                    </td>
                                                    <td class="px-2 py-2">
                                                        <button type="button" wire:click="removeLineItem({{ $index }})" wire:disabled="{{ count($lineItems) <= 1 }}"
                                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-500/10 disabled:opacity-40 disabled:pointer-events-none transition-colors" title="{{ __('common.remove') }}">
                                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="px-4 py-2 border-t border-slate-200 dark:border-slate-600/50 bg-slate-50/50 dark:bg-slate-800/30">
                                        <button type="button" wire:click="addLineItem"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-dashed border-sky-500/50 hover:bg-sky-500/10 text-sky-600 dark:text-sky-400 text-sm font-semibold transition-colors">
                                            <i class="fa-solid fa-plus"></i> {{ __('maintenance.add_service') }} +
                                        </button>
                                    </div>
                                </div>
                                {{-- Invoice details (subtotal, tax, total) --}}
                                <div class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/30 border border-slate-200 dark:border-slate-600/50 space-y-2">
                                    <p class="text-sm font-semibold text-slate-700 dark:text-servx-silver-light flex justify-between">
                                        <span>{{ __('maintenance.total_before_tax') }}</span>
                                        <span>{{ number_format($this->getSubtotal(), 2) }} {{ __('company.sar') }}</span>
                                    </p>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-servx-silver-light flex justify-between">
                                        <span>{{ __('maintenance.tax_15') }}</span>
                                        <span>{{ number_format($this->getVatAmount(), 2) }} {{ __('company.sar') }}</span>
                                    </p>
                                    <p class="text-base font-bold text-emerald-600 dark:text-emerald-400 flex justify-between pt-2 border-t border-slate-200 dark:border-slate-600/50">
                                        <span>{{ __('maintenance.total') }}</span>
                                        <span>{{ number_format($this->getTotal(), 2) }} {{ __('company.sar') }}</span>
                                    </p>
                                </div>
                                {{-- Tax option --}}
                                <div class="mt-3">
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
                                </div>
                        </div>

                        {{-- Description (optional) --}}
                        <div>
                            <label class="block text-sm font-bold text-slate-600 dark:text-servx-silver-light mb-1">{{ __('common.description') }}</label>
                            <input type="text" wire:model="description" maxlength="500" class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300" placeholder="{{ __('common.optional') }}">
                        </div>

                        {{-- 4. Upload Invoice --}}
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
