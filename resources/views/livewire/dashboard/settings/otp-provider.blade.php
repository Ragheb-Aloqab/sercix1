<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">

    <h2 class="text-lg font-black">مزود OTP</h2>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
        إعداد مزود إرسال رمز التحقق (OTP) عبر SMS.
    </p>

    {{-- Success message --}}
    @if (session()->has('success_otp'))
        <div class="mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">
            {{ session('success_otp') }}
        </div>
    @endif

    {{-- Form --}}
    <form wire:submit.prevent="save" class="mt-5 space-y-4">

        {{-- Provider --}}
        <div>
            <label class="text-sm font-bold">مزود OTP</label>
            <select wire:model="provider"
                class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                <option value="none">بدون (تعطيل OTP)</option>
                <option value="twilio">Twilio</option>
                <option value="taqnyat">Taqnyat</option>
                <option value="msegat">Msegat</option>
                <option value="custom">Custom API</option>
            </select>
            @error('provider')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- API Key --}}
        @if ($provider !== 'none')
            <div>
                <label class="text-sm font-bold">API Key / Token</label>
                <input wire:model.defer="api_key" type="text" placeholder="أدخل مفتاح API الخاص بالمزود"
                    class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
                @error('api_key')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Sender --}}
            <div>
                <label class="text-sm font-bold">Sender ID</label>
                <input wire:model.defer="sender" type="text" placeholder="مثال: SERVX أو اسم الشركة"
                    class="mt-2 w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />
                @error('sender')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        {{-- Save --}}
        <div class="pt-2">
            <button class="px-5 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                حفظ الإعدادات
            </button>
        </div>
    </form>

    {{-- Help --}}
    <div class="mt-5 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
        <p class="font-semibold mb-1">ملاحظات:</p>
        <ul class="list-disc ms-4 space-y-1">
            <li>يتم استخدام هذه الإعدادات لإرسال رمز التحقق عند تسجيل الدخول أو إنشاء الحساب.</li>
            <li>يمكنك تعطيل OTP بالكامل باختيار "بدون".</li>
            <li>ربط Webhook أو التحقق من الإرسال يتم لاحقاً داخل Service مخصص.</li>
        </ul>
    </div>
</div>
