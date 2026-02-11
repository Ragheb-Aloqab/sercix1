<div class="space-y-6">
    @if (session('error'))
        <div class="p-4 rounded-2xl border border-rose-200 bg-rose-50 text-rose-800 dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-300">
            {{ session('error') }}
        </div>
    @endif

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-black text-xl">إنشاء طلب</p>
                <p class="text-sm text-slate-500 mt-1">اختر المركبة والخدمات وطريقة الدفع.</p>
            </div>
            <a href="{{ route('company.orders.index') }}"
               class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                رجوع
            </a>
        </div>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
        <div class="p-5 border-b border-slate-200/70 dark:border-slate-800">
            <h2 class="text-lg font-black">تفاصيل الطلب</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">سيتم إنشاء الطلب بحالة: معلق.</p>
        </div>
        <div class="p-5">
            <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">المركبة</label>
                    <select wire:model="vehicle_id" required
                            class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        <option value="">اختر المركبة</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">{{ $vehicle->name ?? $vehicle->plate_number ?? ('مركبة #' . $vehicle->id) }}</option>
                        @endforeach
                    </select>
                    @error('vehicle_id')
                        <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">الخدمات</label>
                    <div class="mt-2 space-y-2">
                        @foreach ($services as $service)
                            @php
                                $price = $service->pivot_base_price ?? $service->base_price ?? null;
                                $minutes = $service->pivot_estimated_minutes ?? null;
                            @endphp
                            <label class="flex items-center justify-between gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 cursor-pointer">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" wire:model="service_ids" value="{{ $service->id }}"
                                           class="h-5 w-5 rounded border-slate-300">
                                    <div>
                                        <div class="font-bold">{{ $service->name }}</div>
                                        <div class="text-xs text-slate-500">
                                            @if ($price !== null) {{ number_format((float) $price, 2) }} SAR @else - @endif
                                            @if ($minutes !== null) <span class="mx-2">•</span> {{ (int) $minutes }} د @endif
                                        </div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('service_ids')
                        <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">الفرع</label>
                    <select wire:model="company_branch_id"
                            class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        <option value="">بدون فرع</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('company_branch_id')
                        <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">ملاحظات</label>
                    <textarea wire:model="notes" rows="4"
                              class="mt-1 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent"
                              placeholder="..."></textarea>
                    @error('notes')
                        <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400">طريقة الدفع</label>
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 cursor-pointer">
                            <input type="radio" wire:model="payment_method" value="cash" class="h-5 w-5">
                            <span class="font-bold">كاش</span>
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 cursor-pointer">
                            <input type="radio" wire:model="payment_method" value="tap" class="h-5 w-5">
                            <span class="font-bold">أونلاين (Tap)</span>
                        </label>
                        <label class="flex items-center gap-3 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 cursor-pointer">
                            <input type="radio" wire:model="payment_method" value="bank" class="h-5 w-5">
                            <span class="font-bold">تحويل بنكي</span>
                        </label>
                    </div>
                    @error('payment_method')
                        <p class="text-sm text-rose-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 flex flex-col sm:flex-row gap-2 pt-2">
                    <button type="submit" wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold disabled:opacity-70">
                        <span wire:loading.remove>إنشاء الطلب</span>
                        <span wire:loading><i class="fa-solid fa-spinner fa-spin me-1"></i> جاري الإنشاء...</span>
                    </button>
                    <a href="{{ route('company.orders.index') }}"
                       class="w-full sm:w-auto px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 text-center font-bold">
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
