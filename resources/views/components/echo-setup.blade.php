{{-- Load Laravel Echo + Pusher for real-time notifications when configured --}}
@php
    $broadcastDriver = config('broadcasting.default');
    $pusherKey = config('broadcasting.connections.pusher.key');
    $pusherCluster = config('broadcasting.connections.pusher.options.cluster', 'mt1');
    $actor = auth('company')->user() ?? auth('maintenance_center')->user() ?? auth('web')->user();
@endphp
@if($broadcastDriver === 'pusher' && $pusherKey && $actor)
    <script src="https://js.pusher.com/8.3.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.min.js"></script>
    <script>
        (function() {
            const channelName = @json($actor instanceof \App\Models\Company ? 'App.Models.Company.' . $actor->id : 'App.Models.User.' . $actor->id);
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: @json($pusherKey),
                cluster: @json($pusherCluster),
                forceTLS: true,
                authEndpoint: '/broadcasting/auth',
                auth: { headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content } }
            });
            window.Echo.private(channelName).notification(function() {
                window.Livewire?.dispatch('notification-received');
            });
        })();
    </script>
@endif
