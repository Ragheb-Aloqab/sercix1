@extends('admin.layouts.app')

@section('title', ' خدمات الشركة | SERV.X')
@section('page_title', 'خدمات الشركة ')

@section('content')
    <div class="space-y-6">

        @if (session('success'))
            <div class="p-4 rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <form method="GET" action="{{ route('company.services.index') }}" class="flex flex-col md:flex-row gap-3">
                <input type="text" name="q" value="{{ $q }}" placeholder="بحث عن خدمات..."
                    class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                <button class="px-5 py-3 rounded-2xl bg-slate-900 hover:bg-black text-white font-bold">
                    بحث
                </button>
                <a href="{{ route('company.services.index') }}"
                    class="px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold text-center">
                    ارجاع
                </a>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse($services as $service)
                @php
                    $enabled = is_null($service->pivot_is_enabled) || (bool)$service->pivot_is_enabled;
                     $price = $service->base_price ?? null;
                     $minutes = $service->estimated_minutes ?? null;
                @endphp

                <div
                    class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-black text-lg">{{ $service->name ?? 'Service' }}</p>
                            @if (!empty($service->description))
                                <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $service->description }}</p>
                            @endif
                        </div>

                        <span
                            class="text-xs font-bold px-3 py-1 rounded-xl {{ $enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                            {{ $enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>

                    <div class="mt-4 text-sm space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">السعر</span>
                            <span class="font-bold">
                                {{ is_null($price) ? '-' : number_format((float) $price, 2) . ' SAR' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">الوقت المتوقع </span>
                            <span class="font-bold">
                                {{ is_null($minutes) ? '-' : (int) $minutes . ' min' }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="col-span-full p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 text-center text-slate-500">
                    لا خدمات متوفرة
                </div>
            @endforelse
        </div>

        @if ($services->hasPages())
            <div class="p-2">
                {{ $services->links() }}
            </div>
        @endif

    </div>
@endsection
