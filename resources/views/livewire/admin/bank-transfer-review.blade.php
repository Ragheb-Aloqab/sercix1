<div class="space-y-6">
    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <h1 class="text-xl font-black">تحويلات بنكية بانتظار المراجعة</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">عرض إيصال التحويل وتأكيد أو رفض الدفع.</p>
    </div>

    <div class="space-y-4">
        @forelse($payments as $payment)
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <div class="flex flex-col lg:flex-row lg:items-start gap-6">
                    {{-- Receipt image --}}
                    <div class="flex-shrink-0">
                        @if($payment->receipt_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($payment->receipt_path))
                            <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                                <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" class="block">
                                    <img src="{{ asset('storage/' . $payment->receipt_path) }}"
                                         alt="إيصال التحويل"
                                         class="w-full max-w-sm h-64 object-contain" />
                                </a>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">
                                <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" class="text-sky-600 font-semibold">
                                    فتح الإيصال في نافذة جديدة
                                </a>
                            </p>
                        @else
                            <div class="w-48 h-32 rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-700 dark:text-amber-400 text-sm font-semibold">
                                لا يوجد ملف مرفق
                            </div>
                        @endif
                    </div>

                    {{-- Details --}}
                    <div class="flex-1 min-w-0 space-y-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">رقم الدفع</span>
                                <p class="font-bold">#{{ $payment->id }}</p>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">الطلب</span>
                                <p class="font-bold">
                                    <a href="{{ route('admin.orders.show', $payment->order_id) }}" class="text-sky-600 hover:underline">#{{ $payment->order_id }}</a>
                                </p>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">الشركة</span>
                                <p class="font-bold">{{ $payment->company?->company_name ?? '—' }}</p>
                                @if($payment->company?->phone)
                                    <p class="text-xs text-slate-500">{{ $payment->company->phone }}</p>
                                @endif
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">المبلغ</span>
                                <p class="font-bold">{{ number_format((float) $payment->amount, 2) }} SAR</p>
                            </div>
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">اسم المرسل</span>
                                <p class="font-bold">{{ $payment->sender_name ?? '—' }}</p>
                            </div>
                            @if($payment->bankAccount)
                                <div>
                                    <span class="text-slate-500 dark:text-slate-400">الحساب البنكي</span>
                                    <p class="font-bold">{{ $payment->bankAccount->bank_name }} — {{ $payment->bankAccount->account_name }}</p>
                                </div>
                            @endif
                            <div>
                                <span class="text-slate-500 dark:text-slate-400">تاريخ الرفع</span>
                                <p class="font-bold">{{ $payment->updated_at?->format('Y-m-d H:i') }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 pt-2">
                            <button type="button"
                                    wire:click="confirmPayment({{ $payment->id }})"
                                    wire:confirm="تأكيد استلام التحويل وتحديث الدفع كمدفوع؟"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                                تأكيد الاستلام
                            </button>
                            <button type="button"
                                    wire:click="rejectPayment({{ $payment->id }})"
                                    wire:confirm="رفض هذا التحويل؟"
                                    class="px-4 py-2 rounded-xl border border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-800 dark:bg-rose-900/30 dark:text-rose-400 font-bold">
                                رفض
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-10 text-center text-slate-500">
                لا توجد تحويلات بنكية بانتظار المراجعة.
            </div>
        @endforelse
    </div>

    {{ $payments->links() }}
</div>
