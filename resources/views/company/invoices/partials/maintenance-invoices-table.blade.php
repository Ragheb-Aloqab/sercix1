<div class="rounded-2xl bg-white dark:bg-slate-800/40 border border-slate-200 dark:border-slate-500/30 p-5 backdrop-blur-sm overflow-hidden mb-6 shadow-sm dark:shadow-none transition-colors duration-300">
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[500px]">
            <thead class="border-b border-slate-200 dark:border-slate-600/50">
                <tr class="text-slate-600 dark:text-slate-400">
                    <th class="p-4 text-end font-bold">#</th>
                    <th class="p-4 text-end font-bold">{{ __('maintenance.center_name') }}</th>
                    <th class="p-4 text-end font-bold">{{ __('driver.vehicle') }}</th>
                    <th class="p-4 text-end font-bold">{{ __('invoice.date_label') }}</th>
                    <th class="p-4 text-end font-bold">{{ __('invoice.total') }}</th>
                    <th class="p-4 text-end font-bold">{{ __('common.actions') ?? 'إجراء' }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-600/50">
                @foreach($maintenanceInvoices as $req)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors duration-300">
                        <td class="p-4 font-bold text-slate-900 dark:text-white text-end">#{{ $req->id }}</td>
                        <td class="p-4 text-slate-900 dark:text-white text-end">{{ $req->approvedCenter?->name ?? '-' }}</td>
                        <td class="p-4 text-slate-900 dark:text-white text-end">{{ $req->vehicle?->plate_number ?? '-' }}</td>
                        <td class="p-4 text-slate-600 dark:text-slate-400 text-end">{{ $req->final_invoice_uploaded_at?->format('Y-m-d') ?? '-' }}</td>
                        <td class="p-4 font-semibold text-slate-900 dark:text-white text-end">{{ number_format((float) ($req->final_invoice_amount ?? $req->approved_quote_amount ?? 0), 2) }} {{ __('company.sar') }}</td>
                        <td class="p-3 sm:p-4 text-end">
                            <a href="{{ route('company.maintenance-invoices.view', $req) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl border border-slate-300 dark:border-slate-500/50 font-semibold text-slate-700 dark:text-white hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-300">
                                <i class="fa-solid fa-eye"></i> {{ __('common.view') }}
                            </a>
                            <a href="{{ route('company.maintenance-invoices.download', $req) }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 dark:hover:bg-sky-500 text-white font-semibold transition-colors duration-300">
                                <i class="fa-solid fa-download"></i> {{ __('common.download') }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
