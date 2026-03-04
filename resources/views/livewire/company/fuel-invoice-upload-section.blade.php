<div>
    {{-- Add Fuel Invoice button (shown when embedded on invoices page) --}}
    <button type="button"
        wire:click="openModal"
        class="shrink-0 px-5 py-3 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold transition-colors inline-flex items-center gap-2">
        <i class="fa-solid fa-gas-pump"></i>
        {{ __('invoice.add_fuel_invoice') }}
    </button>

    {{-- Success notification --}}
    @if (session('fuel_invoice_success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-400/50" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            {{ session('fuel_invoice_success') }}
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
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-4">{{ __('invoice.upload_fuel_invoice') }}</h3>

                    <form wire:submit="saveInvoice" class="space-y-4">
                        {{-- Drag & Drop --}}
                        <div x-data="{ dragging: false }"
                             @dragover.prevent="dragging = true"
                             @dragleave.prevent="dragging = false"
                             @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                             class="rounded-xl border-2 border-dashed transition-colors px-6 py-8 text-center"
                             :class="dragging ? 'border-sky-500 bg-sky-500/10' : 'border-slate-300 dark:border-slate-600/50 bg-slate-50 dark:bg-slate-800/30'">
                            <input type="file" wire:model="invoice_file" id="modal_fuel_invoice_file"
                                   accept=".pdf,.jpg,.jpeg,.png,.webp"
                                   class="hidden"
                                   x-ref="fileInput">
                            <p class="text-slate-600 dark:text-servx-silver-light mb-2">
                                <i class="fa-solid fa-cloud-arrow-up text-3xl text-sky-600 dark:text-sky-400"></i>
                            </p>
                            <p class="text-slate-600 dark:text-servx-silver-light text-sm mb-1">{{ __('maintenance.invoice_file_accept', ['max' => $maxFileMb]) }}</p>
                            <p class="text-slate-500 text-xs mb-3">
                                @if($invoice_file)
                                    <span class="text-sky-400">{{ $invoice_file->getClientOriginalName() }}</span>
                                    <span class="text-slate-500 dark:text-servx-silver">({{ number_format($invoice_file->getSize() / 1024, 1) }} KB)</span>
                                @else
                                    {{ __('common.choose_file') }}
                                @endif
                            </p>
                            <label for="modal_fuel_invoice_file" class="inline-block px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white font-semibold cursor-pointer transition-colors">
                                <i class="fa-solid fa-upload me-2"></i>{{ __('common.upload') }}
                            </label>
                        </div>
                        @error('invoice_file')
                            <p class="text-sm text-red-400">{{ $message }}</p>
                        @enderror

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-servx-silver-light mb-1">{{ __('driver.vehicle') }} <span class="text-red-400">*</span></label>
                                <select wire:model="vehicle_id" class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300">
                                    <option value="">{{ __('invoice.select_vehicle') }}</option>
                                    @foreach($vehicles as $v)
                                        <option value="{{ $v->id }}">{{ $v->plate_number }} — {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) }}</option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')
                                    <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 dark:text-servx-silver-light mb-1">{{ __('maintenance.final_invoice_amount') }} ({{ __('company.sar') }})</label>
                                <input type="number" wire:model="amount" step="0.01" min="0" class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300" placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-servx-silver-light mb-1">{{ __('common.description') }}</label>
                            <input type="text" wire:model="description" maxlength="500" class="w-full rounded-xl border border-slate-300 dark:border-slate-600/50 bg-white dark:bg-slate-800/60 px-4 py-2 text-slate-900 dark:text-servx-silver-light transition-colors duration-300" placeholder="{{ __('common.optional') }}">
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold transition-colors">
                                <span wire:loading.remove wire:target="saveInvoice">{{ __('invoice.add_fuel_invoice') }}</span>
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
</div>
