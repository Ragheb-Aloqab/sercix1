<div>
    {{-- Success notification --}}
    @if (session('invoice_success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            {{ session('invoice_success') }}
        </div>
    @endif

    {{-- Invoices section header with Upload button (top right) --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <h2 class="dash-section-title mb-0">{{ __('maintenance.add_invoice') }} — {{ __('common.uploaded') }}</h2>
        <button type="button"
            wire:click="openModal"
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
                        <tr class="text-start text-servx-silver text-sm border-b border-slate-600/50">
                            <th class="pb-3 pe-4">{{ __('common.preview') }}</th>
                            <th class="pb-3 pe-4">{{ __('driver.vehicle') }}</th>
                            <th class="pb-3 pe-4">{{ __('maintenance.final_invoice_amount') }}</th>
                            <th class="pb-3 pe-4">{{ __('maintenance.upload_date') }}</th>
                            <th class="pb-3 pe-4"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companyInvoices as $inv)
                            <tr class="border-b border-slate-600/30 hover:bg-slate-800/30">
                                <td class="py-4 pe-4">
                                    @if($inv->isImage())
                                        <button type="button"
                                            @click="$dispatch('open-image-preview', { url: '{{ route('company.maintenance-invoices.company.view', $inv) }}' })"
                                            class="block w-16 h-16 rounded-lg overflow-hidden border border-slate-600/50 hover:border-sky-500/50 transition-colors cursor-pointer"
                                            title="{{ __('common.view') }}">
                                            <img src="{{ route('company.maintenance-invoices.company.thumbnail', $inv) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                        </button>
                                    @else
                                        <a href="{{ route('company.maintenance-invoices.company.view', $inv) }}" target="_blank"
                                            class="inline-flex w-16 h-16 rounded-lg bg-red-500/20 border border-red-400/50 items-center justify-center hover:bg-red-500/30 transition-colors"
                                            title="{{ __('common.view') }}">
                                            <i class="fa-solid fa-file-pdf text-2xl text-red-400"></i>
                                        </a>
                                    @endif
                                </td>
                                <td class="py-4 pe-4">{{ $inv->vehicle?->plate_number ?? '-' }}</td>
                                <td class="py-4 pe-4">{{ $inv->amount ? number_format($inv->amount, 2) . ' ' . __('company.sar') : '-' }}</td>
                                <td class="py-4 pe-4">{{ $inv->created_at?->format('Y-m-d H:i') ?? '-' }}</td>
                                <td class="py-4 pe-4">
                                    <div class="flex gap-2">
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
                                            class="px-3 py-2 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 text-servx-silver-light text-sm font-semibold">
                                            <i class="fa-solid fa-download me-1"></i> {{ __('fleet.download_pdf') }}
                                        </a>
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
            <p class="text-servx-silver">{{ __('maintenance.no_invoices') }}</p>
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
                <div class="relative w-full max-w-lg rounded-2xl bg-slate-800 border border-slate-600/50 shadow-2xl p-6">
                    <h3 class="text-xl font-bold text-white mb-4">{{ __('maintenance.upload_maintenance_invoice') }}</h3>

                    <form wire:submit="saveInvoice" class="space-y-4">
                        {{-- Drag & Drop --}}
                        <div x-data="{ dragging: false }"
                             @dragover.prevent="dragging = true"
                             @dragleave.prevent="dragging = false"
                             @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                             class="rounded-xl border-2 border-dashed transition-colors px-6 py-8 text-center"
                             :class="dragging ? 'border-sky-500 bg-sky-500/10' : 'border-slate-600/50 bg-slate-800/30'">
                            <input type="file" wire:model="invoice_file" id="modal_invoice_file"
                                   accept=".pdf,.jpg,.jpeg,.png,.webp"
                                   class="hidden"
                                   x-ref="fileInput">
                            <p class="text-servx-silver-light mb-2">
                                <i class="fa-solid fa-cloud-arrow-up text-3xl text-sky-400"></i>
                            </p>
                            <p class="text-servx-silver-light text-sm mb-1">{{ __('maintenance.invoice_file_accept', ['max' => $maxFileMb]) }}</p>
                            <p class="text-slate-500 text-xs mb-3">
                                @if($invoice_file)
                                    <span class="text-sky-400">{{ $invoice_file->getClientOriginalName() }}</span>
                                    <span class="text-servx-silver">({{ number_format($invoice_file->getSize() / 1024, 1) }} KB)</span>
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('driver.vehicle') }}</label>
                                <select wire:model="vehicle_id" class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light">
                                    <option value="">{{ __('fuel.all_vehicles') }}</option>
                                    @foreach($vehicles as $v)
                                        <option value="{{ $v->id }}">{{ $v->plate_number }} — {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('maintenance.final_invoice_amount') }} ({{ __('company.sar') }})</label>
                                <input type="number" wire:model="amount" step="0.01" min="0" class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light" placeholder="0.00">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-servx-silver-light mb-1">{{ __('common.description') }}</label>
                            <input type="text" wire:model="description" maxlength="500" class="w-full rounded-xl border border-slate-600/50 bg-slate-800/60 px-4 py-2 text-servx-silver-light" placeholder="{{ __('common.optional') }}">
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold transition-colors">
                                <span wire:loading.remove wire:target="saveInvoice">{{ __('maintenance.add_invoice') }}</span>
                                <span wire:loading wire:target="saveInvoice">{{ __('common.saving') ?: 'Saving...' }}</span>
                            </button>
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-3 rounded-xl border border-slate-600/50 hover:bg-slate-700/50 text-servx-silver-light font-bold transition-colors">
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
        <div class="relative max-w-4xl max-h-[90vh] rounded-xl overflow-hidden bg-slate-900 shadow-2xl">
            <button type="button" @click="open = false" class="absolute top-3 end-3 z-10 w-10 h-10 rounded-full bg-black/50 hover:bg-black/70 text-white flex items-center justify-center">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img :src="url" alt="" class="max-w-full max-h-[90vh] object-contain" @click.stop>
        </div>
    </div>
</div>
