@extends('admin.layouts.app')

@section('title', __('dashboard.customers') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('dashboard.customers'))

@section('content')
    <div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
        <div class="dashboard-content max-w-7xl mx-auto space-y-6">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-center sm:text-start w-full sm:w-auto">
                    <h1 class="dash-page-title">{{ __('dashboard.customers') }}</h1>
                    <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
                </div>
                <div class="flex gap-2">
                    @if(auth()->user()?->role === 'super_admin')
                    <a href="{{ route('admin.customers.create') }}" class="dash-btn dash-btn-primary">
                        <i class="fa-solid fa-plus"></i>{{ __('admin_dashboard.quick_add_company') }}
                    </a>
                    @endif
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

            {{-- Search --}}
            <div class="dash-card">
                <form method="GET" class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <i class="fa-solid fa-search absolute start-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input name="q" value="{{ $q ?? '' }}"
                            class="w-full ps-10 pe-4 py-2.5 rounded-xl bg-slate-800/50 border border-slate-600/50 text-white placeholder-slate-400 focus:border-sky-500/50"
                            placeholder="{{ __('admin_dashboard.filter_by_company') }}" />
                    </div>
                    <button type="submit" class="dash-btn dash-btn-primary">
                        <i class="fa-solid fa-magnifying-glass"></i>{{ __('common.search') }}
                    </button>
                </form>
            </div>

            {{-- Table --}}
            <div class="dash-card overflow-hidden p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-800/50 border-b border-slate-700">
                            <tr>
                                <th class="p-4 text-start font-semibold text-slate-300">{{ __('common.company') }}</th>
                                <th class="p-4 text-start font-semibold text-slate-300">{{ __('admin_dashboard.phone') }}</th>
                                <th class="p-4 text-start font-semibold text-slate-300">{{ __('admin_dashboard.subscription_status') }}</th>
                                <th class="p-4 text-end font-semibold text-slate-300">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @forelse($customers as $customer)
                                <tr class="hover:bg-slate-800/30 transition-colors">
                                    <td class="p-4">
                                        <p class="font-semibold text-white">{{ $customer->company_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $customer->email ?? '' }}</p>
                                    </td>
                                    <td class="p-4 text-slate-300">{{ $customer->phone ?? '—' }}</td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $customer->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                            {{ $customer->status === 'active' ? __('admin_dashboard.active') : __('admin_dashboard.inactive') }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-end">
                                        <div class="flex flex-wrap gap-2 justify-end">
                                            <a href="{{ route('admin.customers.edit', $customer) }}" class="dash-btn dash-btn-secondary !py-2 !px-3 text-sm">
                                                <i class="fa-solid fa-pen-to-square"></i>{{ __('common.edit') }}
                                            </a>
                                            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="inline" onsubmit="return confirm('{{ __('messages.confirm_delete') ?? 'Delete permanently?' }}');">
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
                                    <td colspan="4" class="p-8 text-center text-slate-400">{{ __('admin_dashboard.no_companies') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($customers->hasPages())
                    <div class="p-4 border-t border-slate-700">
                        {{ $customers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
