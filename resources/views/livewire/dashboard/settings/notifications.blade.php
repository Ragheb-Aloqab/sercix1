<div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 shadow-soft p-5">
    <h2 class="text-lg font-black flex items-center gap-2">
        إشعارات الإدارة
    
        @if($unreadCount > 0)
            <span class="px-2 py-0.5 text-xs rounded-full bg-red-600 text-white">
                {{ $unreadCount }}
            </span>
        @endif
    </h2>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">آخر تغييرات الفنيين/الشركات</p>

    <div class="mt-4 space-y-3">
        @forelse($notifications as $n)
             <div
    wire:click="markAsRead('{{ $n->id }}')"
    class="p-4 rounded-2xl border cursor-pointer transition
    {{ $n->read_at ? 'border-slate-200 dark:border-slate-800' : 'border-blue-500/60 bg-blue-50/40 dark:bg-slate-800 hover:bg-blue-100/40' }}">
                <p class="font-bold">{{ $n->data['title'] ?? {{ __('dashboard.notification') }} }}</p>
                @if(!empty($n->data['body']))
    <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
        {{ $n->data['body'] }}
    </p>
@endif
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>

                @if (!empty($n->data['changes'] ?? []))
                    <div class="mt-2 text-xs">
                        @foreach ($n->data['changes'] as $k => $v)
                            <div class="flex justify-between gap-3">
                                <span class="text-slate-500">{{ $k }}</span>
                                <span class="font-semibold">{{ is_scalar($v) ? $v : json_encode($v) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-slate-500">لا يوجد إشعارات.</p>
        @endforelse
    </div>
</div>
