@php
    $vehicles = $this->vehicles;
    $initialLocations = $this->initialLocations;
    $mode = $this->mode;
    $isSingle = $mode === 'single';
    $vehicle = $isSingle ? $vehicles->first() : null;
    $hasVehicles = $vehicles->isNotEmpty();
    $mapId = $isSingle && $vehicle ? 'vehicle-map-' . $vehicle->id : 'vehicle-map-all';
@endphp

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin />
    @if (!$isSingle)
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" crossorigin />
        <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css"
            crossorigin />
    @endif
    <style>
        .vehicle-marker {
            background: transparent !important;
            border: none !important;
        }

        .vehicle-marker-inner {
            will-change: transform;
        }

        .vehicle-map-container {
            opacity: 0;
            transition: opacity 0.5s ease-out;
        }

        .vehicle-map-container.vehicle-map-ready {
            opacity: 1;
        }

        .map-style-option[data-active="true"] {
            background: rgba(56, 189, 248, 0.2);
            color: rgb(125, 211, 252);
        }
    </style>
@endpush

@if (!$hasVehicles)
    <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-12 text-center">
        <p class="text-slate-400">{{ __('vehicles.no_vehicles') ?? __('vehicles.add_vehicle') }}</p>
        <a href="{{ route('company.vehicles.index') }}"
            class="inline-block mt-4 px-4 py-2 rounded-2xl bg-sky-600 text-white font-bold">{{ __('vehicles.vehicles_list') }}</a>
    </div>
