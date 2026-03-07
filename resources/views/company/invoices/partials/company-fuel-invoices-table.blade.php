<div class="rounded-2xl bg-white dark:bg-slate-800/40 border border-slate-200 dark:border-amber-500/30 p-5 mb-6 shadow-sm dark:shadow-none transition-colors duration-300">
    <h3 class="text-lg font-bold text-amber-700 dark:text-amber-400 mb-4">{{ __('invoice.add_fuel_invoice') }} — {{ __('common.uploaded') }}</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[500px]">
            <thead class="border-b border-slate-200 dark:border-slate-600/50">
                <tr class="text-slate-600 dark:text-slate-400">
                    <th class="p-4 text-end font-bold">{{ __('common.preview') }}</th>
                    <th class="p-4 text-end font-bold">{{ __('driver.vehicle') }}</th>
                    <th class="p-4 text-end font-bold">{{ __('invoice.date_label') }}</th>
                    <th class="p-4 text-end font-bold">{{ __('invoice.total') }}</th>
                    <th class="p-4 text-end font-bold">{{ __('common.actions') ?? 'إجراء' }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-600/50">
                @foreach($companyFuelInvoices as $inv)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors duration-300">
                        <td class="p-4 text-end">
                            @if($inv->invoice_file && $inv->isImage())
                                <button type="button"
                                    @click="$dispatch('open-image-preview', { url: '{{ route('company.fuel-invoices.view', $inv) }}' })"
                                    class="block w-16 h-16 rounded-lg overflow-hidden border border-slate-300 dark:border-slate-600/50 hover:border-amber-500/50 transition-colors cursor-pointer ms-auto"
                                    title="{{ __('common.view') }}">
                                    <img src="{{ route('company.fuel-invoices.thumbnail', $inv) }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                </button>
                            @elseif($inv->invoice_file && $inv->isPdf())
                                <a href="{{ route('company.fuel-invoices.view', $inv) }}" target="_blank"
                                    class="inline-flex w-16 h-16 rounded-lg bg-red-500/20 border border-red-400/50 items-center justify-center hover:bg-red-500/30 transition-colors ms-auto"
                                    title="{{ __('common.view') }}">
                                    <i class="fa-solid fa-file-pdf text-2xl text-red-600 dark:text-red-400"></i>
                                </a>
                            @else
                                <span class="inline-flex w-16 h-16 rounded-lg bg-slate-100 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600/50 items-center justify-center ms-auto text-slate-400 text-xs">{{ __('common.no_file') }}</span>
                            @endif
                        </td>
                        <td class="p-4 text-slate-900 dark:text-white text-end">{{ $inv->vehicle?->plate_number ?? '-' }}</td>
                        <td class="p-4 text-slate-600 dark:text-slate-400 text-end">{{ $inv->created_at?->format('Y-m-d') ?? '-' }}</td>
                        <td class="p-4 font-semibold text-slate-900 dark:text-white text-end">{{ $inv->amount ? number_format($inv->amount, 2) . ' ' . __('company.sar') : '-' }}</td>
                        <td class="p-4 text-end">
                            <div class="flex flex-wrap gap-2 justify-end">
                                @if($inv->invoice_file)
                                    @if($inv->isImage())
                                        <button type="button" @click="$dispatch('open-image-preview', { url: '{{ route('company.fuel-invoices.view', $inv) }}' })"
                                            class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 dark:hover:bg-sky-500 text-white font-semibold transition-colors duration-300">
                                            <i class="fa-solid fa-eye shrink-0"></i><span>{{ __('common.view') }}</span>
                                        </button>
                                    @else
                                        <a href="{{ route('company.fuel-invoices.view', $inv) }}" target="_blank"
                                            class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 dark:hover:bg-sky-500 text-white font-semibold transition-colors duration-300">
                                            <i class="fa-solid fa-eye shrink-0"></i><span>{{ __('common.view') }}</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('company.fuel-invoices.download', $inv) }}"
                                        class="inline-flex items-center gap-1 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-500/50 font-semibold text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-300">
                                        <i class="fa-solid fa-download shrink-0"></i><span>{{ __('invoice.download_invoice') }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-500 text-sm">—</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
