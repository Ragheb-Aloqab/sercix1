@extends('layouts.driver')

@section('title', __('tracking.start_tracking'))

@section('content')
<div class="max-w-4xl mx-auto w-full">
    <div class="mb-6">
        <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center gap-2 text-slate-600 dark:text-servx-silver hover:text-slate-700 dark:hover:text-servx-silver-light font-semibold mb-4 transition-colors">
            <i class="fa-solid fa-arrow-left"></i> {{ __('dashboard.main_page') }}
        </a>
        <h1 class="dash-page-title">{{ __('tracking.start_tracking') }}</h1>
        <p class="text-slate-600 dark:text-servx-silver mt-1">{{ $vehicle->plate_number }} — {{ $vehicle->display_name }}</p>
    </div>

    {{-- Modal: Enter end odometer when stopping (required to store in history) --}}
    <div id="modal-end-odometer" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-600/50 p-6 w-full max-w-sm shadow-2xl transition-colors duration-300">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('tracking.enter_end_odometer') }}</h3>
            <p class="text-sm text-slate-600 dark:text-servx-silver mb-4">{{ __('tracking.end_odometer_hint') }}</p>
            <p id="end-odometer-error" class="text-sm text-rose-400 hidden mb-2"></p>
            <input type="number" id="input-end-odometer" min="0" step="1" placeholder="0"
                class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600/50 text-slate-900 dark:text-white font-bold mb-4 transition-colors duration-300">
            <div class="flex gap-2">
                <button type="button" id="btn-cancel-stop" class="flex-1 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-500/50 text-slate-700 dark:text-servx-silver hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-300">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-confirm-stop" class="flex-1 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold">{{ __('tracking.stop_tracking') }}</button>
            </div>
        </div>
    </div>


    {{-- Modal: Daily odometer entry --}}
    <div id="modal-daily-odometer" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60">
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-600/50 p-6 w-full max-w-sm shadow-2xl transition-colors duration-300">
            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">{{ __('tracking.enter_daily_odometer') }}</h3>
            <p class="text-sm text-slate-600 dark:text-servx-silver mb-4">{{ __('tracking.daily_odometer_hint') }}</p>
            <p id="daily-odometer-error" class="text-sm text-rose-400 hidden mb-2"></p>
            <input type="number" id="input-daily-odometer" min="0" step="1" placeholder="0"
                class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-300 dark:border-slate-600/50 text-slate-900 dark:text-white font-bold mb-4 transition-colors duration-300">
            <div class="flex gap-2">
                <button type="button" id="btn-cancel-daily" class="flex-1 px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-500/50 text-slate-700 dark:text-servx-silver hover:bg-slate-100 dark:hover:bg-slate-700/50 transition-colors duration-300">{{ __('common.cancel') }}</button>
                <button type="button" id="btn-confirm-daily" class="flex-1 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">{{ __('common.save') }}</button>
            </div>
        </div>
    </div>

    <div id="tracking-container" class="dash-card mt-6">
        <div id="tracking-idle" class="text-center py-8">
            <p class="text-slate-600 dark:text-servx-silver mb-4">{{ __('tracking.mobile_tracking_hint') }}</p>
            <button type="button" id="btn-start-tracking"
                class="px-6 py-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-lg transition-colors">
                <i class="fa-solid fa-location-dot me-2"></i>{{ __('tracking.start_tracking') }}
            </button>
        </div>

        <div id="tracking-active" class="hidden">
            <div class="flex items-center justify-between mb-4">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-400 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ __('tracking.tracking_active') }}
                </span>
                <button type="button" id="btn-stop-tracking"
                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-semibold text-sm">
                    {{ __('tracking.stop_tracking') }}
                </button>
            </div>
            <p id="tracking-status" class="text-sm text-slate-600 dark:text-servx-silver mb-2">—</p>
            <p id="tracking-error" class="text-sm text-rose-400 hidden"></p>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var vehicleId = {{ $vehicle->id }};
    var reportUrl = '{{ route('driver.tracking.report') }}';
    var startUrl = '{{ route('driver.tracking.start') }}';
    var stopUrl = '{{ route('driver.tracking.stop') }}';
    var dailyOdometerUrl = '{{ route('driver.odometer.daily') }}';
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

    function startGeolocation() {
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

    function doStartTracking() {
        var fd = new FormData();
        fd.append('_token', csrf);
        fd.append('vehicle_id', vehicleId);
        fetch(startUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) {
                if (r.status === 403) {
                    showError('{{ __("tracking.tracking_error") }}');
                    return { error: true };
                }
                return r.json().catch(function() { return {}; });
            })
            .then(function(data) {
                if (data && data.error) {
                    showError(data.message || data.error || '{{ __("tracking.tracking_error") }}');
                } else {
                    startGeolocation();
                }
            })
            .catch(function() {
                showError('{{ __("tracking.tracking_error") }}');
            });
    }

    function doStopTracking(endOdometer) {
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

        var fd = new FormData();
        fd.append('_token', csrf);
        fd.append('vehicle_id', vehicleId);
        fd.append('end_odometer', endOdometer);
        var endErrorEl = document.getElementById('end-odometer-error');
        fetch(stopUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) { return r.json().catch(function() { return {}; }).then(function(d) { return [r.status, d]; }); })
            .then(function(arr) {
                var status = arr[0], data = arr[1];
                if (status === 422 && data.message) {
                    endErrorEl.textContent = data.message;
                    endErrorEl.classList.remove('hidden');
                    return;
                }
                endErrorEl.classList.add('hidden');
                document.getElementById('modal-end-odometer').classList.add('hidden');
                if (data && data.trip_distance_km > 0) {
                    setStatus('{{ __("tracking.trip_recorded") }}: ' + parseFloat(data.trip_distance_km).toFixed(1) + ' {{ __("common.km") }}');
                }
            });
    }

    btnStart.addEventListener('click', function() {
        doStartTracking();
    });

    btnStop.addEventListener('click', function() {
        document.getElementById('modal-end-odometer').classList.remove('hidden');
        document.getElementById('input-end-odometer').value = '';
        document.getElementById('end-odometer-error').classList.add('hidden');
        document.getElementById('input-end-odometer').focus();
    });
    document.getElementById('btn-cancel-stop').addEventListener('click', function() {
        document.getElementById('modal-end-odometer').classList.add('hidden');
    });
    document.getElementById('btn-confirm-stop').addEventListener('click', function() {
        var val = parseFloat(document.getElementById('input-end-odometer').value);
        if (isNaN(val) || val < 0) {
            showError('{{ __("tracking.odometer_invalid") }}');
            return;
        }
        document.getElementById('modal-end-odometer').classList.add('hidden');
        doStopTracking(val);
    });

    var dailyErrorEl = document.getElementById('daily-odometer-error');
    document.getElementById('btn-daily-odometer').addEventListener('click', function() {
        document.getElementById('modal-daily-odometer').classList.remove('hidden');
        document.getElementById('input-daily-odometer').value = '';
        dailyErrorEl.classList.add('hidden');
        document.getElementById('input-daily-odometer').focus();
    });
    document.getElementById('btn-cancel-daily').addEventListener('click', function() {
        document.getElementById('modal-daily-odometer').classList.add('hidden');
    });
    document.getElementById('btn-confirm-daily').addEventListener('click', function() {
        var val = parseFloat(document.getElementById('input-daily-odometer').value);
        if (isNaN(val) || val < 0) {
            dailyErrorEl.textContent = '{{ __("tracking.odometer_invalid") }}';
            dailyErrorEl.classList.remove('hidden');
            return;
        }
        dailyErrorEl.classList.add('hidden');
        var fd = new FormData();
        fd.append('_token', csrf);
        fd.append('vehicle_id', vehicleId);
        fd.append('odometer_km', val);
        fetch(dailyOdometerUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) { return r.json().catch(function() { return {}; }).then(function(d) { return [r.status, d]; }); })
            .then(function(arr) {
                var status = arr[0], data = arr[1];
                if (status === 422 && data.message) {
                    dailyErrorEl.textContent = data.message;
                    dailyErrorEl.classList.remove('hidden');
                } else if (data.ok) {
                    document.getElementById('modal-daily-odometer').classList.add('hidden');
                }
            });
    });

    fetch('{{ route('driver.tracking.status') }}', { headers: { 'Accept': 'application/json' } })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.active && data.vehicle_id === vehicleId) {
                startGeolocation();
            }
        });
})();
</script>
@endpush
@endsection
