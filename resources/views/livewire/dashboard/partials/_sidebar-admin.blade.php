{{-- Admin-only menu items. Rendered only when $role === 'admin' --}}
@if ($role === 'admin')
    {{-- Quick actions (Admin only) --}}
    <div class="p-6">
        <div class="rounded-2xl p-4 bg-gradient-to-br from-emerald-500/10 to-sky-500/10 border border-emerald-500/10 dark:border-slate-800">
            <p class="font-bold">{{ __('dashboard.quick_action') }}</p>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">{{ __('dashboard.quick_action_desc') }}</p>
            <div class="mt-3 flex flex-wrap gap-2">
                <a href="{{ route('admin.services.index') }}"
                    class="px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">
                    <i class="fa-solid fa-plus me-2"></i> {{ __('dashboard.add_service') }}
                </a>
                <a href="{{ route('admin.orders.index') }}"
                    class="px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-sm font-semibold">
                    <i class="fa-solid fa-receipt me-2"></i> {{ __('dashboard.orders') }}
                </a>
            </div>
        </div>
    </div>
@endif
