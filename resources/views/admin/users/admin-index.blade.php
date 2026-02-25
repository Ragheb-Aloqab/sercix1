@extends('admin.layouts.app')

@section('title', __('admin_dashboard.admin_users') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('admin_dashboard.admin_users'))

@section('content')
<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="dash-page-title">{{ __('admin_dashboard.admin_users') }}</h1>
            <a href="{{ route('admin.users.create') }}" class="dash-btn dash-btn-primary">
                <i class="fa-solid fa-plus"></i>{{ __('admin_dashboard.add_admin') }}
            </a>
        </div>

        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
            <input name="q" value="{{ $q ?? '' }}" placeholder="{{ __('common.search') }}..."
                class="flex-1 max-w-xs px-4 py-2 rounded-xl bg-slate-800/50 border border-slate-600 text-white">
            <button type="submit" class="dash-btn dash-btn-secondary">{{ __('common.search') }}</button>
        </form>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-500/10 text-rose-400 border border-rose-500/20">{{ session('error') }}</div>
        @endif

        <div class="dash-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-800/50">
                        <tr>
                            <th class="text-start px-4 py-3 text-sm font-bold text-slate-400">{{ __('common.name') }}</th>
                            <th class="text-start px-4 py-3 text-sm font-bold text-slate-400">{{ __('admin_dashboard.email') }}</th>
                            <th class="text-start px-4 py-3 text-sm font-bold text-slate-400">{{ __('admin_dashboard.phone') }}</th>
                            <th class="text-start px-4 py-3 text-sm font-bold text-slate-400">{{ __('admin_dashboard.role') }}</th>
                            <th class="text-start px-4 py-3 text-sm font-bold text-slate-400">{{ __('admin_dashboard.status') }}</th>
                            <th class="text-end px-4 py-3 text-sm font-bold text-slate-400">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                            <tr class="border-t border-slate-700/50 hover:bg-slate-800/30">
                                <td class="px-4 py-3 font-semibold text-white">{{ $u->name }}</td>
                                <td class="px-4 py-3 text-slate-300">{{ $u->email }}</td>
                                <td class="px-4 py-3 text-slate-400">{{ $u->phone ?? '—' }}</td>
                                <td class="px-4 py-3"><span class="px-2 py-1 rounded-lg text-xs bg-sky-500/20 text-sky-400">{{ $u->role }}</span></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs {{ $u->status === 'active' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400' }}">
                                        {{ $u->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('admin.users.edit', $u) }}" class="dash-btn dash-btn-secondary text-sm me-1">{{ __('common.edit') }}</a>
                                    @if($u->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle', $u) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-1 rounded-lg text-sm font-bold {{ $u->status === 'active' ? 'bg-amber-500/30 text-amber-400' : 'bg-emerald-500/30 text-emerald-400' }}">
                                                {{ $u->status === 'active' ? __('admin_dashboard.suspend') : __('admin_dashboard.activate') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">{{ __('admin_dashboard.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="p-4 border-t border-slate-700">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
