<div class="rounded-2xl bg-white dark:bg-slate-800/40 border border-slate-200 dark:border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-300 dark:hover:border-slate-400/50 transition-all duration-300 overflow-hidden mb-6 shadow-sm dark:shadow-none">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[500px]">
            <thead class="border-b border-slate-200 dark:border-slate-600/50">
                <tr class="text-slate-600 dark:text-slate-400">
                    <th class="p-4 text-end font-bold">{{ __('invoice.invoice_number_label') }}</th>
                    @if($invoiceType === 'maintenance')
                        <th class="p-4 text-end font-bold">{{ __('maintenance.center_name') }}</th>
                    @else
                        <th class="p-4 text-end font-bold">{{ __('invoice.driver_name') }}</th>
                    @endif
                    <th class="p-4 text-end font-bold">{{ __('invoice.date_label') }}</th>
                    <th class="p-4 text-end font-bold">{{ __('invoice.total') }}</th>
                    <th class="p-4 text-end font-bold">{{ __('common.actions') ?? 'إجراء' }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-600/50">
                @if($invoiceType === 'maintenance')
                    @foreach($companyMaintenanceInvoices ?? [] as $inv)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors duration-300">
                            <td class="p-4 font-bold text-slate-900 dark:text-white text-end">#{{ $inv->id }}</td>
                            <td class="p-4 text-slate-600 dark:text-slate-400 text-end">{{ __('common.uploaded') }}</td>
                            <td class="p-4 text-slate-600 dark:text-slate-400 text-end">{{ $inv->created_at?->format('Y-m-d') ?? '-' }}</td>
                            <td class="p-4 font-semibold text-slate-900 dark:text-white text-end">{{ number_format((float) ($inv->amount ?? 0), 2) }} {{ __('company.sar') }}</td>
                            <td class="p-3 sm:p-4 text-end">
                                <div class="flex flex-wrap gap-2 justify-end">
                                    <a href="{{ route('company.maintenance-invoices.company.view', $inv) }}" target="_blank"
                                        class="inline-flex items-center justify-center gap-1 px-3 py-2 min-h-[44px] rounded-xl border border-slate-300 dark:border-slate-500/50 font-semibold text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-300">
                                        <i class="fa-solid fa-eye shrink-0"></i><span>{{ __('invoice.view_details') }}</span>
                                    </a>
                                    <a href="{{ route('company.maintenance-invoices.company.download', $inv) }}"
                                        class="inline-flex items-center justify-center gap-1 px-3 py-2 min-h-[44px] rounded-xl bg-sky-600 hover:bg-sky-700 dark:hover:bg-sky-500 text-white font-semibold transition-colors duration-300">
                                        <i class="fa-solid fa-download shrink-0"></i><span>{{ __('invoice.download_invoice') }}</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @foreach($maintenanceInvoices as $req)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors duration-300">
                            <td class="p-4 font-bold text-slate-900 dark:text-white text-end">#{{ $req->id }}</td>
                            <td class="p-4 text-slate-900 dark:text-white text-end">{{ $req->approvedCenter?->name ?? '-' }}</td>
                            <td class="p-4 text-slate-600 dark:text-slate-400 text-end">{{ $req->final_invoice_uploaded_at?->format('Y-m-d') ?? '-' }}</td>
                            <td class="p-4 font-semibold text-slate-900 dark:text-white text-end">{{ number_format((float) ($req->final_invoice_amount ?? $req->approved_quote_amount ?? 0), 2) }} {{ __('company.sar') }}</td>
                            <td class="p-3 sm:p-4 text-end">
                                <div class="flex flex-wrap gap-2 justify-end">
                                    <a href="{{ route('company.maintenance-invoices.view', $req) }}" target="_blank"
                                        class="inline-flex items-center justify-center gap-1 px-3 py-2 min-h-[44px] rounded-xl border border-slate-300 dark:border-slate-500/50 font-semibold text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-300">
                                        <i class="fa-solid fa-eye shrink-0"></i><span>{{ __('invoice.view_details') }}</span>
                                    </a>
                                    <a href="{{ route('company.maintenance-invoices.download', $req) }}"
                                        class="inline-flex items-center justify-center gap-1 px-3 py-2 min-h-[44px] rounded-xl bg-sky-600 hover:bg-sky-700 dark:hover:bg-sky-500 text-white font-semibold transition-colors duration-300">
                                        <i class="fa-solid fa-download shrink-0"></i><span>{{ __('invoice.download_invoice') }}</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if(($companyMaintenanceInvoices ?? collect())->isEmpty() && $maintenanceInvoices->isEmpty())
                        <tr>
                            <td colspan="5" class="p-6 text-center text-slate-500 dark:text-slate-500">{{ __('maintenance.no_invoices') ?? __('invoice.no_invoices') }}</td>
                        </tr>
                    @endif
                @else
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors duration-300">
                            <td class="p-4 font-bold text-slate-900 dark:text-white text-end">{{ $invoice->invoice_number ?? '#' . $invoice->id }}</td>
                            <td class="p-4 text-slate-900 dark:text-white text-end">{{ $invoice->driver_name ?? '-' }}</td>
                            <td class="p-4 text-slate-600 dark:text-slate-400 text-end">{{ optional($invoice->created_at)->format('Y-m-d') }}</td>
                            <td class="p-4 font-semibold text-slate-900 dark:text-white text-end">{{ number_format((float) ($invoice->total ?? 0), 2) }} {{ __('company.sar') }}</td>
                            <td class="p-3 sm:p-4 text-end">
                                <div class="flex flex-wrap gap-2 justify-end">
                                    <a href="{{ route('company.invoices.show', $invoice) }}"
                                        class="inline-flex items-center justify-center gap-1 px-3 py-2 min-h-[44px] rounded-xl border border-slate-300 dark:border-slate-500/50 font-semibold text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-300">
                                        <i class="fa-solid fa-eye shrink-0"></i><span>{{ __('invoice.view_details') }}</span>
                                    </a>
                                    <a href="{{ route('company.invoices.pdf', $invoice) }}"
                                        download="invoice-{{ $invoice->invoice_number ?? $invoice->id }}.pdf"
                                        class="inline-flex items-center justify-center gap-1 px-3 py-2 min-h-[44px] rounded-xl bg-sky-600 hover:bg-sky-700 dark:hover:bg-sky-500 text-white font-semibold transition-colors duration-300">
                                        <i class="fa-solid fa-file-pdf shrink-0"></i><span>{{ __('invoice.download_invoice') }}</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-slate-500 dark:text-slate-500">{{ __('invoice.no_invoices') }}</td>
                        </tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>
</div>
