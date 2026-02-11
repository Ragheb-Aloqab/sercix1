<div class="space-y-4">
    @if (session('success'))
        <div class="p-4 rounded-2xl bg-emerald-500/10 text-emerald-800 dark:text-emerald-300 border border-emerald-500/20">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-500/10 text-rose-800 dark:text-rose-300 border border-rose-500/20">
            <p class="font-bold mb-2">يوجد أخطاء:</p>
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
                <p class="text-sm text-slate-500 dark:text-slate-400">تفاصيل الطلب</p>
                <h1 class="text-2xl font-black">#{{ $order->id }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    تم الإنشاء: {{ $order->created_at?->format('Y-m-d H:i') }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @include('admin.orders.partials._status_badge', ['status' => $order->status])
                <a href="{{ route('admin.orders.index') }}"
                   class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">
                    رجوع للقائمة
                </a>
                <a href="{{ route('admin.orders.invoice.show', $order) }}"
                   class="px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold">
                    <i class="fa-solid fa-print me-2"></i> الفاتورة
                </a>
                @if (!$order->invoice)
                    <button type="button" wire:click="createInvoice"
                            class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                        إنشاء فاتورة
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

            {{-- Attachments (Livewire) --}}
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
                <div class="p-5 border-b border-slate-200/70 dark:border-slate-800">
                    <h2 class="text-lg font-black">المرفقات</h2>
                </div>
                <div class="p-5 space-y-4">
                    <form wire:submit="uploadAttachment" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <select wire:model="attachment_type"
                                class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                            <option value="before_photo">before</option>
                            <option value="after_photo">after</option>
                            <option value="signature">signature</option>
                            <option value="other">other</option>
                        </select>
                        <input type="file" wire:model="attachment_file" accept="image/*"
                               class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
                        <button type="submit" class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                            رفع
                        </button>
                    </form>
                    @php
                        $before = $order->attachments->where('type', 'before_photo');
                        $after = $order->attachments->where('type', 'after_photo');
                        $others = $order->attachments->whereIn('type', ['signature', 'other']);
                    @endphp
                    <div>
                        <h3 class="font-black mb-3">صور قبل</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                            @forelse($before as $att)
                                <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                                    <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank"><img src="{{ asset('storage/' . $att->file_path) }}" class="w-full h-32 object-cover" alt=""></a>
                                    <div class="p-3 flex justify-between items-center">
                                        <span class="text-xs text-slate-500">{{ $att->created_at?->format('Y-m-d H:i') }}</span>
                                        <button type="button" wire:click="deleteAttachment({{ $att->id }})" class="text-rose-600 font-bold text-sm">حذف</button>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 col-span-full">لا توجد صور قبل.</p>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <h3 class="font-black mb-3">صور بعد</h3>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                            @forelse($after as $att)
                                <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                                    <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank"><img src="{{ asset('storage/' . $att->file_path) }}" class="w-full h-32 object-cover" alt=""></a>
                                    <div class="p-3 flex justify-between items-center">
                                        <span class="text-xs text-slate-500">{{ $att->created_at?->format('Y-m-d H:i') }}</span>
                                        <button type="button" wire:click="deleteAttachment({{ $att->id }})" class="text-rose-600 font-bold text-sm">حذف</button>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 col-span-full">لا توجد صور بعد.</p>
                            @endforelse
                        </div>
                    </div>
                    <div>
                        <h3 class="font-black mb-3">توقيع / أخرى</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @forelse($others as $att)
                                <div class="p-4 rounded-2xl border border-slate-200 dark:border-slate-800">
                                    <div class="flex justify-between items-center">
                                        <p class="font-bold">{{ $att->type }}</p>
                                        <button type="button" wire:click="deleteAttachment({{ $att->id }})" class="text-rose-600 font-bold text-sm">حذف</button>
                                    </div>
                                    <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="text-sky-600 font-semibold text-sm">فتح الملف</a>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">لا توجد مرفقات أخرى.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            {{-- Assign technician (Livewire) --}}
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <h2 class="text-lg font-black mb-3">إسناد فني</h2>
                <p class="text-sm text-slate-500 mb-3">الفني الحالي: <span class="font-bold">{{ $order->technician?->name ?? 'غير مسند' }}</span></p>
                <form wire:submit="assignTechnician" class="space-y-3">
                    <select wire:model="technician_id"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        <option value="0">اختر فني</option>
                        @foreach ($technicians as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} {{ $t->phone ? "({$t->phone})" : '' }}</option>
                        @endforeach
                    </select>
                    <textarea wire:model="assign_note" rows="3"
                              class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                              placeholder="ملاحظة (اختياري)"></textarea>
                    <button type="submit" class="w-full px-4 py-3 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white font-bold">إسناد</button>
                </form>
            </div>

            {{-- Change status (Livewire) --}}
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <h2 class="text-lg font-black mb-3">تغيير الحالة</h2>
                <form wire:submit="changeStatus" class="space-y-3">
                    <select wire:model="to_status"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        @foreach (\App\Support\OrderStatus::ALL as $st)
                            <option value="{{ $st }}">{{ $st }}</option>
                        @endforeach
                    </select>
                    <textarea wire:model="status_note" rows="3"
                              class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                              placeholder="سبب/ملاحظة (اختياري)"></textarea>
                    <button type="submit" class="w-full px-4 py-3 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-bold">تحديث</button>
                </form>
            </div>

            {{-- Payment (Livewire) + Bank receipt --}}
            @php
                $payment = $order->payment;
                $status = $payment?->status;
                $method = $payment?->method;
                $amount = (float) ($payment?->amount ?? 0);
                $statusLabel = match ($status) {
                    'paid' => 'مدفوع',
                    'pending' => 'قيد الانتظار',
                    'failed' => 'فشل الدفع',
                    default => '—',
                };
                $methodLabel = match ($method) {
                    'cash' => 'كاش',
                    'tap' => 'Tap',
                    'bank' => 'تحويل بنكي',
                    default => '—',
                };
                $badgeClass = match ($status) {
                    'paid' => 'bg-emerald-100 text-emerald-700',
                    'pending' => 'bg-amber-100 text-amber-700',
                    'failed' => 'bg-red-100 text-red-700',
                    default => 'bg-slate-100 text-slate-700',
                };
            @endphp
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <h2 class="text-lg font-black mb-3">الدفع</h2>
                <div class="text-sm mb-4">
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">{{ $statusLabel }}</span>
                        @if ($payment)
                            <span class="text-xs font-semibold">{{ number_format($amount, 2) }} SAR • {{ $methodLabel }}</span>
                        @endif
                    </div>
                </div>
                @if ($payment && $payment->method === 'bank' && $payment->status === 'pending' && $payment->receipt_path)
                    <div class="mb-4 p-4 rounded-2xl border border-sky-200 dark:border-sky-800 bg-sky-50/50 dark:bg-sky-900/20">
                        <p class="font-bold text-sm mb-2">إيصال التحويل البنكي</p>
                        @if(\Illuminate\Support\Facades\Storage::disk('public')->exists($payment->receipt_path))
                            <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" class="block rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 mb-2">
                                <img src="{{ asset('storage/' . $payment->receipt_path) }}" alt="إيصال" class="w-full max-h-48 object-contain" />
                            </a>
                            <button type="button" wire:click="confirmBankPayment({{ $payment->id }})"
                                    wire:confirm="تأكيد استلام التحويل؟"
                                    class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold">
                                تأكيد الاستلام
                            </button>
                        @else
                            <p class="text-slate-500 text-sm">لا يوجد ملف مرفق.</p>
                        @endif
                    </div>
                @endif
                <form wire:submit="storePayment" class="space-y-3">
                    <select wire:model="payment_method"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        <option value="cash">cash</option>
                        <option value="tap">tap</option>
                        <option value="bank">bank</option>
                    </select>
                    <select wire:model="payment_status"
                            class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        <option value="pending">pending</option>
                        <option value="paid">paid</option>
                        <option value="failed">failed</option>
                    </select>
                    <input type="number" step="0.01" wire:model="payment_amount"
                           class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                           placeholder="Amount SAR" />
                    <button type="submit" class="w-full px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">حفظ الدفع</button>
                </form>
            </div>

            @include('admin.orders.partials._quick_info', ['order' => $order])
        </div>
    </div>
</div>
