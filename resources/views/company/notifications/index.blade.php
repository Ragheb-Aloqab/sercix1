@extends('admin.layouts.app')

@section('page_title', 'الاشعارات')
@section('subtitle', 'Updates about orders and payments')

@section('content')
    <div class="max-w-6xl mx-auto p-6 space-y-6">

        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-xl font-black">Notifications</h1>
                    <p class="text-sm text-slate-500 mt-1">Order completion and payment updates.</p>
                </div>

                <form method="GET" action="{{ route('company.notifications.index') }}" class="flex items-center gap-2">
                    <select name="filter"
                        class="px-4 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 bg-transparent">
                        <option value="all" @selected($filter !== 'unread')>All</option>
                        <option value="unread" @selected($filter === 'unread')>Unread</option>
                    </select>
                    <button class="px-4 py-2 rounded-2xl bg-slate-900 hover:bg-black text-white font-bold">Apply</button>
                </form>
            </div>
        </div>

        <div
            class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft overflow-hidden">
            <div class="divide-y divide-slate-200/70 dark:divide-slate-800">
                @forelse($notifications as $n)
                    @php
                        $data = $n->data ?? [];
                        $isUnread = is_null($n->read_at);
                        $link = $data['route'] ?? null;
                    @endphp

                    <div class="p-5 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-black truncate">{{ $data['title'] ?? 'Notification' }}</p>
                                @if ($isUnread)
                                    <span
                                        class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-800 font-bold">Unread</span>
                                @endif
                            </div>

                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                                {{ $data['message'] ?? '' }}
                            </p>

                            <p class="text-xs text-slate-500 mt-2">
                                {{ optional($n->created_at)->format('Y-m-d H:i') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if ($link)
                                <a href="{{ $link }}"
                                    class="px-4 py-2 rounded-2xl border border-slate-200 dark:border-slate-800 font-bold">
                                    View
                                </a>
                            @endif

                            @if ($isUnread)
                                <form method="POST" action="{{ route('company.notifications.read', $n->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        class="px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold">
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
                <div class="p-5 border-t border-slate-200/70 dark:border-slate-800">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
