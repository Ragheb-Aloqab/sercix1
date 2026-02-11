@php
    $map = [
        'pending' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        'requested' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
        'assigned' => 'bg-sky-500/10 text-sky-700 dark:text-sky-300',
        'on_the_way' => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300',
        'in_progress' => 'bg-amber-500/10 text-amber-800 dark:text-amber-300',
        'completed' => 'bg-slate-200/70 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        'cancelled' => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
        'paid' => 'bg-emerald-600/15 text-emerald-800 dark:text-emerald-300',
    ];
@endphp

<span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $map[$status] ?? 'bg-slate-100 text-slate-700' }}">
    {{ $status }}
</span>
