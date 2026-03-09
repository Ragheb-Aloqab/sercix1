@extends('admin.layouts.app')

@section('title', __('plans.title') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('plans.title'))

@section('content')
    <div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
        <div class="dashboard-content max-w-7xl mx-auto space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-center sm:text-start">
                    <h1 class="dash-page-title">{{ __('plans.title') }}</h1>
                    <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.plans.create') }}" class="dash-btn dash-btn-primary">
                        <i class="fa-solid fa-plus"></i>{{ __('plans.add_plan') }}
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="dash-btn dash-btn-secondary">
                        <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
                    </a>
                </div>
            </div>

            @if (session('success'))
                <div class="dash-card border-emerald-500/30 bg-emerald-500/10">
                    <p class="text-emerald-400">{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="dash-card border-rose-500/30 bg-rose-500/10">
                    <p class="text-rose-400">{{ session('error') }}</p>
                </div>
            @endif

            <div class="dash-card overflow-hidden p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-800/50 border-b border-slate-700">
                            <tr>
                                <th class="p-4 text-start font-semibold text-slate-300">{{ __('plans.name') }}</th>
                                <th class="p-4 text-start font-semibold text-slate-300">{{ __('plans.tag') }}</th>
                                <th class="p-4 text-start font-semibold text-slate-300">{{ __('plans.price') }}</th>
                                <th class="p-4 text-start font-semibold text-slate-300">{{ __('plans.companies_count') }}</th>
                                <th class="p-4 text-start font-semibold text-slate-300">{{ __('plans.is_active') }}</th>
                                <th class="p-4 text-end font-semibold text-slate-300">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @forelse($plans as $plan)
                                <tr class="hover:bg-slate-800/30 transition-colors">
                                    <td class="p-4">
                                        <p class="font-semibold text-white">{{ $plan->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $plan->slug }}</p>
                                    </td>
                                    <td class="p-4 text-slate-300">{{ $plan->tag ?? '—' }}</td>
                                    <td class="p-4 text-slate-300">
                                        @if($plan->price !== null)
                                            {{ number_format((float) $plan->price, 2) }} SAR
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        @if($plan->companies_count > 0)
                                            <a href="{{ route('admin.customers.index', ['plan_id' => $plan->id]) }}" class="text-sky-400 hover:text-sky-300 font-semibold">
                                                {{ $plan->companies_count }} {{ __('dashboard.customers') }}
                                            </a>
                                        @else
                                            <span class="text-slate-300">0</span>
                                        @endif
                                    </td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $plan->is_active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                            {{ $plan->is_active ? __('admin_dashboard.active') : __('admin_dashboard.inactive') }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-end">
                                        <div class="flex flex-wrap gap-2 justify-end">
                                            <a href="{{ route('admin.plans.edit', $plan) }}" class="dash-btn dash-btn-secondary !py-2 !px-3 text-sm">
                                                <i class="fa-solid fa-pen-to-square"></i>{{ __('common.edit') }}
                                            </a>
                                            <form method="POST" action="{{ route('admin.plans.toggle', $plan) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dash-btn !py-2 !px-3 text-sm {{ $plan->is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white">
                                                    <i class="fa-solid {{ $plan->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                    {{ $plan->is_active ? __('plans.plan_deactivated') : __('plans.plan_activated') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" class="inline" onsubmit="return confirm('{{ __('messages.confirm_delete') ?? 'Delete permanently?' }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dash-btn !py-2 !px-3 text-sm bg-rose-600 hover:bg-rose-700 border-rose-600">
                                                    <i class="fa-solid fa-trash"></i>{{ __('common.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-slate-400">{{ __('plans.no_plans') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($plans->hasPages())
                    <div class="p-4 border-t border-slate-700">
                        {{ $plans->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
