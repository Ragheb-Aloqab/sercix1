@extends('admin.layouts.app')

@section('title', 'تفاصيل المهمة | SERV.X')
@section('page_title', 'تفاصيل المهمة')

@section('content')
    <div class="space-y-6">

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-900 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 text-red-900 px-4 py-3">
                <p class="font-bold mb-1">يوجد أخطاء:</p>
                <ul class="list-disc ms-5 text-sm">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="font-black text-xl">طلب #{{ $order->id }}</p>
                    <p class="text-sm text-slate-500 mt-1">الحالة: {{ $order->status }}</p>
                    <p class="text-sm text-slate-500 mt-1">
                        الشركة: {{ $order->company?->company_name ?? '-' }}
                        @if ($order->company?->phone)
                            — {{ $order->company->phone }}
                        @endif
                    </p>
                </div>

                <a href="{{ route('tech.tasks.index') }}"
                    class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 font-semibold">
                    رجوع
                </a>
            </div>
        </div>

        {{-- Upload Before/After --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            {{-- Before Upload --}}
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <h3 class="font-black text-lg mb-3">صور قبل</h3>

                <form method="POST"
                      action="{{ route('tech.tasks.attachments.before', $order->id) }}"
                      enctype="multipart/form-data"
                      class="flex items-center gap-3">
                    @csrf

                    {{-- ✅ لازم يكون images[] --}}
                    <input type="file" name="images[]" multiple accept="image/*"
                           class="block w-full text-sm rounded-xl border border-slate-200 dark:border-slate-800 p-2" />

                    <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold">
                        رفع
                    </button>
                </form>

                {{-- Before Gallery --}}
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @forelse($order->beforePhotos as $img)
                        <a href="{{ asset('storage/'.$img->file_path) }}" target="_blank"
                           class="block rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800">
                            <img src="{{ (asset('storage/'.$img->file_path)) }}" class="w-full h-28 object-cover" alt="">
                        </a>
                    @empty
                        <p class="text-sm text-slate-500 col-span-full">لا توجد صور قبل حتى الآن.</p>
                    @endforelse
                </div>
            </div>

            {{-- After Upload --}}
            <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                <h3 class="font-black text-lg mb-3">صور بعد</h3>

                <form method="POST"
                      action="{{ route('tech.tasks.attachments.after', $order->id) }}"
                      enctype="multipart/form-data"
                      class="flex items-center gap-3">
                    @csrf

                    {{-- ✅ لازم يكون images[] --}}
                    <input type="file" name="images[]" multiple accept="image/*"
                           class="block w-full text-sm rounded-xl border border-slate-200 dark:border-slate-800 p-2" />

                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold">
                        رفع
                    </button>
                </form>

                {{-- After Gallery --}}
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

        {{-- Confirm Complete --}}
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <form method="POST" action="{{ route('tech.tasks.confirmComplete', $order->id) }}">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-black text-white font-semibold">
                    تأكيد إنجاز المهمة
                </button>
            </form>
        </div>

    </div>
@endsection
