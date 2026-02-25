@extends('layouts.driver')

@section('title', __('tracking.start_tracking'))

@section('content')
<div class="max-w-4xl mx-auto w-full">
    <div class="mb-6">
        <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-800 font-semibold mb-4">
            <i class="fa-solid fa-arrow-left"></i> {{ __('dashboard.main_page') }}
        </a>
        <h1 class="text-2xl font-black">{{ __('tracking.start_tracking') }}</h1>
        <p class="text-slate-500 mt-1">{{ $vehicle->plate_number }} — {{ $vehicle->display_name }}</p>
    </div>

    <div id="tracking-container" class="rounded-3xl bg-white border border-slate-200 shadow-soft p-6">
        <div id="tracking-idle" class="text-center py-8">
            <p class="text-slate-600 mb-4">{{ __('tracking.mobile_tracking_hint') }}</p>
            <button type="button" id="btn-start-tracking"
                class="px-6 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-lg transition-colors">
                <i class="fa-solid fa-location-dot me-2"></i>{{ __('tracking.start_tracking') }}
            </button>
        </div>

        <div id="tracking-active" class="hidden">
            <div class="flex items-center justify-between mb-4">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ __('tracking.tracking_active') }}
                </span>
                <button type="button" id="btn-stop-tracking"
                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-sm">
                    {{ __('tracking.stop_tracking') }}
                </button>
            </div>
            <p id="tracking-status" class="text-sm text-slate-500 mb-2">—</p>
            <p id="tracking-error" class="text-sm text-rose-600 hidden"></p>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var vehicleId = {{ $vehicle->id }};
    var reportUrl = '{{ route('driver.tracking.report') }}';
    var csrf = '{{ csrf_token() }}';
    var intervalId = null;
    var watchId = null;

    var btnStart = document.getElementById('btn-start-tracking');
    var btnStop = document.getElementById('btn-stop-tracking');
    var idleEl = document.getElementById('tracking-idle');
    var activeEl = document.getElementById('tracking-active');
    var statusEl = document.getElementById('tracking-status');
    var errorEl = document.getElementById('tracking-error');

    function showError(msg) {
        errorEl.textContent = msg;
        errorEl.classList.remove('hidden');
    }
    function hideError() {
        errorEl.classList.add('hidden');
    }
    function setStatus(msg) {
        statusEl.textContent = msg;
    }

    function reportPosition(lat, lng, speed) {
        var fd = new FormData();
        fd.append('_token', csrf);
        fd.append('vehicle_id', vehicleId);
        fd.append('lat', lat);
        fd.append('lng', lng);
        if (speed != null) fd.append('speed', speed);

        fetch(reportUrl, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) {
            if (r.status === 403) {
                showError('{{ __("tracking.tracking_error") }}');
                stopTracking();
                return;
            }
            return r.json();
        })
        .then(function(data) {
            if (data && data.error) showError(data.message || data.error);
            else hideError();
        })
        .catch(function() {
            showError('{{ __("tracking.tracking_error") }}');
        });
    }

    function startTracking() {
        if (!navigator.geolocation) {
            showError('{{ __("tracking.tracking_error") }}');
            return;
        }
        idleEl.classList.add('hidden');
        activeEl.classList.remove('hidden');
        setStatus('{{ __("tracking.fetching") }}');
        hideError();

        watchId = navigator.geolocation.watchPosition(
            function(pos) {
                var lat = pos.coords.latitude;
                var lng = pos.coords.longitude;
                var speed = pos.coords.speed != null ? (pos.coords.speed * 3.6) : null;
                setStatus('{{ __("tracking.last_update") }}: ' + new Date().toLocaleTimeString());
                reportPosition(lat, lng, speed);
            },
            function(err) {
                if (err.code === 1) showError('{{ __("tracking.tracking_permission_denied") }}');
                else showError('{{ __("tracking.tracking_error") }}');
            },
            { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 }
        );

        intervalId = setInterval(function() {
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    var lat = pos.coords.latitude;
                    var lng = pos.coords.longitude;
                    var speed = pos.coords.speed != null ? (pos.coords.speed * 3.6) : null;
                    setStatus('{{ __("tracking.last_update") }}: ' + new Date().toLocaleTimeString());
                    reportPosition(lat, lng, speed);
                },
                function() {}
            );
        }, 8000);
    }

    function stopTracking() {
        if (watchId != null) {
            navigator.geolocation.clearWatch(watchId);
            watchId = null;
        }
        if (intervalId != null) {
            clearInterval(intervalId);
            intervalId = null;
        }
        activeEl.classList.add('hidden');
        idleEl.classList.remove('hidden');
        hideError();
    }

    btnStart.addEventListener('click', startTracking);
    btnStop.addEventListener('click', stopTracking);

    window.addEventListener('beforeunload', function() {
        if (watchId != null) stopTracking();
    });
})();
</script>
@endpush
@endsection
