@extends('admin.layouts.app')

@section('title', __('admin_dashboard.announcements') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('admin_dashboard.announcements'))

@section('content')
<div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
    <div class="dashboard-content max-w-4xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h1 class="dash-page-title">{{ __('admin_dashboard.announcements') }}</h1>
            <a href="{{ route('admin.announcements.create') }}" class="dash-btn dash-btn-primary">
                <i class="fa-solid fa-plus"></i>{{ __('admin_dashboard.new_announcement') }}
            </a>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">{{ session('success') }}</div>
        @endif

        <div class="space-y-4">
            @forelse($announcements as $a)
                <div class="dash-card flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-white truncate">{{ $a->title }}</h3>
                        <p class="text-sm text-slate-400 mt-1">{{ \Illuminate\Support\Str::limit(strip_tags($a->body), 120) }}</p>
                        <p class="text-xs text-slate-500 mt-2">{{ $a->target_type }} · {{ $a->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <a href="{{ route('admin.announcements.edit', $a) }}" class="dash-btn dash-btn-secondary text-sm">{{ __('common.edit') }}</a>
                        <form action="{{ route('admin.announcements.destroy', $a) }}" method="POST" onsubmit="return confirm('{{ __('common.confirm_delete') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2 rounded-xl bg-rose-500/30 text-rose-400 text-sm font-bold hover:bg-rose-500/50">{{ __('common.delete') }}</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="dash-card text-center py-12 text-slate-500">{{ __('admin_dashboard.no_announcements') }}</div>
            @endforelse
        </div>

        @if($announcements->hasPages())
            <div class="mt-4">{{ $announcements->links() }}</div>
        @endif
    </div>
</div>
@endsection
