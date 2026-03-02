@extends('admin.layouts.app')

@section('title', __('maintenance.maintenance_centers') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('maintenance.maintenance_centers'))

@section('content')
    <div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
        <div class="dashboard-content max-w-7xl mx-auto space-y-6">
            {{-- Header --}}
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="text-center sm:text-start">
                    <h1 class="dash-page-title">{{ __('maintenance.maintenance_centers') }}</h1>
                    <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
                    <p class="text-sm text-slate-400 mt-1">{{ __('maintenance.admin_centers_desc') ?? 'Manage all maintenance centers (Super Admin only)' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.maintenance-centers.create') }}" class="dash-btn dash-btn-primary">
                        <i class="fa-solid fa-plus me-2"></i>{{ __('common.add') }} {{ __('maintenance.center') }}
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="dash-btn dash-btn-secondary">
                        <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="dash-card border-emerald-500/30 bg-emerald-500/10">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="dash-card border-red-500/30 bg-red-500/10">
                    <ul class="list-disc ms-6 text-red-300">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Filters --}}
            <div class="dash-card">
                <form method="GET" action="{{ route('admin.maintenance-centers.index') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-3">
                    <div class="lg:col-span-6">
                        <label class="text-xs text-slate-500">{{ __('common.search') }}</label>
                        <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="{{ __('maintenance.search_placeholder') ?? 'Name, phone, city...' }}"
                            class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                    </div>
                    <div class="lg:col-span-3">
                        <label class="text-xs text-slate-500">{{ __('maintenance.status') ?? 'Status' }}</label>
                        <select name="status" class="mt-1 w-full rounded-xl border-slate-200 dark:border-slate-800 dark:bg-slate-950">
                            <option value="all" @selected(($status ?? 'all') === 'all')>{{ __('common.all') ?? 'All' }}</option>
                            <option value="active" @selected(($status ?? 'all') === 'active')>{{ __('maintenance.active') ?? 'Active' }}</option>
                            <option value="suspended" @selected(($status ?? 'all') === 'suspended')>{{ __('maintenance.suspended') ?? 'Suspended' }}</option>
                        </select>
                    </div>
                    <div class="lg:col-span-3 flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 font-semibold">
                            <i class="fa-solid fa-magnifying-glass me-2"></i>{{ __('common.apply') ?? 'Apply' }}
                        </button>
                        <a href="{{ route('admin.maintenance-centers.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">{{ __('common.reset') ?? 'Reset' }}</a>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="dash-card overflow-hidden p-0">
                <div class="px-5 py-4 border-b border-slate-700 flex items-center justify-between">
                    <p class="font-bold">{{ __('maintenance.centers_list') ?? 'Centers List' }}</p>
                    <p class="text-sm text-slate-500">{{ $centers->total() }} {{ __('maintenance.center') }}</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-950">
                            <tr class="text-slate-600 dark:text-slate-300">
                                <th class="text-start px-4 py-3">{{ __('maintenance.center_name') }}</th>
                                <th class="text-start px-4 py-3">{{ __('maintenance.status') ?? 'Status' }}</th>
                                <th class="text-start px-4 py-3">{{ __('maintenance.services_completed') ?? 'Services Completed' }}</th>
                                <th class="text-start px-4 py-3">{{ __('maintenance.total_revenue') ?? 'Total Revenue' }}</th>
                                <th class="text-start px-4 py-3">{{ __('maintenance.outstanding_balance') ?? 'Outstanding' }}</th>
                                <th class="text-start px-4 py-3">{{ __('maintenance.rating') ?? 'Rating' }}</th>
                                <th class="text-start px-4 py-3">{{ __('common.created_at') ?? 'Created' }}</th>
                                <th class="text-end px-4 py-3">{{ __('common.actions') ?? 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($centers as $c)
                                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-950/40">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.maintenance-centers.show', $c) }}" class="font-bold text-slate-900 dark:text-white hover:text-sky-500">{{ $c->name }}</a>
                                        <p class="text-xs text-slate-500">{{ $c->phone }} · {{ $c->city ?? '-' }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($c->status === 'active')
                                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ __('maintenance.active') ?? 'Active' }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800">
                                                <span class="w-2 h-2 rounded-full bg-amber-500"></span> {{ __('maintenance.suspended') ?? 'Suspended' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $c->completed_jobs_count ?? $c->total_completed_jobs ?? 0 }}</td>
                                    <td class="px-4 py-3 font-semibold">{{ number_format((float) ($c->total_earnings ?? 0), 2) }} {{ __('company.sar') ?? 'SAR' }}</td>
                                    <td class="px-4 py-3">{{ number_format((float) max(0, ($c->total_earnings ?? 0) - ($c->paid_amount ?? 0)), 2) }} {{ __('company.sar') ?? 'SAR' }}</td>
                                    <td class="px-4 py-3">{{ $c->rating ? number_format((float) $c->rating, 1) : '-' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $c->created_at?->format('Y-m-d') ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.maintenance-centers.show', $c) }}" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">
                                                <i class="fa-solid fa-eye me-1"></i>{{ __('common.view') ?? 'View' }}
                                            </a>
                                            <a href="{{ route('admin.maintenance-centers.edit', $c) }}" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold">
                                                <i class="fa-solid fa-pen-to-square me-1"></i>{{ __('common.edit') ?? 'Edit' }}
                                            </a>
                                            <form method="POST" action="{{ route('admin.maintenance-centers.toggle-status', $c) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-2 rounded-xl font-semibold {{ $c->status === 'active' ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}"
                                                    onclick="return confirm('{{ $c->status === 'active' ? (__('maintenance.confirm_suspend') ?? 'Suspend this center?') : (__('maintenance.confirm_activate') ?? 'Activate this center?') }}');">
                                                    <i class="fa-solid {{ $c->status === 'active' ? 'fa-pause' : 'fa-play' }} me-1"></i>
                                                    {{ $c->status === 'active' ? (__('maintenance.suspend') ?? 'Suspend') : (__('maintenance.activate') ?? 'Activate') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-10 text-center text-slate-500">{{ __('maintenance.no_centers') ?? 'No maintenance centers found.' }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-slate-700">{{ $centers->links() }}</div>
            </div>
        </div>
    </div>
@endsection
