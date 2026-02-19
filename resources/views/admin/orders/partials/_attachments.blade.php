<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft">
    <div class="p-5 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-black">المرفقات</h2>
            <p class="text-sm text-slate-500 dark:text-slate-400">صور قبل/بعد/توقيع</p>
        </div>
    </div>

    <div class="p-5 space-y-6">


        <form method="POST" action="{{ route('admin.orders.attachments.store', $order) }}" enctype="multipart/form-data"
            class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @csrf

            <select name="type"
                class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">

                <option value="before_photo">before</option>
                <option value="after_photo">after</option>
                <option value="signature">signature</option>
                <option value="other">other</option>
            </select>

            <input type="file" name="file" accept="image/*"
                class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent" />

            <button type="submit"
                class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                رفع
            </button>
        </form>

        {{-- رسائل --}}
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-900 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 text-red-900 px-4 py-3">
                <ul class="list-disc ms-5 text-sm">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div>
            <h3 class="font-black mb-3">صور قبل</h3>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @forelse($before as $att)
                    <div
                        class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="block">
                            <img src="{{ asset('storage/' . $att->file_path) }}" class="w-full h-32 object-cover"
                                alt="before">
                        </a>

                        <div class="p-3 flex items-center justify-between">
                            <span class="text-xs text-slate-500">{{ $att->created_at?->format('Y-m-d H:i') }}</span>

                            <form method="POST" action="{{ route('admin.orders.attachments.destroy', $att) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 font-bold text-sm">{{ __('common.delete') }}</button>
                            </form>
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
                    <div
                        class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="block">
                            <img src="{{ asset('storage/' . $att->file_path) }}" class="w-full h-32 object-cover"
                                alt="after">
                        </a>

                        <div class="p-3 flex items-center justify-between">
                            <span class="text-xs text-slate-500">{{ $att->created_at?->format('Y-m-d H:i') }}</span>

                            <form method="POST" action="{{ route('admin.orders.attachments.destroy', $att) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 font-bold text-sm">{{ __('common.delete') }}</button>
                            </form>
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
                        <div class="flex items-center justify-between">
                            <p class="font-bold">{{ $att->type }}</p>

                            <form method="POST" action="{{ route('admin.orders.attachments.destroy', $att) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-rose-600 font-bold text-sm">{{ __('common.delete') }}</button>
                            </form>
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            {{ $att->created_at?->format('Y-m-d H:i') }}
                        </p>

                        <div class="mt-3">
                            <a class="text-sky-600 font-semibold" href="{{ asset('storage/' . $att->file_path) }}"
                                target="_blank">
                                فتح الملف
                            </a>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">لا توجد مرفقات أخرى.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
