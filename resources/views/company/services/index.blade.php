@extends('admin.layouts.app')

@section('title', ' خدمات الشركة | SERV.X')
@section('page_title', __('dashboard.services'))

@section('content')
@include('company.partials.glass-start', ['title' => __('dashboard.services')])
    <div class="space-y-6">

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/20 text-emerald-300 border border-emerald-400/50 mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 mb-6">
            <form method="GET" action="{{ route('company.services.index') }}" class="flex flex-col md:flex-row gap-3">
                <input type="text" name="q" value="{{ $q }}" placeholder="بحث عن خدمات..."
                    class="w-full px-4 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white placeholder-slate-500">
                <button class="px-5 py-3 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors">
                    بحث
                </button>
                <a href="{{ route('company.services.index') }}"
                    class="px-5 py-3 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white font-bold text-center hover:bg-slate-700/50 transition-colors">
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

                <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-black text-lg text-white">{{ $service->name ?? 'Service' }}</p>
                            @if (!empty($service->description))
                                <p class="text-sm text-slate-400 mt-1 line-clamp-2">{{ $service->description }}</p>
                            @endif
                        </div>

                        <span class="text-xs font-bold px-3 py-1 rounded-xl {{ $enabled ? 'bg-emerald-500/30 text-emerald-300 border border-emerald-400/50' : 'bg-slate-600/30 text-slate-400 border border-slate-500/50' }}">
                            {{ $enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>

                    <div class="mt-4 text-sm space-y-2">
                        <div class="flex items-center justify-between py-2 border-b border-slate-600/50">
                            <span class="font-bold text-white">{{ is_null($price) ? '-' : number_format((float) $price, 2) . ' ' . __('company.sar') }}</span>
                            <span class="text-slate-400">السعر</span>
                        </div>

                        <div class="flex items-center justify-between py-2">
                            <span class="font-bold text-white">{{ is_null($minutes) ? '-' : (int) $minutes . ' min' }}</span>
                            <span class="text-slate-400">الوقت المتوقع</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-6 rounded-2xl bg-slate-800/40 border border-slate-500/30 text-center text-slate-500">
                    لا خدمات متوفرة
                </div>
            @endforelse
        </div>

        @if ($services->hasPages())
            <div class="mt-6">
                {{ $services->links() }}
            </div>
        @endif

    </div>
@include('company.partials.glass-end')
@endsection
