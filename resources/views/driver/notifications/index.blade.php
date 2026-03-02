@extends('layouts.driver')

@section('title', __('common.notifications'))

@section('content')
<div class="max-w-4xl mx-auto w-full pb-24 lg:pb-0">
    <h1 class="dash-page-title mb-6">{{ __('common.notifications') }}</h1>

    <div class="dash-card">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <form method="GET" action="{{ route('driver.notifications.index') }}" class="flex items-center gap-2">
                <select name="filter" class="px-4 py-2 rounded-xl border border-slate-600/50 bg-slate-800/60 text-servx-silver-light text-sm font-semibold">
                    <option value="all" @selected($filter !== 'unread')>{{ __('common.all') }}</option>
                    <option value="unread" @selected($filter === 'unread')>{{ __('common.unread') }}</option>
                </select>
                <button type="submit" class="dash-btn dash-btn-primary">
                    {{ __('common.apply') }}
                </button>
            </form>
        </div>

        <div class="divide-y divide-slate-600/40">
            @forelse($notifications as $n)
                @php
                    $data = $n->data ?? [];
                    $isUnread = is_null($n->read_at);
                    $link = $data['url'] ?? $data['route'] ?? null;
                    if (! $link && ! empty($data['maintenance_request_id'])) {
                        $link = route('driver.maintenance-request.show', $data['maintenance_request_id']);
                    }
                @endphp

                <div class="py-4 flex items-start justify-between gap-4 hover:bg-slate-700/30 rounded-xl transition-colors {{ $isUnread ? 'bg-emerald-500/10' : '' }}">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="font-bold text-servx-silver-light">{{ $data['title'] ?? __('common.notification') }}</p>
                            @if ($isUnread)
                                <span class="shrink-0 px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold">{{ __('common.unread') }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-servx-silver mt-1">{{ $data['message'] ?? '' }}</p>
                        <p class="text-xs text-servx-silver mt-2">{{ $n->created_at?->format('Y-m-d H:i') }}</p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if ($link)
                            <a href="{{ route('driver.notifications.read', $n->id) }}"
                               class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-sm font-semibold">
                                {{ __('common.view') }}
                            </a>
                        @elseif ($isUnread)
                            <form method="POST" action="{{ route('driver.notifications.read', $n->id) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold">
                                    {{ __('common.mark_read') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-servx-silver">
                    {{ __('dashboard.no_notifications') ?? 'No notifications yet.' }}
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="mt-6 pt-4 border-t border-slate-600/40">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
