@extends('admin.layouts.app')

@section('title', __('common.notifications') . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', __('common.notifications'))

@section('content')
    <div class="dashboard-glass min-h-[calc(100vh-8rem)] mx-0 px-4 sm:px-6 py-6 sm:py-8 rounded-[28px] sm:rounded-[32px] overflow-hidden shadow-2xl">
        <div class="dashboard-content max-w-4xl mx-auto space-y-6">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-center sm:text-start w-full sm:w-auto">
                    <h1 class="dash-page-title">{{ __('common.notifications') }}</h1>
                    <div class="dash-title-accent mx-auto sm:ms-0 sm:me-0"></div>
                </div>
                <div class="flex gap-2">
                    <a href="?filter=all" class="dash-btn dash-btn-secondary {{ ($filter ?? 'all') === 'all' ? '!bg-sky-600 !border-sky-500' : '' }}">
                        {{ __('common.all') }}
                    </a>
                    <a href="?filter=unread" class="dash-btn dash-btn-secondary {{ ($filter ?? '') === 'unread' ? '!bg-sky-600 !border-sky-500' : '' }}">
                        {{ __('common.unread') ?? 'Unread' }}
                    </a>
                    <a href="?filter=read" class="dash-btn dash-btn-secondary {{ ($filter ?? '') === 'read' ? '!bg-sky-600 !border-sky-500' : '' }}">
                        {{ __('common.read') ?? 'Read' }}
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="dash-btn dash-btn-secondary">
                        <i class="fa-solid fa-arrow-left rtl:rotate-180"></i>{{ __('common.back') }}
                    </a>
                </div>
            </div>

            {{-- Notifications List --}}
            <div class="space-y-3">
                @forelse($notifications as $notification)
                    <div class="dash-card flex items-start gap-3 {{ is_null($notification->read_at) ? 'border-sky-500/30 bg-sky-500/5' : '' }}">
                        @if(is_null($notification->read_at))
                            <span class="mt-2 w-2 h-2 rounded-full bg-sky-500 shrink-0"></span>
                        @else
                            <span class="mt-2 w-2 h-2 rounded-full bg-transparent shrink-0"></span>
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-white">
                                {{ $notification->data['title'] ?? $notification->data['message'] ?? __('common.notifications') }}
                            </p>
                            @if(!empty($notification->data['message']) && ($notification->data['title'] ?? '') !== ($notification->data['message'] ?? ''))
                                <p class="text-sm text-slate-400 mt-0.5">{{ $notification->data['message'] }}</p>
                            @endif
                            <p class="text-xs text-slate-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if(!empty($notification->data['url']))
                                <a href="{{ $notification->data['url'] }}" wire:navigate class="dash-btn dash-btn-primary !py-2 !px-3 text-sm">
                                    {{ __('common.view') }}
                                </a>
                            @endif
                            @if(is_null($notification->read_at))
                                <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="dash-btn dash-btn-secondary !py-2 !px-3 text-sm">
                                        {{ __('messages.notification_marked_read') ?? 'Mark read' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="dash-card text-center py-12 text-slate-400">
                        {{ __('admin_dashboard.no_activity') }}
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
@endsection
