@extends('admin.layouts.app')

@section('page_title', __('dashboard.notifications'))
@section('subtitle', 'Updates about orders')

@section('content')
@include('company.partials.glass-start', ['title' => __('dashboard.notifications')])
    <div class="space-y-6">

        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-5 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 mb-6">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm text-slate-500">Order completion updates.</p>

                <form method="GET" action="{{ route('company.notifications.index') }}" class="flex items-center gap-2">
                    <select name="filter" class="px-4 py-2 rounded-2xl border border-slate-500/50 bg-slate-800/40 text-white">
                        <option value="all" @selected($filter !== 'unread')>All</option>
                        <option value="unread" @selected($filter === 'unread')>Unread</option>
                    </select>
                    <button class="px-4 py-2 rounded-2xl bg-sky-600 hover:bg-sky-500 text-white font-bold transition-colors">Apply</button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 backdrop-blur-sm hover:border-slate-400/50 transition-all duration-300 overflow-hidden">
            <div class="divide-y divide-slate-600/50">
                @forelse($notifications as $n)
                    @php
                        $data = $n->data ?? [];
                        $isUnread = is_null($n->read_at);
                        $link = $data['route'] ?? null;
                    @endphp

                    <div class="p-5 flex items-start justify-between gap-4 hover:bg-slate-700/30 transition-colors">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-black truncate text-white">{{ $data['title'] ?? 'Notification' }}</p>
                                @if ($isUnread)
                                    <span class="text-xs px-2 py-1 rounded-full bg-amber-500/30 text-amber-300 border border-amber-400/50 font-bold">Unread</span>
                                @endif
                            </div>

                            <p class="text-sm text-slate-400 mt-1">
                                {{ $data['message'] ?? '' }}
                            </p>

                            <p class="text-xs text-slate-500 mt-2">
                                {{ optional($n->created_at)->format('Y-m-d H:i') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if ($link)
                                <a href="{{ $link }}"
                                    class="px-4 py-2 rounded-2xl border border-slate-500/50 text-white font-bold hover:bg-slate-700/50 transition-colors">
                                    View
                                </a>
                            @endif

                            @if ($isUnread)
                                <form method="POST" action="{{ route('company.notifications.read', $n->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold transition-colors">
                                        Mark read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500">
                        No notifications yet.
                    </div>
                @endforelse
            </div>

            @if ($notifications->hasPages())
                <div class="p-5 border-t border-slate-600/50">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>

    </div>
@include('company.partials.glass-end')
@endsection
