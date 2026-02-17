<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5 sm:p-6">

    <div class="mb-6">
        <h2 class="text-lg font-black text-slate-900 dark:text-white">إعدادات طرق الدفع</h2>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            فعّل أو عطّل طرق الدفع، واضبط Tap والتحويل البنكي.
        </p>
    </div>

    {{-- Success --}}
    @if (session()->has('success_tap'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-semibold">
            {{ session('success_tap') }}
        </div>
    @endif

    {{-- Form --}}
    <form wire:submit.prevent="save" class="space-y-6">

        {{-- Payment Methods Toggles --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- Cash --}}
            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 p-4 hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-black text-slate-900 dark:text-white">💵 الدفع كاش</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            الدفع عند تقديم الخدمة.
                        </p>
                    </div>

                    <label class="inline-flex items-center cursor-pointer select-none shrink-0">
                        <input type="checkbox" wire:model="enable_cash_payment" class="sr-only peer">
                        <div class="w-12 h-7 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-emerald-600 relative transition-colors duration-200">
                            <span class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white dark:bg-slate-900 shadow-md transition-all duration-200 peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
                        </div>
                    </label>
                </div>
                @error('enable_cash_payment')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Online (Tap) --}}
            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 p-4 hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-black text-slate-900 dark:text-white">💳 الدفع أونلاين</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Visa / MasterCard / Mada عبر Tap.
                        </p>
                    </div>

                    <label class="inline-flex items-center cursor-pointer select-none shrink-0">
                        <input type="checkbox" wire:model="enable_online_payment" class="sr-only peer">
                        <div class="w-12 h-7 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-emerald-600 relative transition-colors duration-200">
                            <span class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white dark:bg-slate-900 shadow-md transition-all duration-200 peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
                        </div>
                    </label>
                </div>
                @error('enable_online_payment')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bank Transfer --}}
            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 p-4 hover:border-slate-300 dark:hover:border-slate-700 transition-colors sm:col-span-2 lg:col-span-1">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="font-black text-slate-900 dark:text-white">🏦 التحويل البنكي</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            عرض بيانات الحساب وإرفاق إيصال التحويل.
                        </p>
                    </div>

                    <label class="inline-flex items-center cursor-pointer select-none shrink-0">
                        <input type="checkbox" wire:model="enable_bank_payment" class="sr-only peer">
                        <div class="w-12 h-7 rounded-full bg-slate-200 dark:bg-slate-700 peer-checked:bg-emerald-600 relative transition-colors duration-200">
                            <span class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white dark:bg-slate-900 shadow-md transition-all duration-200 peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
                        </div>
                    </label>
                </div>
                @error('enable_bank_payment')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Tap Settings --}}
        <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="font-black text-slate-900 dark:text-white">إعدادات Tap Payments</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        تظهر وتُستخدم فقط عند تفعيل الدفع الأونلاين.
                    </p>
                </div>

                <span class="text-xs px-3 py-1.5 rounded-full border shrink-0
                    {{ $enable_online_payment ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200' : 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400' }}">
                    {{ $enable_online_payment ? 'مفعّل' : 'غير مفعّل' }}
                </span>
            </div>

            <div class="{{ $enable_online_payment ? '' : 'opacity-60 pointer-events-none' }} mt-4 space-y-4">

                {{-- Mode --}}
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">وضع التشغيل</label>
                    <select wire:model="tap_mode"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800/50 bg-transparent focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:focus:ring-emerald-600 dark:focus:border-emerald-600 transition">
                        <option value="sandbox">Sandbox (اختبار)</option>
                        <option value="live">Live (إنتاج)</option>
                    </select>
                    @error('tap_mode')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- API Key --}}
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">
                        Tap Secret API Key
                        <span class="text-xs text-slate-500">(حسب وضع التشغيل)</span>
                    </label>
                    <input wire:model.defer="tap_api_key" type="text" placeholder="sk_test_xxx أو sk_live_xxx"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800/50 bg-transparent focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:focus:ring-emerald-600 dark:focus:border-emerald-600 transition" />
                    @error('tap_api_key')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Publishable Key (for embedded card form) --}}
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Publishable Key</label>
                    <input wire:model.defer="tap_publishable_key" type="text" placeholder="pk_test_xxx أو pk_live_xxx"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800/50 bg-transparent focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:focus:ring-emerald-600 dark:focus:border-emerald-600 transition" />
                    @error('tap_publishable_key')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-slate-500">مطلوب لعرض نموذج إدخال البطاقة في الصفحة.</p>
                </div>

                {{-- Merchant ID --}}
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Merchant ID</label>
                    <input wire:model.defer="tap_merchant_id" type="text" placeholder="599424"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800/50 bg-transparent focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:focus:ring-emerald-600 dark:focus:border-emerald-600 transition" />
                    @error('tap_merchant_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Webhook Secret --}}
                <div>
                    <label class="text-sm font-bold text-slate-700 dark:text-slate-300">Webhook Secret</label>
                    <input wire:model.defer="tap_webhook_secret" type="text" placeholder="whsec_xxx"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 dark:bg-slate-800/50 bg-transparent focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:focus:ring-emerald-600 dark:focus:border-emerald-600 transition" />
                    @error('tap_webhook_secret')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    <p class="font-semibold mb-1">ملاحظات:</p>
                    <ul class="list-disc ms-4 space-y-1">
                        <li>في وضع <b>Sandbox</b> يتم استخدام مفاتيح الاختبار فقط.</li>
                        <li>في وضع <b>Live</b> تأكد من تفعيل Webhook داخل لوحة Tap.</li>
                        <li>Webhook Secret للتحقق من صحة الإشعارات القادمة من Tap.</li>
                        <li>Webhook URL: <code class="bg-slate-100 dark:bg-slate-800 px-1 rounded">{{ url('/payments/tap/webhook') }}</code> — Tap لا يرسل إلى localhost، استخدم ngrok أو استضافة فعلية للاختبار.</li>
                    </ul>
                </div>
            </div>
        </div>
        

        {{-- Bank Settings --}}
        <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 p-4 sm:p-5">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="font-black text-slate-900 dark:text-white">التحويل البنكي</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        فعّل التحويل البنكي ثم أضف/عدّل الحسابات البنكية من صفحة الحسابات.
                    </p>
                </div>

                <span class="text-xs px-3 py-1.5 rounded-full border shrink-0
                    {{ $enable_bank_payment ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200' : 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-400' }}">
                    {{ $enable_bank_payment ? 'مفعّل' : 'غير مفعّل' }}
                </span>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    سيتم عرض الحسابات <b>الفعّالة</b> للعملاء عند اختيار التحويل البنكي.
                </div>

                <a href="{{ route('admin.settings.bank-accounts') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 font-bold text-sm text-slate-900 dark:text-white transition-colors">
                    <i class="fa-solid fa-landmark"></i>
                    إدارة الحسابات البنكية
                </a>
            </div>

            {{-- Optional: Preview count --}}
            <div
                class="mt-4 p-3 rounded-2xl bg-slate-50 dark:bg-slate-900/30 border border-slate-200/70 dark:border-slate-800 text-sm">
                <div class="flex items-center justify-between">
                    <span class="font-semibold">عدد الحسابات الفعّالة:</span>
                    <span class="font-black">{{ $activeBankAccountsCount ?? '-' }}</span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    (للعرض فقط) الحسابات يتم إدارتها من صفحة الحسابات البنكية.
                </p>
            </div>
        </div>


        {{-- Save --}}
        <div class="pt-4 border-t border-slate-200/70 dark:border-slate-800 flex items-center justify-end gap-2">
            <button type="submit"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-soft transition-colors disabled:opacity-50"
                wire:loading.attr="disabled">
                <i class="fa-solid fa-check-circle" wire:loading.remove wire:target="save"></i>
                <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="save"></i>
                حفظ الإعدادات
            </button>
        </div>
    </form>

</div>
