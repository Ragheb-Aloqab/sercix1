@php
    $vehicles = $this->vehicles;
    $initialLocations = $this->initialLocations;
    $mode = $this->mode;
    $isSingle = $mode === 'single';
    $vehicle = $isSingle ? $vehicles->first() : null;
    $hasVehicles = $vehicles->isNotEmpty();
    $mapId = 'vehicle-map-' . uniqid();
@endphp

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin />
    @if (!$isSingle)
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" crossorigin />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" crossorigin />
    @endif
    <style>.vehicle-marker { background: transparent !important; border: none !important; }</style>
@endpush

@if (!$hasVehicles && !$isSingle)
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-12 text-center">
        <p class="text-slate-400">{{ __('tracking.imei_required') }}</p>
        <p class="text-sm text-slate-500 mt-2">{{ __('vehicles.add_vehicle') }} {{ __('vehicles.edit_vehicle') }}</p>
        <a href="{{ route('company.vehicles.index') }}" class="inline-block mt-4 px-4 py-2 rounded-2xl bg-sky-600 text-white font-bold">{{ __('vehicles.vehicles_list') }}</a>
    </div>
@else
<div class="vehicle-tracking-map" wire:poll.visible.30s="refreshPositions">
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 overflow-hidden backdrop-blur-sm">
        <div id="{{ $mapId }}" wire:ignore
             class="w-full min-h-[400px]"
             style="height: {{ $mapHeight }};"
             data-config="{{ base64_encode(json_encode([
                 'vehicles' => $vehicles->map(fn($v) => ['id' => $v->id, 'name' => $v->display_name, 'plate' => $v->plate_number ?? '—', 'type' => $v->type ?? 'car'])->values(),
                 'initialLocations' => $initialLocations,
                 'isSingle' => $isSingle,
                 'fetchUrl' => $isSingle ? route('company.vehicles.track.fetch', $vehicle) : route('company.tracking.fetch_all'),
                 'trackBaseUrl' => url('/company/vehicles'),
                 'csrf' => csrf_token(),
                 'translations' => [
                     'plate' => __('tracking.plate'),
                     'speed' => __('tracking.speed'),
                     'last_update' => __('tracking.last_update'),
                     'address' => __('tracking.address'),
                     'kmh' => __('tracking.kmh'),
                     'km' => __('tracking.km'),
                     'odometer' => __('tracking.odometer'),
                     'machine_status' => __('tracking.machine_status'),
                     'track_vehicle' => __('tracking.track_vehicle'),
                     'no_location' => __('tracking.no_location'),
                 ],
             ])) }}"></div>
    </div>

    @if ($showInfoPanel && $isSingle && $vehicle)
        {{-- Compact vehicle details: machine status, odometer, location --}}
        <div class="mt-4 rounded-xl bg-slate-800/40 border border-slate-500/30 px-4 py-3 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
            <span class="flex items-center gap-2">
                <span class="text-slate-400">{{ __('tracking.machine_status') }}:</span>
                <span class="font-bold text-white" id="{{ $mapId }}-machine-status">{{ ($ms = ($initialLocations[$vehicle->id] ?? [])['machine_status'] ?? null) ? $ms : '—' }}</span>
                <span class="w-2 h-2 rounded-full {{ $ms && !in_array($ms, ['OFF', '0', 'false', 'إيقاف']) ? 'bg-emerald-500' : 'bg-slate-500' }}" id="{{ $mapId }}-machine-dot"></span>
            </span>
            <span class="flex items-center gap-2">
                <span class="text-slate-400">{{ __('tracking.odometer') }}:</span>
                <span class="font-bold text-white" id="{{ $mapId }}-odometer">{{ isset($initialLocations[$vehicle->id]['odometer']) ? number_format((float)$initialLocations[$vehicle->id]['odometer'], 1) . ' ' . __('tracking.km') : '—' }}</span>
            </span>
            <span class="flex items-center gap-2 min-w-0 flex-1">
                <span class="text-slate-400 shrink-0">{{ __('tracking.address') }}:</span>
                <span class="font-medium text-white truncate" id="{{ $mapId }}-address" title="{{ $initialLocations[$vehicle->id]['address'] ?? '' }}">{{ ($initialLocations[$vehicle->id]['address'] ?? null) ?: '—' }}</span>
            </span>
        </div>
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4">
                <p class="text-sm text-slate-400">{{ __('tracking.vehicle_name') }}</p>
                <p class="font-bold text-white" id="{{ $mapId }}-name">{{ $vehicle->display_name }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4">
                <p class="text-sm text-slate-400">{{ __('tracking.plate') }}</p>
                <p class="font-bold text-white" id="{{ $mapId }}-plate">{{ $vehicle->plate_number ?? '—' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4">
                <p class="text-sm text-slate-400">{{ __('tracking.speed') }}</p>
                <p class="font-bold text-white" id="{{ $mapId }}-speed">{{ isset($initialLocations[$vehicle->id]['speed']) ? round($initialLocations[$vehicle->id]['speed']) . ' ' . __('tracking.kmh') : '—' }}</p>
            </div>
            <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4">
                <p class="text-sm text-slate-400">{{ __('tracking.last_update') }}</p>
                <p class="font-bold text-white text-sm" id="{{ $mapId }}-timestamp">{{ ($initialLocations[$vehicle->id] ?? [])['tracker_timestamp'] ?? '—' }}</p>
            </div>
        </div>
    @endif
</div>
@endif

@push('scripts-head')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin></script>
    @if (!$isSingle)
        <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js" crossorigin></script>
    @endif
    <script>
    /**
     * Livewire-native vehicle tracking map.
     * - Dynamic interval: 3s when moving (speed>0), 12s when stopped.
     * - Markers: update in-place (setLatLng, setIcon, setPopupContent); no recreation.
     * - MarkerCluster for multi-vehicle; lightweight divIcons.
     * - Safe API: lastKnownPositions fallback on error; no crash.
     * - wire:poll.visible.30s + positions-updated event; client fetch for responsiveness.
     */
    (function() {
        const INTERVAL_MOVING_MS = 3000;   // 2–5s: vehicles moving (speed > 0)
        const INTERVAL_STOPPED_MS = 12000; // 10–15s: vehicles stopped (speed = 0)

        function initVehicleMap() {
            const el = document.querySelector('[id^="vehicle-map-"]');
            if (!el || typeof L === 'undefined') return;
            const mapId = el.id;
            if (window['_vehicleMap_' + mapId]) return;

            let config;
            try {
                config = JSON.parse(atob(el.dataset.config || ''));
            } catch (e) { return; }
            if (!config) return;

            const { vehicles, initialLocations, isSingle, fetchUrl, trackBaseUrl, csrf, translations } = config;
            const defaultCenter = [24.7136, 46.6753];
            const defaultZoom = isSingle ? 13 : 10;

            const map = L.map(mapId, { zoomControl: false, attributionControl: false }).setView(defaultCenter, defaultZoom);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            const statusColors = { moving: '#10b981', stopped: '#ef4444', idle: '#f59e0b' };
            const getIcon = (vehicleType, status) => {
                const color = statusColors[status] || statusColors.idle;
                let iconClass = 'fa-car';
                const t = (vehicleType || '').toLowerCase();
                if (t.includes('truck') || t.includes('bus') || t.includes('van')) iconClass = 'fa-truck';
                else if (t.includes('motorcycle') || t.includes('bike')) iconClass = 'fa-motorcycle';
                return L.divIcon({
                    className: 'vehicle-marker',
                    html: `<div style="width:${isSingle ? 40 : 36}px;height:${isSingle ? 40 : 36}px;background:${color};border:3px solid white;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.3)"><i class="fa-solid ${iconClass}" style="color:white;font-size:${isSingle ? 16 : 14}px"></i></div>`,
                    iconSize: [isSingle ? 40 : 36, isSingle ? 40 : 36],
                    iconAnchor: [isSingle ? 20 : 18, isSingle ? 20 : 18],
                });
            };

            const buildPopup = (vehicleId, data) => {
                const v = (vehicles || []).find(x => x.id == vehicleId) || {};
                const speedStr = (data && data.speed != null) ? Math.round(data.speed) + ' ' + (translations.kmh || 'km/h') : '—';
                const odometerStr = (data && data.odometer != null) ? parseFloat(data.odometer).toFixed(1) + ' ' + (translations.km || 'km') : '—';
                const machineStr = (data && data.machine_status) ? data.machine_status : '—';
                const ts = (data && data.tracker_timestamp) || (translations.no_location || 'No location data yet');
                const addr = (data && data.address) ? '<p class="text-sm text-slate-600 mt-2"><strong>' + (translations.address || 'Address') + ':</strong><br>' + data.address + '</p>' : '';
                const trackLink = !isSingle ? '<a href="' + trackBaseUrl + '/' + vehicleId + '/track" class="inline-block mt-2 text-sm text-sky-600 hover:text-sky-500 font-bold">' + (translations.track_vehicle || 'Track') + ' →</a>' : '';
                return '<div class="min-w-[220px] text-start p-1">' +
                    '<p class="font-bold text-slate-800 text-lg">' + (v.name || '') + '</p>' +
                    '<p class="text-sm text-slate-600 mt-1"><strong>' + (translations.plate || 'Plate') + ':</strong> ' + (v.plate || '—') + '</p>' +
                    '<p class="text-sm text-slate-600"><strong>' + (translations.speed || 'Speed') + ':</strong> ' + speedStr + '</p>' +
                    '<p class="text-sm text-slate-600"><strong>' + (translations.machine_status || 'Engine') + ':</strong> ' + machineStr + '</p>' +
                    '<p class="text-sm text-slate-600"><strong>' + (translations.odometer || 'Odometer') + ':</strong> ' + odometerStr + '</p>' +
                    '<p class="text-sm text-slate-600"><strong>' + (translations.last_update || 'Last update') + ':</strong> ' + ts + '</p>' +
                    addr + trackLink + '</div>';
            };

            let markerCluster = null;
            const markers = {};
            let singleMarker = null;
            const lastKnownPositions = {};
            const cachedInfoEls = { speed: null, odometer: null, timestamp: null, machineStatus: null, address: null, machineDot: null };

            if (!isSingle) {
                markerCluster = L.markerClusterGroup({ chunkedLoading: true, spiderfyOnMaxZoom: true, showCoverageOnHover: false });
                map.addLayer(markerCluster);
            }

            function safeGetInfoEl(key) {
                const idMap = { machineStatus: 'machine-status', address: 'address', machineDot: 'machine-dot' };
                const elId = idMap[key] || key;
                if (!cachedInfoEls[key]) cachedInfoEls[key] = document.getElementById(mapId + '-' + elId);
                return cachedInfoEls[key];
            }

            function createOrUpdateMarker(vehicleId, data, fallbackIndex) {
                const v = (vehicles || []).find(x => x.id == vehicleId);
                if (!v) return;

                let safeData = (data && typeof data === 'object' && !data.error) ? data : null;
                const lastKnown = lastKnownPositions[vehicleId];
                const hasValidCoords = safeData && safeData.lat != null && safeData.lng != null;

                let lat, lng;
                if (hasValidCoords) {
                    lat = parseFloat(safeData.lat);
                    lng = parseFloat(safeData.lng);
                    if (lat < -90 || lat > 90) { [lat, lng] = [lng, lat]; }
                    lastKnownPositions[vehicleId] = { lat, lng, speed: safeData.speed, odometer: safeData.odometer, tracker_timestamp: safeData.tracker_timestamp, machine_status: safeData.machine_status, address: safeData.address };
                } else if (lastKnown) {
                    lat = lastKnown.lat;
                    lng = lastKnown.lng;
                    safeData = { ...(safeData || {}), lat, lng, speed: lastKnown.speed, odometer: lastKnown.odometer, tracker_timestamp: lastKnown.tracker_timestamp, machine_status: lastKnown.machine_status, address: lastKnown.address };
                } else {
                    const offset = (fallbackIndex ?? 0) * 0.002;
                    lat = defaultCenter[0] + offset;
                    lng = defaultCenter[1] + offset;
                    safeData = { lat, lng, tracker_timestamp: null, address: null, speed: null, odometer: null };
                }

                const locData = safeData;
                const speedVal = parseFloat(locData.speed) || 0;
                const status = (locData.status || (speedVal > 0 ? 'moving' : (speedVal === 0 ? 'stopped' : 'idle'))).toLowerCase();
                const icon = getIcon(v.type, status);

                if (isSingle) {
                    if (singleMarker) {
                        singleMarker.setLatLng([lat, lng]);
                        singleMarker.setIcon(icon);
                        singleMarker.setPopupContent(buildPopup(vehicleId, locData));
                    } else {
                        singleMarker = L.marker([lat, lng], { icon }).addTo(map);
                        singleMarker.bindPopup(buildPopup(vehicleId, locData));
                    }
                    map.setView([lat, lng], map.getZoom());
                    const speedEl = safeGetInfoEl('speed');
                    const odometerEl = safeGetInfoEl('odometer');
                    const tsEl = safeGetInfoEl('timestamp');
                    const machineEl = safeGetInfoEl('machineStatus');
                    const addrEl = safeGetInfoEl('address');
                    if (speedEl) speedEl.textContent = locData.speed != null ? Math.round(locData.speed) + ' ' + (translations.kmh || 'km/h') : '—';
                    if (odometerEl) odometerEl.textContent = locData.odometer != null ? parseFloat(locData.odometer).toFixed(1) + ' ' + (translations.km || 'km') : '—';
                    if (tsEl) tsEl.textContent = locData.tracker_timestamp || '—';
                    if (machineEl) machineEl.textContent = locData.machine_status || '—';
                    if (addrEl) { addrEl.textContent = locData.address || '—'; addrEl.title = locData.address || ''; }
                    const dotEl = safeGetInfoEl('machineDot');
                    if (dotEl) dotEl.className = 'w-2 h-2 rounded-full ' + (locData.machine_status && !['OFF','0','false','إيقاف'].includes(locData.machine_status) ? 'bg-emerald-500' : 'bg-slate-500');
                } else {
                    if (markers[vehicleId]) {
                        markers[vehicleId].setLatLng([lat, lng]);
                        markers[vehicleId].setIcon(icon);
                        markers[vehicleId].setPopupContent(buildPopup(vehicleId, locData));
                    } else {
                        const m = L.marker([lat, lng], { icon });
                        m.bindPopup(buildPopup(vehicleId, locData));
                        markers[vehicleId] = m;
                        markerCluster.addLayer(m);
                    }
                }
            }

            (vehicles || []).forEach((v, idx) => {
                const loc = (initialLocations && initialLocations[v.id]) ? initialLocations[v.id] : null;
                try { createOrUpdateMarker(v.id, loc, idx); } catch (err) { console.warn('Vehicle map init error:', err); }
            });

            let pollTimer = null;
            let lastMaxSpeed = 0;

            function scheduleNextFetch(forceStopped = false) {
                const isVisible = typeof document.visibilityState === 'undefined' || document.visibilityState === 'visible';
                const interval = forceStopped || !isVisible || lastMaxSpeed === 0 ? INTERVAL_STOPPED_MS : INTERVAL_MOVING_MS;
                if (pollTimer) clearTimeout(pollTimer);
                pollTimer = setTimeout(doFetch, interval);
            }

            function doFetch() {
                fetch(fetchUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
                    .then(r => r.json())
                    .catch(() => null)
                    .then(res => {
                        if (!res || !res.success) { scheduleNextFetch(true); return; }
                        const data = res.data;
                        if (!data || typeof data !== 'object') { scheduleNextFetch(); return; }

                        let maxSpeed = 0;
                        if (isSingle && vehicles && vehicles[0]) {
                            const d = safeData(data);
                            if (d) maxSpeed = Math.max(0, parseFloat(d.speed) || 0);
                            try { createOrUpdateMarker(vehicles[0].id, d, 0); } catch (err) { console.warn('Map update error:', err); }
                        } else {
                            Object.keys(data).forEach((id, idx) => {
                                const d = safeData(data[id]);
                                if (d) maxSpeed = Math.max(maxSpeed, parseFloat(d.speed) || 0);
                                try { createOrUpdateMarker(parseInt(id), d, idx); } catch (err) { console.warn('Map update error:', err); }
                            });
                        }
                        lastMaxSpeed = maxSpeed;
                        scheduleNextFetch();
                    });
            }

            function safeData(d) {
                if (!d || typeof d !== 'object' || d.error) return null;
                return d;
            }

            doFetch();

            function updateLastMaxSpeed(val) {
                if (typeof val === 'number' && !isNaN(val)) lastMaxSpeed = val;
            }

            window['_vehicleMap_' + mapId] = { map, createOrUpdateMarker, updateLastMaxSpeed };
        }

        document.addEventListener('livewire:load', initVehicleMap);
        document.addEventListener('DOMContentLoaded', () => setTimeout(initVehicleMap, 150));
        if (document.readyState !== 'loading') setTimeout(initVehicleMap, 150);

        document.addEventListener('livewire:init', () => {
            Livewire.on('positions-updated', (e) => {
                const positions = e && (e.positions || e);
                const el = document.querySelector('[id^="vehicle-map-"]');
                if (!el || !positions || typeof positions !== 'object') return;
                const state = window['_vehicleMap_' + el.id];
                if (!state || !state.createOrUpdateMarker) return;
                let maxSpeed = 0;
                Object.keys(positions).forEach(id => {
                    const d = positions[id];
                    if (d && !d.error) {
                        if (d.speed != null) maxSpeed = Math.max(maxSpeed, parseFloat(d.speed) || 0);
                        try { state.createOrUpdateMarker(parseInt(id), d); } catch (err) { console.warn('Map update error:', err); }
                    }
                });
                if (state.updateLastMaxSpeed) state.updateLastMaxSpeed(maxSpeed);
            });
        });
    })();
    </script>
@endpush
