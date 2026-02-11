<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-900 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300 px-4 py-3">
            {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 text-red-900 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300 px-4 py-3">
            <p class="font-bold mb-1">يوجد أخطاء:</p>
            <ul class="list-disc ms-5 text-sm">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="font-black text-xl">طلب #{{ $order->id }}</p>
                <p class="text-sm text-slate-500 mt-1">الحالة: {{ $order->status }}</p>
                <p class="text-sm text-slate-500 mt-1">
                    الشركة: {{ $order->company?->company_name ?? '-' }}
                    @if ($order->company?->phone) — {{ $order->company->phone }} @endif
                </p>
            </div>
            <a href="{{ route('tech.tasks.index') }}"
               class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                رجوع
            </a>
        </div>
    </div>

    {{-- تفاصيل السائق / جهة الاتصال للخدمة --}}
    @if($order->driver_phone || $order->requested_by_name || $order->vehicle)
    <div class="rounded-3xl bg-emerald-50 dark:bg-emerald-900/20 border-2 border-emerald-200 dark:border-emerald-800 shadow-soft p-5">
        <h3 class="font-black text-lg mb-4 flex items-center gap-2">
            <span class="text-emerald-700 dark:text-emerald-400">معلومات السائق — جهة الاتصال للخدمة</span>
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            @if($order->requested_by_name)
                <div>
                    <p class="text-slate-500 dark:text-slate-400">اسم السائق</p>
                    <p class="font-bold mt-1">{{ $order->requested_by_name }}</p>
                </div>
            @endif
            @if($order->driver_phone)
                <div>
                    <p class="text-slate-500 dark:text-slate-400">جوال السائق</p>
                    <p class="font-bold mt-1 font-mono">{{ $order->driver_phone }}</p>
                    @php
                        $wa = preg_replace('/[^0-9]/', '', $order->driver_phone);
                        if (str_starts_with($wa, '0')) { $wa = '966' . substr($wa, 1); }
                        elseif (!str_starts_with($wa, '966')) { $wa = '966' . $wa; }
                        $wa = 'https://wa.me/' . $wa;
                    @endphp
                    <a href="{{ $wa }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 mt-2 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        واتساب السائق
                    </a>
                </div>
            @endif
            @if($order->vehicle)
                <div>
                    <p class="text-slate-500 dark:text-slate-400">المركبة</p>
                    <p class="font-bold mt-1">{{ $order->vehicle->plate_number ?? '—' }} {{ $order->vehicle->make ? $order->vehicle->make . ' ' . $order->vehicle->model : '' }}</p>
                </div>
            @endif
            @if($order->address || $order->city)
                <div class="sm:col-span-2">
                    <p class="text-slate-500 dark:text-slate-400">موقع الخدمة</p>
                    <p class="font-bold mt-1">{{ $order->city ? $order->city . ' — ' : '' }}{{ $order->address ?? '—' }}</p>
                </div>
            @endif
        </div>
        @if($order->services && $order->services->isNotEmpty())
            <div class="mt-4 pt-4 border-t border-emerald-200 dark:border-emerald-800">
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-2">الخدمات المطلوبة</p>
                <ul class="flex flex-wrap gap-2">
                    @foreach($order->services as $s)
                        <li class="px-3 py-1 rounded-xl bg-white dark:bg-slate-800 border border-emerald-200 dark:border-emerald-800 text-sm font-medium">{{ $s->name }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if($order->notes)
            <p class="text-slate-600 dark:text-slate-400 text-sm mt-3"><span class="font-semibold">ملاحظات:</span> {{ $order->notes }}</p>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h3 class="font-black text-lg mb-3">صور قبل</h3>
            <form method="POST" action="{{ route('tech.tasks.attachments.before', $order->id) }}"
                  enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <input type="file" name="images[]" multiple accept="image/*"
                       class="block w-full text-sm rounded-xl border border-slate-200 dark:border-slate-800 p-2" />
                <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold">رفع</button>
            </form>
            <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                @forelse($order->beforePhotos as $img)
                    <a href="{{ asset('storage/'.$img->file_path) }}" target="_blank"
                       class="block rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                        <img src="{{ asset('storage/'.$img->file_path) }}" class="w-full h-28 object-cover" alt="">
                    </a>
                @empty
                    <p class="text-sm text-slate-500 col-span-full">لا توجد صور قبل حتى الآن.</p>
                @endforelse
            </div>
        </div>
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <h3 class="font-black text-lg mb-3">صور بعد</h3>
            <form method="POST" action="{{ route('tech.tasks.attachments.after', $order->id) }}"
                  enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <input type="file" name="images[]" multiple accept="image/*"
                       class="block w-full text-sm rounded-xl border border-slate-200 dark:border-slate-800 p-2" />
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">رفع</button>
            </form>
            <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                @forelse($order->afterPhotos as $img)
                    <a href="{{ asset('storage/'.$img->file_path) }}" target="_blank"
                       class="block rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                        <img src="{{ asset('storage/'.$img->file_path) }}" class="w-full h-28 object-cover" alt="">
                    </a>
                @empty
                    <p class="text-sm text-slate-500 col-span-full">لا توجد صور بعد حتى الآن.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
        <button type="button" wire:click="confirmComplete"
                class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-black text-white font-semibold">
            تأكيد إنجاز المهمة
        </button>
    </div>
</div>
