<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">

    <h2 class="text-lg font-black">إعدادات طرق الدفع</h2>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
        فعّل أو عطّل طرق الدفع، واضبط Tap والتحويل البنكي.
    </p>

    {{-- Success --}}
    @if (session()->has('success_tap'))
        <div class="mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">
            {{ session('success_tap') }}
        </div>
    @endif

    {{-- Form --}}
    <form wire:submit.prevent="save" class="mt-6 space-y-6">

        {{-- Payment Methods Toggles --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- Cash --}}
            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="font-black">💵 الدفع كاش</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            الدفع عند تقديم الخدمة.
                        </p>
                    </div>

                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" wire:model="enable_cash_payment" class="sr-only peer">
                        <div
                            class="w-12 h-7 rounded-full bg-slate-200 dark:bg-slate-800 peer-checked:bg-emerald-600 relative transition">
                            <span
                                class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white dark:bg-slate-900 shadow
                                transition-all peer-checked:translate-x-5"></span>
                        </div>
                    </label>
                </div>
                @error('enable_cash_payment')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Online (Tap) --}}
            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="font-black">💳 الدفع أونلاين</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Visa / MasterCard / Mada عبر Tap.
                        </p>
                    </div>

                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" wire:model="enable_online_payment" class="sr-only peer">
                        <div
                            class="w-12 h-7 rounded-full bg-slate-200 dark:bg-slate-800 peer-checked:bg-emerald-600 relative transition">
                            <span
                                class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white dark:bg-slate-900 shadow
                                transition-all peer-checked:translate-x-5"></span>
                        </div>
                    </label>
                </div>
                @error('enable_online_payment')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bank Transfer --}}
            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="font-black">🏦 التحويل البنكي</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            عرض بيانات الحساب وإرفاق إيصال التحويل.
                        </p>
                    </div>

                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input type="checkbox" wire:model="enable_bank_payment" class="sr-only peer">
                        <div
                            class="w-12 h-7 rounded-full bg-slate-200 dark:bg-slate-800 peer-checked:bg-emerald-600 relative transition">
                            <span
                                class="absolute top-0.5 start-0.5 w-6 h-6 rounded-full bg-white dark:bg-slate-900 shadow
                                transition-all peer-checked:translate-x-5"></span>
                        </div>
                    </label>
                </div>
                @error('enable_bank_payment')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Tap Settings --}}
        <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-black">إعدادات Tap Payments</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        تظهر وتُستخدم فقط عند تفعيل الدفع الأونلاين.
                    </p>
                </div>

                <span
                    class="text-xs px-3 py-1 rounded-full border
                    {{ $enable_online_payment ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-800 dark:bg-slate-900/30 dark:text-slate-300' }}">
                    {{ $enable_online_payment ? 'مفعّل' : 'غير مفعّل' }}
                </span>
            </div>

            <div class="{{ $enable_online_payment ? '' : 'opacity-60 pointer-events-none' }} mt-4 space-y-4">

                {{-- Mode --}}
                <div>
                    <label class="text-sm font-bold">وضع التشغيل</label>
                    <select wire:model="tap_mode"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        <option value="sandbox">Sandbox (اختبار)</option>
                        <option value="live">Live (إنتاج)</option>
                    </select>
                    @error('tap_mode')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- API Key --}}
                <div>
                    <label class="text-sm font-bold">
                        Tap API Key
                        <span class="text-xs text-slate-500">(حسب وضع التشغيل)</span>
                    </label>
                    <input wire:model.defer="tap_api_key" type="text" placeholder="sk_test_xxx أو sk_live_xxx"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
                    @error('tap_api_key')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Webhook Secret --}}
                <div>
                    <label class="text-sm font-bold">Webhook Secret</label>
                    <input wire:model.defer="tap_webhook_secret" type="text" placeholder="whsec_xxx"
                        class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
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
                    </ul>
                </div>
            </div>
        </div>

        {{-- Bank Settings --}}
        <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-black">التحويل البنكي</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        فعّل التحويل البنكي ثم أضف/عدّل الحسابات البنكية من صفحة الحسابات.
                    </p>
                </div>

                <span
                    class="text-xs px-3 py-1 rounded-full border
            {{ $enable_bank_payment ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-800 dark:bg-slate-900/30 dark:text-slate-300' }}">
                    {{ $enable_bank_payment ? 'مفعّل' : 'غير مفعّل' }}
                </span>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    سيتم عرض الحسابات <b>الفعّالة</b> للعملاء عند اختيار التحويل البنكي.
                </div>

                <a href="{{ route('admin.settings.bank-accounts') }}"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold text-sm">
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
        <div class="pt-2 flex items-center justify-end gap-2">
            <button class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                حفظ الإعدادات
            </button>
        </div>
    </form>

</div>
