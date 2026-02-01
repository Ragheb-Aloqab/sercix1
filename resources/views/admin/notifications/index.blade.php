@extends('admin.layouts.app')


@section('title', 'إشعاراتي')

@section('content')
<div class="max-w-4xl mx-auto p-4">
@php
$filter = "all";
@endphp
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-slate-800 dark:text-slate-100">
            إشعاراتي
        </h1>

        <!-- Filters -->
        <div class="flex gap-2">
            <a href="?filter=all"
               class="px-3 py-1 rounded-full text-sm {{ $filter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 dark:bg-slate-800' }}">
                الكل
            </a>
            <a href="?filter=unread"
               class="px-3 py-1 rounded-full text-sm {{ $filter === 'unread' ? 'bg-slate-900 text-white' : 'bg-slate-100 dark:bg-slate-800' }}">
                غير مقروء
            </a>
            <a href="?filter=read"
               class="px-3 py-1 rounded-full text-sm {{ $filter === 'read' ? 'bg-slate-900 text-white' : 'bg-slate-100 dark:bg-slate-800' }}">
                مقروء
            </a>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="space-y-3">
        @forelse($notifications as $notification)
            <div class="flex items-start gap-3 p-4 rounded-2xl border
                {{ is_null($notification->read_at)
                    ? 'bg-slate-50 dark:bg-slate-900 border-slate-200 dark:border-slate-700'
                    : 'bg-white dark:bg-slate-800 border-slate-100 dark:border-slate-700' }}">

                <!-- Dot -->
                @if(is_null($notification->read_at))
                    <span class="mt-2 w-2 h-2 rounded-full bg-blue-500"></span>
                @else
                    <span class="mt-2 w-2 h-2 rounded-full bg-transparent"></span>
                @endif

                <!-- Content -->
                <div class="flex-1">
                    <p class="text-sm text-slate-800 dark:text-slate-100">
                        {{ $notification->data['message'] ?? 'إشعار جديد' }}
                    </p>

                    <span class="text-xs text-slate-500">
                        {{ $notification->created_at->diffForHumans() }}
                    </span>
                </div>

                <!-- Actions -->
                @if(is_null($notification->read_at))
                    <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="text-xs text-blue-600 hover:underline">
                            تعليم كمقروء
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div class="text-center text-slate-500 py-10">
                لا توجد إشعارات حالياً
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
</div>
@endsection