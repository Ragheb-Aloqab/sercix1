{{-- Technician nav links. Rendered only when $role === 'technician' --}}
@if ($role === 'technician')
    <a href="{{ route('tech.tasks.index') }}" class="mt-2 {{ $this->isActive('tech.tasks.*') ? $active : $link }}">
        <span class="{{ $this->isActive('tech.tasks.*') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-list-check"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.tasks') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.tasks_desc') }}</p>
        </div>
    </a>

    <a href="{{ route('tech.settings') }}" class="mt-2 {{ $this->isActive('tech.settings') ? $active : $link }}">
        <span class="{{ $this->isActive('tech.settings') ? $iconWrapActive : $iconWrap }}"><i class="fa-solid fa-gear"></i></span>
        <div class="flex-1">
            <p class="font-bold leading-5">{{ __('dashboard.settings') }}</p>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('dashboard.settings_desc') }}</p>
        </div>
    </a>
@endif
