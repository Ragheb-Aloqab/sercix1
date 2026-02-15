@extends('admin.layouts.app')

@section('title', __('vehicles.title') . ' | SERV.X')
@section('page_title', __('vehicles.page_title'))
@section('subtitle', __('vehicles.manage_vehicles'))

@section('content')
    <div class="space-y-6">

        {{-- Header actions --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <form method="GET" action="{{ route('company.vehicles.index') }}" class="flex items-center gap-2">
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="{{ __('vehicles.search_placeholder') }}"
                    class="w-full lg:w-96 px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900" />
                <button class="px-4 py-3 rounded-2xl bg-slate-900 hover:bg-black text-white font-bold">
                    {{ __('vehicles.search') }}
                </button>
                @if (!empty($q))
                    <a href="{{ route('company.vehicles.index') }}"
                        class="px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold">
                        {{ __('vehicles.clear') }}
                    </a>
                @endif
            </form>

            <div class="flex gap-2">
                <a href="{{ route('company.fuel.index') }}" class="px-4 py-3 rounded-2xl border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-800 font-bold hover:bg-amber-100 dark:hover:bg-amber-900/40">
                    <i class="fa-solid fa-gas-pump me-2"></i>{{ __('company.fuel_report') }}
                </a>
                <a href="{{ route('company.vehicles.create') }}"
                    class="px-4 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
                    <i class="fa-solid fa-plus me-2"></i> {{ __('vehicles.add_vehicle') }}
                </a>
            </div>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 text-emerald-800 border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 text-rose-800 border border-rose-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Table --}}
        <div
            class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
            <div class="p-5 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-black">{{ __('vehicles.vehicles_list') }}</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('vehicles.total') }}: {{ $vehicles->total() }}</p>
                </div>
            </div>

            <div class="p-5">
                @if ($vehicles->count())
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-slate-500 dark:text-slate-400">
                                    <th class="text-start py-2">{{ __('vehicles.plate') }}</th>
                                    <th class="text-start py-2">{{ __('vehicles.vehicle') }}</th>
                                    <th class="text-start py-2">{{ __('vehicles.branch') }}</th>
                                    <th class="text-start py-2">{{ __('vehicles.status') }}</th>
                                    <th class="text-end py-2">{{ __('vehicles.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/70 dark:divide-slate-800">
                                @foreach ($vehicles as $v)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                        <td class="py-3 font-bold">
                                            <a href="{{ route('company.vehicles.show', $v) }}" class="text-emerald-600 dark:text-emerald-400 hover:underline">
                                                {{ $v->plate_number }}
                                            </a>
                                        </td>
                                        <td class="py-3">
                                            <a href="{{ route('company.vehicles.show', $v) }}" class="block hover:opacity-80">
                                                <div class="font-semibold">
                                                    {{ $v->make ?? $v->brand ?? '-' }} {{ $v->model ?? '' }}
                                                </div>
                                                <div class="text-xs text-slate-500 dark:text-slate-400">
                                                    {{ __('vehicles.year_label') }}: {{ $v->year ?? '-' }} — {{ __('vehicles.vin_label') }}: {{ $v->vin ?? '-' }}
                                                </div>
                                            </a>
                                        </td>
                                        <td class="py-3">
                                            {{ $v->branch?->name ?? '-' }}
                                        </td>
                                        <td class="py-3">
                                            @if ($v->is_active)
                                                <span
                                                    class="px-2 py-1 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                                                    {{ __('vehicles.active') }}
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 py-1 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 text-xs font-bold dark:bg-slate-800 dark:border-slate-700">
                                                    {{ __('vehicles.inactive') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-end">
                                            <a href="{{ route('company.vehicles.show', $v) }}"
                                                class="px-3 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold inline-flex items-center gap-2 me-2">
                                                <i class="fa-solid fa-list"></i> {{ __('vehicles.details') }}
                                            </a>
                                            <a href="{{ route('company.vehicles.edit', $v->id) }}"
                                                class="px-3 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold hover:bg-slate-50 dark:hover:bg-slate-800 inline-flex items-center gap-2">
                                                <i class="fa-solid fa-pen"></i> {{ __('common.edit') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $vehicles->links() }}
                    </div>
                @else
                    <p class="text-slate-500">{{ __('vehicles.no_vehicles') }}</p>
                @endif
            </div>
        </div>

    </div>
@endsection