@else
    <div class="vehicle-tracking-map" wire:poll.visible.3s="refreshPositions">
        <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 overflow-hidden backdrop-blur-sm relative">
            <div id="{{ $mapId }}" wire:ignore
                class="vehicle-map-container w-full min-h-[400px] transition-opacity duration-500"
                style="height: {{ $mapHeight }};"
                data-config="{{ base64_encode(
                    json_encode([
                        'vehicles' => $vehicles->map(
                                fn($v) => [
                                    'id' => $v->id,
                                    'name' => $v->display_name,
                                    'plate' => $v->plate_number ?? '—',
                                    'type' => $v->type ?? 'car',
                                ],
                            )->values(),
                        'initialLocations' => $initialLocations,
                        'isSingle' => $isSingle,
                        'fetchUrl' => $isSingle ? route('company.vehicles.track.fetch', $vehicle) : route('company.tracking.fetch_all'),
                        'trackBaseUrl' => url('/company/vehicles'),
                        'companyId' => auth('company')->id(),
                        'defaultMapStyle' => config('servx.default_map_style'),
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
                            'map_style' => __('tracking.map_style'),
                            'map_style_carto_dark' => __('tracking.map_style_carto_dark'),
                            'map_style_osm_humanitarian' => __('tracking.map_style_osm_humanitarian'),
                            'map_style_stadia_alidade' => __('tracking.map_style_stadia_alidade'),
                            'map_style_esri_imagery' => __('tracking.map_style_esri_imagery'),
                        ],
                    ]),
                ) }}">
            </div>
            {{-- Map style switcher: top-end (LTR) / top-start (RTL) --}}
            <div id="{{ $mapId }}-style-switcher"
                class="absolute top-3 end-3 ms-auto z-[1000] vehicle-map-style-switcher" dir="ltr"
                style="display:none">
                <div
                    class="rounded-xl bg-slate-900/95 backdrop-blur-md border border-slate-600/50 shadow-xl overflow-hidden">
                    <button type="button"
                        class="map-style-trigger flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-slate-200 hover:bg-slate-700/50 transition-colors w-full"
                        aria-haspopup="listbox" aria-expanded="false" aria-label="{{ __('tracking.map_style') }}">
                        <i class="fa-solid fa-layer-group text-sky-400"></i>
                        <span class="map-style-current-name">{{ __('tracking.map_style_carto_dark') }}</span>
                        <i
                            class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform map-style-chevron"></i>
                    </button>
                    <div class="map-style-dropdown hidden border-t border-slate-600/50 py-1 max-h-56 overflow-y-auto">
                        <button type="button"
                            class="map-style-option w-full flex items-center gap-2 px-3 py-2 text-sm text-start hover:bg-slate-700/50 transition-colors data-[active]:bg-sky-500/20 data-[active]:text-sky-300"
                            data-style="carto_dark">
                            <i class="fa-solid fa-moon w-4 text-center"></i>
                            <span>{{ __('tracking.map_style_carto_dark') }}</span>
                        </button>
                        <button type="button"
                            class="map-style-option w-full flex items-center gap-2 px-3 py-2 text-sm text-start hover:bg-slate-700/50 transition-colors data-[active]:bg-sky-500/20 data-[active]:text-sky-300"
                            data-style="osm_humanitarian">
                            <i class="fa-solid fa-people-group w-4 text-center"></i>
                            <span>{{ __('tracking.map_style_osm_humanitarian') }}</span>
                        </button>
                        <button type="button"
                            class="map-style-option w-full flex items-center gap-2 px-3 py-2 text-sm text-start hover:bg-slate-700/50 transition-colors data-[active]:bg-sky-500/20 data-[active]:text-sky-300"
                            data-style="stadia_alidade">
                            <i class="fa-solid fa-map w-4 text-center"></i>
                            <span>{{ __('tracking.map_style_stadia_alidade') }}</span>
                        </button>
                        <button type="button"
                            class="map-style-option w-full flex items-center gap-2 px-3 py-2 text-sm text-start hover:bg-slate-700/50 transition-colors data-[active]:bg-sky-500/20 data-[active]:text-sky-300"
                            data-style="esri_imagery">
                            <i class="fa-solid fa-satellite w-4 text-center"></i>
                            <span>{{ __('tracking.map_style_esri_imagery') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if ($showInfoPanel && $isSingle && $vehicle)
            {{-- Compact vehicle details: machine status, odometer, location --}}
            <div
                class="mt-4 rounded-xl bg-slate-800/40 border border-slate-500/30 px-4 py-3 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                <span class="flex items-center gap-2">
                    <span class="text-slate-400">{{ __('tracking.machine_status') }}:</span>
                    <span class="font-bold text-white"
                        id="{{ $mapId }}-machine-status">{{ ($ms = ($initialLocations[$vehicle->id] ?? [])['machine_status'] ?? null) ? $ms : '—' }}</span>
                    <span
                        class="w-2 h-2 rounded-full {{ $ms && !in_array($ms, ['OFF', '0', 'false', 'إيقاف']) ? 'bg-emerald-500' : 'bg-slate-500' }}"
                        id="{{ $mapId }}-machine-dot"></span>
                </span>
                <span class="flex items-center gap-2">
                    <span class="text-slate-400">{{ __('tracking.odometer') }}:</span>
                    <span class="font-bold text-white"
                        id="{{ $mapId }}-odometer">{{ isset($initialLocations[$vehicle->id]['odometer']) ? number_format((float) $initialLocations[$vehicle->id]['odometer'], 1) . ' ' . __('tracking.km') : '—' }}</span>
                </span>
                <span class="flex items-center gap-2 min-w-0 flex-1">
                    <span class="text-slate-400 shrink-0">{{ __('tracking.address') }}:</span>
                    <span class="font-medium text-white truncate" id="{{ $mapId }}-address"
                        title="{{ $initialLocations[$vehicle->id]['address'] ?? '' }}">{{ $initialLocations[$vehicle->id]['address'] ?? null ?: '—' }}</span>
                </span>
            </div>
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4">
                    <p class="text-sm text-slate-400">{{ __('tracking.vehicle_name') }}</p>
                    <p class="font-bold text-white" id="{{ $mapId }}-name">{{ $vehicle->display_name }}</p>
                </div>
                <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4">
                    <p class="text-sm text-slate-400">{{ __('tracking.plate') }}</p>
                    <p class="font-bold text-white" id="{{ $mapId }}-plate">{{ $vehicle->plate_number ?? '—' }}
                    </p>
                </div>
                <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4">
                    <p class="text-sm text-slate-400">{{ __('tracking.speed') }}</p>
                    <p class="font-bold text-white" id="{{ $mapId }}-speed">
                        {{ isset($initialLocations[$vehicle->id]['speed']) ? round($initialLocations[$vehicle->id]['speed']) . ' ' . __('tracking.kmh') : '—' }}
                    </p>
                </div>
                <div class="rounded-2xl bg-slate-800/40 border border-slate-500/30 p-4">
                    <p class="text-sm text-slate-400">{{ __('tracking.last_update') }}</p>
                    <p class="font-bold text-white text-sm" id="{{ $mapId }}-timestamp">
                        {{ ($initialLocations[$vehicle->id] ?? [])['tracker_timestamp'] ?? '—' }}</p>
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
         * Smooth vehicle tracking - Uber/Careem-style
         * - 60fps interpolation via requestAnimationFrame
         * - Bearing-based rotation, Kalman smoothing, spike filtering
         * - Polling (1-2s when moving) + optional WebSocket-ready
         */
        (function() {
            const INTERVAL_MOVING_MS = 1500;
            const INTERVAL_STOPPED_MS = 12000;

            function initVehicleMap() {
                const el = document.querySelector('[id^="vehicle-map-"]');
                if (!el || typeof L === 'undefined') return;
                const mapId = el.id;
                if (window['_vehicleMap_' + mapId]) return;

                // Defer init if map container is hidden (e.g. inside inactive tab)
                const rect = el.getBoundingClientRect();
                if (rect.width === 0 || rect.height === 0) {
                    const once = () => {
                        window.removeEventListener('vehicle-tracking-tab-visible', once);
                        setTimeout(initVehicleMap, 200);
                    };
                    window.addEventListener('vehicle-tracking-tab-visible', once);
                    return;
                }

                const VehicleTrackerEngine = window.VehicleTrackerEngine;
                if (!VehicleTrackerEngine) {
                    setTimeout(initVehicleMap, 100);
                    return;
                }

                let config;
                try {
                    config = JSON.parse(atob(el.dataset.config || ''));
                } catch (e) {
                    return;
                }
                if (!config) return;

                const {
                    vehicles,
                    initialLocations,
                    isSingle,
                    fetchUrl,
                    trackBaseUrl,
                    companyId,
                    defaultMapStyle,
                    csrf,
                    translations
                } = config;
                const defaultCenter = [24.7136, 46.6753];
                const defaultZoom = isSingle ? 13 : 10;

                const map = L.map(mapId, {
                    zoomControl: false,
                    attributionControl: false
                }).setView(defaultCenter, defaultZoom);

                L.control.zoom({
                    position: 'bottomright'
                }).addTo(map);

                const MapStyleManagerClass = window.MapStyleManager;
                const MAP_STYLES = window.MAP_STYLES || {};
                let styleManager = null;
                if (MapStyleManagerClass) {
                    styleManager = new MapStyleManagerClass(map, {
                        storageKey: 'vehicle_map_style',
                        defaultStyle: defaultMapStyle || null,
                    });
                    styleManager.init();
                } else {
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                }

                const statusColors = {
                    moving: '#10b981',
                    stopped: '#ef4444',
                    idle: '#f59e0b'
                };
                const getIcon = (vehicleType, status, bearingDeg = 0) => {
                    const color = statusColors[status] || statusColors.idle;
                    let iconClass = 'fa-car';
                    const t = (vehicleType || '').toLowerCase();
                    if (t.includes('truck') || t.includes('bus') || t.includes('van')) iconClass = 'fa-truck';
                    else if (t.includes('motorcycle') || t.includes('bike')) iconClass = 'fa-motorcycle';
                    const size = isSingle ? 40 : 36;
                    return L.divIcon({
                        className: 'vehicle-marker',
                        html: `<div class="vehicle-marker-inner" style="width:${size}px;height:${size}px;background:${color};border:3px solid white;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,0.3);transform:rotate(${bearingDeg}deg)"><i class="fa-solid ${iconClass}" style="color:white;font-size:${isSingle ? 16 : 14}px"></i></div>`,
                        iconSize: [size, size],
                        iconAnchor: [size / 2, size / 2],
                    });
                };

                const buildPopup = (vehicleId, data) => {
                    const v = (vehicles || []).find(x => x.id == vehicleId) || {};
                    const speedStr = (data && data.speed != null) ? Math.round(data.speed) + ' ' + (translations
                        .kmh || 'km/h') : '—';
                    const odometerStr = (data && data.odometer != null) ? parseFloat(data.odometer).toFixed(1) +
                        ' ' + (translations.km || 'km') : '—';
                    const machineStr = (data && data.machine_status) ? data.machine_status : '—';
                    const ts = (data && data.tracker_timestamp) || (translations.no_location ||
                        'No location data yet');
                    const addr = (data && data.address) ? '<p class="text-sm text-slate-600 mt-2"><strong>' + (
                        translations.address || 'Address') + ':</strong><br>' + data.address + '</p>' : '';
                    const trackLink = !isSingle ? '<a href="' + trackBaseUrl + '/' + vehicleId +
                        '/track" class="inline-block mt-2 text-sm text-sky-600 hover:text-sky-500 font-bold">' + (
                            translations.track_vehicle || 'Track') + ' →</a>' : '';
                    return '<div class="min-w-[220px] text-start p-1">' +
                        '<p class="font-bold text-slate-800 text-lg">' + (v.name || '') + '</p>' +
                        '<p class="text-sm text-slate-600 mt-1"><strong>' + (translations.plate || 'Plate') +
                        ':</strong> ' + (v.plate || '—') + '</p>' +
                        '<p class="text-sm text-slate-600"><strong>' + (translations.speed || 'Speed') +
                        ':</strong> ' + speedStr + '</p>' +
                        '<p class="text-sm text-slate-600"><strong>' + (translations.machine_status || 'Engine') +
                        ':</strong> ' + machineStr + '</p>' +
                        '<p class="text-sm text-slate-600"><strong>' + (translations.odometer || 'Odometer') +
                        ':</strong> ' + odometerStr + '</p>' +
                        '<p class="text-sm text-slate-600"><strong>' + (translations.last_update || 'Last update') +
                        ':</strong> ' + ts + '</p>' +
                        addr + trackLink + '</div>';
                };

                let markerCluster = null;
                const markers = {};
                let singleMarker = null;
                const lastMeta = {};

                const engine = new VehicleTrackerEngine({
                    onFrame: (vehicleId, {
                        lat,
                        lng,
                        bearing: bearingDeg,
                        meta,
                        speed,
                        isInstant
                    }) => {
                        const v = (vehicles || []).find(x => x.id == vehicleId);
                        if (!v) return;
                        const locData = {
                            lat,
                            lng,
                            speed,
                            ...meta
                        };
                        lastMeta[vehicleId] = locData;
                        const speedVal = parseFloat(speed) || 0;
                        const status = (meta?.status || (speedVal > 0 ? 'moving' : (speedVal === 0 ?
                            'stopped' : 'idle'))).toLowerCase();
                        const icon = getIcon(v.type, status, bearingDeg);

                        if (isSingle) {
                            if (singleMarker) {
                                singleMarker.setLatLng([lat, lng]);
                                singleMarker.setIcon(icon);
                                singleMarker.setPopupContent(buildPopup(vehicleId, locData));
                                if (!map.getBounds().contains([lat, lng]) && isInstant) {
                                    map.panTo([lat, lng], {
                                        animate: true,
                                        duration: 0.5
                                    });
                                }
                            }
                            const speedEl = document.getElementById(mapId + '-speed');
                            const odometerEl = document.getElementById(mapId + '-odometer');
                            const tsEl = document.getElementById(mapId + '-timestamp');
                            const machineEl = document.getElementById(mapId + '-machine-status');
                            const addrEl = document.getElementById(mapId + '-address');
                            if (speedEl) speedEl.textContent = speed != null ? Math.round(speed) + ' ' + (
                                translations.kmh || 'km/h') : '—';
                            if (odometerEl) odometerEl.textContent = meta?.odometer != null ? parseFloat(
                                meta.odometer).toFixed(1) + ' ' + (translations.km || 'km') : '—';
                            if (tsEl) tsEl.textContent = meta?.tracker_timestamp || '—';
                            if (machineEl) machineEl.textContent = meta?.machine_status || '—';
                            if (addrEl) {
                                addrEl.textContent = meta?.address || '—';
                                addrEl.title = meta?.address || '';
                            }
                            const dotEl = document.getElementById(mapId + '-machine-dot');
                            if (dotEl) dotEl.className = 'w-2 h-2 rounded-full ' + (meta?.machine_status &&
                                !['OFF', '0', 'false', 'إيقاف'].includes(meta.machine_status) ?
                                'bg-emerald-500' : 'bg-slate-500');
                        } else {
                            if (markers[vehicleId]) {
                                markerCluster.removeLayer(markers[vehicleId]);
                                markers[vehicleId].setLatLng([lat, lng]);
                                markers[vehicleId].setIcon(icon);
                                markers[vehicleId].setPopupContent(buildPopup(vehicleId, locData));
                                markerCluster.addLayer(markers[vehicleId]);
                            }
                        }
                    },
                });

                if (!isSingle) {
                    markerCluster = L.markerClusterGroup({
                        chunkedLoading: true,
                        spiderfyOnMaxZoom: true,
                        showCoverageOnHover: false
                    });
                    map.addLayer(markerCluster);
                }

                const switcherEl = document.getElementById(mapId + '-style-switcher');

                function updateStyleSwitcherUI(styleId) {
                    const label = translations['map_style_' + styleId] || MAP_STYLES[styleId]?.name || styleId;
                    const nameEl = switcherEl?.querySelector('.map-style-current-name');
                    if (nameEl) nameEl.textContent = label;
                    switcherEl?.querySelectorAll('.map-style-option').forEach(btn => {
                        btn.dataset.active = btn.dataset.style === styleId ? 'true' : '';
                    });
                }
                if (switcherEl && styleManager) {
                    switcherEl.style.display = '';
                    const currentId = styleManager.getSavedStyle();
                    updateStyleSwitcherUI(currentId);
                    const trigger = switcherEl.querySelector('.map-style-trigger');
                    const dropdown = switcherEl.querySelector('.map-style-dropdown');
                    const chevron = switcherEl.querySelector('.map-style-chevron');
                    trigger?.addEventListener('click', () => {
                        const open = !dropdown?.classList.contains('hidden');
                        dropdown?.classList.toggle('hidden', open);
                        chevron?.style.setProperty('transform', open ? '' : 'rotate(180deg)');
                    });
                    document.addEventListener('click', (e) => {
                        if (!switcherEl.contains(e.target)) {
                            dropdown?.classList.add('hidden');
                            chevron?.style.setProperty('transform', '');
                        }
                    });
                    switcherEl.querySelectorAll('.map-style-option').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const sid = btn.dataset.style;
                            if (sid && styleManager.applyStyle(sid)) {
                                updateStyleSwitcherUI(sid);
                                dropdown?.classList.add('hidden');
                                chevron?.style.setProperty('transform', '');
                            }
                        });
                    });
                }

                requestAnimationFrame(() => el.classList.add('vehicle-map-ready'));

                function applyPositionUpdate(vehicleId, data, fallbackIndex) {
                    const v = (vehicles || []).find(x => x.id == vehicleId);
                    if (!v) return;
                    let safeData = (data && typeof data === 'object' && !data.error) ? data : null;
                    const state = engine.getState(vehicleId);
                    const hasCoords = safeData && safeData.lat != null && safeData.lng != null;

                    if (hasCoords) {
                        engine.pushUpdate(vehicleId, safeData);
                    } else if (state && state.lat != null) {
                        engine.onFrame(vehicleId, {
                            lat: state.lat,
                            lng: state.lng,
                            bearing: state.bearingDeg,
                            meta: state.meta,
                            speed: state.speed,
                            isInstant: true,
                        });
                    } else {
                        const offset = (fallbackIndex ?? 0) * 0.002;
                        engine.setInitialPosition(vehicleId, {
                            lat: defaultCenter[0] + offset,
                            lng: defaultCenter[1] + offset,
                            tracker_timestamp: null,
                            address: null,
                            speed: null,
                            odometer: null,
                        });
                        engine.pushUpdate(vehicleId, {
                            lat: defaultCenter[0] + offset,
                            lng: defaultCenter[1] + offset,
                            tracker_timestamp: null,
                            address: null,
                            speed: null,
                            odometer: null,
                        });
                    }
                }

                (vehicles || []).forEach((v) => {
                    const loc = (initialLocations && initialLocations[v.id]) ? initialLocations[v.id] : null;
                    engine.setInitialPosition(v.id, loc);
                });

                (vehicles || []).forEach((v) => {
                    const loc = (initialLocations && initialLocations[v.id]) ? initialLocations[v.id] : null;
                    const state = engine.getState(v.id);
                    if (!state || state.lat == null) return;
                    const speedVal = parseFloat(state.speed) || 0;
                    const status = (state.meta?.status || (speedVal > 0 ? 'moving' : (speedVal === 0 ?
                        'stopped' : 'idle'))).toLowerCase();
                    const icon = getIcon(v.type, status, state.bearingDeg);
                    const locData = {
                        lat: state.lat,
                        lng: state.lng,
                        speed: state.speed,
                        ...state.meta
                    };

                    if (isSingle) {
                        singleMarker = L.marker([state.lat, state.lng], {
                            icon
                        }).addTo(map);
                        singleMarker.bindPopup(buildPopup(v.id, locData));
                        map.setView([state.lat, state.lng], map.getZoom());
                    } else {
                        const m = L.marker([state.lat, state.lng], {
                            icon
                        });
                        m.bindPopup(buildPopup(v.id, locData));
                        markers[v.id] = m;
                        markerCluster.addLayer(m);
                    }
                });

                let pollTimer = null;
                let lastMaxSpeed = 0;

                function scheduleNextFetch(forceStopped = false) {
                    const isVisible = typeof document.visibilityState === 'undefined' || document.visibilityState ===
                        'visible';
                    const interval = forceStopped || !isVisible || lastMaxSpeed === 0 ? INTERVAL_STOPPED_MS :
                        INTERVAL_MOVING_MS;
                    if (pollTimer) clearTimeout(pollTimer);
                    pollTimer = setTimeout(doFetch, interval);
                }

                function doFetch() {
                    fetch(fetchUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrf,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({}),
                            credentials: 'same-origin',
                        })
                        .then(r => r.ok ? r.json() : null)
                        .catch(() => null)
                        .then(res => {
                            if (!res || !res.success) {
                                scheduleNextFetch(true);
                                return;
                            }
                            const data = res.data;
                            if (!data || typeof data !== 'object') {
                                scheduleNextFetch();
                                return;
                            }

                            let maxSpeed = 0;
                            if (isSingle && vehicles?.[0]) {
                                const d = data?.lat != null ? data : null;
                                if (d) {
                                    maxSpeed = Math.max(0, parseFloat(d.speed) || 0);
                                    applyPositionUpdate(vehicles[0].id, d, 0);
                                    if (d.lat != null && d.lng != null && !engine.getState(vehicles[0].id)
                                        ?.animation) {
                                        map.panTo([parseFloat(d.lat), parseFloat(d.lng)], {
                                            animate: true,
                                            duration: 0.5
                                        });
                                    }
                                }
                            } else {
                                const validBounds = [];
                                Object.keys(data).forEach((id, idx) => {
                                    const d = data[id];
                                    if (d && d.lat != null && d.lng != null && !d.error) {
                                        maxSpeed = Math.max(maxSpeed, parseFloat(d.speed) || 0);
                                        applyPositionUpdate(parseInt(id), d, idx);
                                        validBounds.push([parseFloat(d.lat), parseFloat(d.lng)]);
                                    }
                                });
                                if (validBounds.length > 0) {
                                    const b = L.latLngBounds(validBounds);
                                    if (!map.getBounds().contains(b.getCenter())) {
                                        map.fitBounds(b, {
                                            padding: [30, 30],
                                            maxZoom: 16
                                        });
                                    }
                                }
                            }
                            lastMaxSpeed = maxSpeed;
                            scheduleNextFetch();
                        });
                }

                doFetch();
                setTimeout(doFetch, 2000);

                const state = {
                    map,
                    engine,
                    applyPositionUpdate,
                    updateLastMaxSpeed: (val) => {
                        if (typeof val === 'number' && !isNaN(val)) lastMaxSpeed = val;
                    },
                };
                window['_vehicleMap_' + mapId] = state;

                if (typeof window.Echo !== 'undefined' && companyId) {
                    try {
                        window.Echo.channel('company.' + companyId + '.vehicles')
                            .listen('.location.updated', (e) => {
                                if (e && e.vehicle_id && state.applyPositionUpdate) {
                                    state.applyPositionUpdate(e.vehicle_id, {
                                        lat: e.lat,
                                        lng: e.lng,
                                        speed: e.speed,
                                        address: e.address,
                                        status: e.status,
                                        tracker_timestamp: e.tracker_timestamp,
                                        odometer: e.odometer,
                                        machine_status: e.machine_status,
                                    });
                                }
                            });
                    } catch (err) {
                        console.warn('Echo subscription failed:', err);
                    }
                }
            }

            document.addEventListener('livewire:load', () => setTimeout(initVehicleMap, 200));
            document.addEventListener('DOMContentLoaded', () => setTimeout(initVehicleMap, 250));
            if (document.readyState !== 'loading') setTimeout(initVehicleMap, 250);

            window.addEventListener('vehicle-tracking-tab-visible', () => {
                const el = document.querySelector('[id^="vehicle-map-"]');
                if (!el) return;
                const state = window['_vehicleMap_' + el.id];
                if (state?.map) {
                    state.map.invalidateSize();
                }
            });

            document.addEventListener('livewire:init', () => {
                Livewire.on('positions-updated', (e) => {
                    const positions = e?.positions ?? e;
                    const el = document.querySelector('[id^="vehicle-map-"]');
                    if (!el || !positions || typeof positions !== 'object') return;
                    const state = window['_vehicleMap_' + el?.id];
                    if (!state?.applyPositionUpdate) return;
                    let maxSpeed = 0;
                    Object.keys(positions).forEach((id, idx) => {
                        const d = positions[id];
                        if (d && !d.error) {
                            if (d.speed != null) maxSpeed = Math.max(maxSpeed, parseFloat(d
                                .speed) || 0);
                            try {
                                state.applyPositionUpdate(parseInt(id), d, idx);
                            } catch (err) {
                                console.warn('Map update error:', err);
                            }
                        }
                    });
                    if (state.updateLastMaxSpeed) state.updateLastMaxSpeed(maxSpeed);
                });
            });
        })();
    </script>
@endpush
