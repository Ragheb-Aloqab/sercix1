@php
    $map = [
        'pending_company' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
        'approved_by_company' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        'pending_assignment' => 'bg-sky-500/10 text-sky-700 dark:text-sky-300',
        'assigned_to_technician' => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300',
        'in_progress' => 'bg-amber-500/10 text-amber-800 dark:text-amber-300',
        'completed' => 'bg-slate-200/70 text-slate-700 dark:bg-slate-800 dark:text-slate-200',
        'cancelled' => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
    ];
    $statusLabels = [
        'pending_company' => __('common.status_pending_company'),
        'approved_by_company' => __('common.status_approved_by_company'),
        'pending_assignment' => __('common.status_pending_assignment'),
        'assigned_to_technician' => __('common.status_assigned_to_technician'),
        'in_progress' => __('common.status_in_progress'),
        'completed' => __('common.status_completed'),
        'cancelled' => __('common.status_cancelled'),
    ];
@endphp

<span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $map[$status] ?? 'bg-slate-100 text-slate-700' }}">
    {{ $statusLabels[$status] ?? $status }}
</span>
