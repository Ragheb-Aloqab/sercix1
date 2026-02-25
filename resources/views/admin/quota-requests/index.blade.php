@extends('admin.layouts.app')

@section('title', __('admin_dashboard.quota_requests') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('admin_dashboard.quota_requests'))

@section('content')
<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-5xl mx-auto space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="dash-page-title">{{ __('admin_dashboard.quota_requests') }}</h1>
            <div class="flex gap-2">
                <a href="{{ route('admin.quota-requests.index') }}" class="dash-btn {{ !request('status') ? 'dash-btn-primary' : 'dash-btn-secondary' }}">
                    {{ __('common.all') }}
                </a>
                <a href="{{ route('admin.quota-requests.index', ['status' => 'pending']) }}" class="dash-btn {{ request('status') === 'pending' ? 'dash-btn-primary' : 'dash-btn-secondary' }}">
                    {{ __('admin_dashboard.pending') }}
                </a>
            </div>
        </div>

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
                            <th class="text-start px-4 py-3 text-sm font-bold text-slate-400">{{ __('admin_dashboard.company') }}</th>
                            <th class="text-start px-4 py-3 text-sm font-bold text-slate-400">{{ __('admin_dashboard.requested_count') }}</th>
                            <th class="text-start px-4 py-3 text-sm font-bold text-slate-400">{{ __('admin_dashboard.status') }}</th>
                            <th class="text-start px-4 py-3 text-sm font-bold text-slate-400">{{ __('admin_dashboard.date') }}</th>
                            <th class="text-end px-4 py-3 text-sm font-bold text-slate-400">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr class="border-t border-slate-700/50 hover:bg-slate-800/30">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.companies.show', $req->company) }}" class="text-sky-400 hover:underline font-semibold">{{ $req->company->company_name }}</a>
                                    <p class="text-xs text-slate-500">{{ __('admin_dashboard.quota') }}: {{ $req->company->vehicle_quota ?? __('admin_dashboard.unlimited') }}</p>
                                </td>
                                <td class="px-4 py-3 text-white">{{ $req->requested_count }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-lg text-xs font-bold
                                        {{ $req->status === 'pending' ? 'bg-amber-500/20 text-amber-400' : '' }}
                                        {{ $req->status === 'approved' ? 'bg-emerald-500/20 text-emerald-400' : '' }}
                                        {{ $req->status === 'rejected' ? 'bg-rose-500/20 text-rose-400' : '' }}">
                                        {{ $req->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-400 text-sm">{{ $req->created_at->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-end">
                                    @if($req->status === 'pending')
                                        <form action="{{ route('admin.quota-requests.approve', $req) }}" method="POST" class="inline-block me-1">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-500/30 text-emerald-400 text-sm font-bold hover:bg-emerald-500/50">{{ __('admin_dashboard.approve') }}</button>
                                        </form>
                                        <form action="{{ route('admin.quota-requests.reject', $req) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 rounded-lg bg-rose-500/30 text-rose-400 text-sm font-bold hover:bg-rose-500/50">{{ __('admin_dashboard.reject') }}</button>
                                        </form>
                                    @else
                                        <span class="text-slate-500 text-sm">{{ $req->reviewed_at?->format('Y-m-d') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-slate-500">{{ __('admin_dashboard.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
                <div class="p-4 border-t border-slate-700">{{ $requests->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
