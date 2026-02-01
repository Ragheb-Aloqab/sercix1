<div class="space-y-6">
    @php($user = auth()->user())

    @if (auth('company')->check())
        @include('livewire.dashboard.company-overview', [
            'company' => auth('company')->user(),
        ])
    @elseif($user && $user->role === 'admin')
        @include('livewire.dashboard.admin-overview')
    @else
        <div class="p-6 text-center text-slate-500">
            لا يوجد محتوى متاح.
        </div>
    @endif
</div>
