<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class StatusBadge extends Component
{
    public string $status;

    public string $class;

    public string $label;

    public function __construct(string $status)
    {
        $this->status = $status;

        $map = [
            'pending_approval' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
            'approved' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
            'in_progress' => 'bg-sky-500/10 text-sky-700 dark:text-sky-300',
            'pending_confirmation' => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300',
            'completed' => 'bg-slate-200/70 text-slate-700 dark:text-slate-800 dark:text-slate-200',
            'rejected' => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
            'cancelled' => 'bg-rose-500/10 text-rose-700 dark:text-rose-300',
            'pending_company' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300',
            'approved_by_company' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
            'pending_assignment' => 'bg-sky-500/10 text-sky-700 dark:text-sky-300',
            'assigned_to_technician' => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300',
        ];

        $statusLabels = [
            'pending_approval' => __('common.status_pending_approval'),
            'approved' => __('common.status_approved'),
            'in_progress' => __('common.status_in_progress'),
            'pending_confirmation' => __('common.status_pending_confirmation'),
            'completed' => __('common.status_completed'),
            'rejected' => __('common.status_rejected'),
            'cancelled' => __('common.status_cancelled'),
            'pending_company' => __('common.status_pending_approval'),
            'approved_by_company' => __('common.status_approved'),
            'pending_assignment' => __('common.status_pending_assignment'),
            'assigned_to_technician' => __('common.status_assigned_to_technician'),
        ];

        $this->class = $map[$status] ?? 'bg-slate-100 text-slate-700';
        $this->label = $statusLabels[$status] ?? $status;
    }

    public function render(): View
    {
        return view('components.status-badge');
    }
}
