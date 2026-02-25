/**
 * Vehicle Tracker Engine - Uber/Careem-style smooth tracking
 * - 60fps interpolation via requestAnimationFrame
 * - Bearing calculation & smooth rotation
 * - Kalman-like smoothing & spike filtering
 * - Speed-based animation duration
 * - Movement buffer queue
 */

const DEG_TO_RAD = Math.PI / 180;
const RAD_TO_DEG = 180 / Math.PI;
const MAX_SPIKE_METERS = 500;        // Ignore jumps > 500m (GPS glitch)
const MIN_ANIMATION_MS = 800;         // Min duration for interpolation
const MAX_ANIMATION_MS = 4000;        // Max duration
const FRAMES_PER_SECOND = 60;
const FRAME_INTERVAL_MS = 1000 / FRAMES_PER_SECOND;

/**
 * Ease-in-out cubic: smooth start and end
 */
function easeInOutCubic(t) {
    return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

/**
 * Haversine distance in meters
 */
function haversineMeters(lat1, lng1, lat2, lng2) {
    const R = 6371000;
    const dLat = (lat2 - lat1) * DEG_TO_RAD;
    const dLng = (lng2 - lng1) * DEG_TO_RAD;
    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(lat1 * DEG_TO_RAD) * Math.cos(lat2 * DEG_TO_RAD) * Math.sin(dLng / 2) ** 2;
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return R * c;
}

/**
 * Bearing in degrees (0-360) from point 1 to point 2
 */
function bearing(lat1, lng1, lat2, lng2) {
    const φ1 = lat1 * DEG_TO_RAD;
    const φ2 = lat2 * DEG_TO_RAD;
    const Δλ = (lng2 - lng1) * DEG_TO_RAD;
    const y = Math.sin(Δλ) * Math.cos(φ2);
    const x = Math.cos(φ1) * Math.sin(φ2) - Math.sin(φ1) * Math.cos(φ2) * Math.cos(Δλ);
    let θ = Math.atan2(y, x) * RAD_TO_DEG;
    return (θ + 360) % 360;
}

/**
 * Normalize angle difference for smooth rotation (-180 to 180)
 */
function shortestAngleDiff(from, to) {
    let diff = (to - from + 360) % 360;
    if (diff > 180) diff -= 360;
    return diff;
}

/**
 * Simple 1D Kalman filter for smoothing
 */
class Kalman1D {
    constructor(processNoise = 0.01, measurementNoise = 0.1) {
        this.Q = processNoise;
        this.R = measurementNoise;
        this.P = 1;
        this.x = null;
    }
    update(measurement) {
        if (this.x === null) {
            this.x = measurement;
            return measurement;
        }
        this.P = this.P + this.Q;
        const K = this.P / (this.P + this.R);
        this.x = this.x + K * (measurement - this.x);
        this.P = (1 - K) * this.P;
        return this.x;
    }
    reset() { this.x = null; this.P = 1; }
}

/**
 * Per-vehicle state for interpolation
 */
class VehicleState {
    constructor(vehicleId) {
        this.vehicleId = vehicleId;
        this.lat = null;
        this.lng = null;
        this.bearingDeg = 0;
        this.speed = 0;
        this.meta = {};
        this.kalmanLat = new Kalman1D(0.005, 0.05);
        this.kalmanLng = new Kalman1D(0.005, 0.05);
        this.animation = null;
    }

    applyRawUpdate(data) {
        if (!data || data.lat == null || data.lng == null) return false;
        let lat = parseFloat(data.lat);
        let lng = parseFloat(data.lng);
        if (lat < -90 || lat > 90) [lat, lng] = [lng, lat];

        // Spike filter
        if (this.lat != null && this.lng != null) {
            const dist = haversineMeters(this.lat, this.lng, lat, lng);
            if (dist > MAX_SPIKE_METERS) return false;
        }

        // Kalman smoothing
        lat = this.kalmanLat.update(lat);
        lng = this.kalmanLng.update(lng);

        const prevLat = this.lat;
        const prevLng = this.lng;
        this.lat = lat;
        this.lng = lng;
        this.speed = data.speed != null ? parseFloat(data.speed) : this.speed;
        this.meta = {
            address: data.address ?? this.meta.address,
            odometer: data.odometer ?? this.meta.odometer,
            tracker_timestamp: data.tracker_timestamp ?? this.meta.tracker_timestamp,
            machine_status: data.machine_status ?? this.meta.machine_status,
            status: data.status ?? this.meta.status,
        };

        const targetBearing = (prevLat != null && prevLng != null)
            ? bearing(prevLat, prevLng, lat, lng)
            : this.bearingDeg;

        return { prevLat, prevLng, targetBearing, isFirst: prevLat == null };
    }
}

/**
 * Vehicle Tracker Engine - main class
 */
export class VehicleTrackerEngine {
    constructor(options = {}) {
        this.onFrame = options.onFrame || (() => {});
        this.vehicles = new Map();
        this.rafId = null;
        this.lastFrameTime = 0;
        this.running = false;
    }

    /**
     * Feed a new position update (from polling or WebSocket)
     */
    pushUpdate(vehicleId, data) {
        let state = this.vehicles.get(vehicleId);
        if (!state) {
            state = new VehicleState(vehicleId);
            this.vehicles.set(vehicleId, state);
        }

        const result = state.applyRawUpdate(data);
        if (!result) return;

        const { prevLat, prevLng, targetBearing, isFirst } = result;

        if (isFirst) {
            state.bearingDeg = targetBearing;
            this.onFrame(vehicleId, {
                lat: state.lat,
                lng: state.lng,
                bearing: state.bearingDeg,
                meta: state.meta,
                speed: state.speed,
                isInstant: true,
            });
            return;
        }

        // Speed-based duration: faster = shorter animation
        const speed = state.speed || 0;
        const dist = haversineMeters(prevLat, prevLng, state.lat, state.lng);
        let durationMs = MIN_ANIMATION_MS;
        if (speed > 0 && dist > 1) {
            const speedMs = speed / 3.6;
            durationMs = Math.min(MAX_ANIMATION_MS, Math.max(MIN_ANIMATION_MS, (dist / speedMs) * 0.5));
        }

        if (state.animation) {
            state.animation.cancel = true;
        }

        const startTime = performance.now();
        const startLat = prevLat;
        const startLng = prevLng;
        const endLat = state.lat;
        const endLng = state.lng;
        const startBearing = state.bearingDeg;
        const endBearing = targetBearing;

        state.animation = {
            cancel: false,
            startTime,
            durationMs,
            startLat,
            startLng,
            endLat,
            endLng,
            startBearing,
            endBearing,
            meta: { ...state.meta },
            speed: state.speed,
        };
        state.bearingDeg = endBearing;

        if (!this.running) {
            this.startLoop();
        }
    }

    startLoop() {
        this.running = true;
        this.lastFrameTime = performance.now();

        const loop = (now) => {
            this.rafId = requestAnimationFrame(loop);
            const dt = now - this.lastFrameTime;
            if (dt < FRAME_INTERVAL_MS) return;
            this.lastFrameTime = now;

            let hasActive = false;
            for (const [vehicleId, state] of this.vehicles) {
                const anim = state.animation;
                if (!anim || anim.cancel) continue;

                const elapsed = now - anim.startTime;
                const t = Math.min(1, elapsed / anim.durationMs);
                const eased = easeInOutCubic(t);

                const lat = anim.startLat + (anim.endLat - anim.startLat) * eased;
                const lng = anim.startLng + (anim.endLng - anim.startLng) * eased;
                const bearingDiff = shortestAngleDiff(anim.startBearing, anim.endBearing);
                const bearing = anim.startBearing + bearingDiff * eased;

                this.onFrame(vehicleId, {
                    lat,
                    lng,
                    bearing,
                    meta: anim.meta,
                    speed: anim.speed,
                    isInstant: false,
                });

                if (t < 1) {
                    hasActive = true;
                } else {
                    state.animation = null;
                }
            }

            if (!hasActive) {
                this.stopLoop();
            }
        };

        this.rafId = requestAnimationFrame(loop);
    }

    stopLoop() {
        if (this.rafId != null) {
            cancelAnimationFrame(this.rafId);
            this.rafId = null;
        }
        this.running = false;
    }

    getState(vehicleId) {
        return this.vehicles.get(vehicleId);
    }

    setInitialPosition(vehicleId, data) {
        let state = this.vehicles.get(vehicleId);
        if (!state) {
            state = new VehicleState(vehicleId);
            this.vehicles.set(vehicleId, state);
        }
        if (data && data.lat != null && data.lng != null) {
            state.lat = parseFloat(data.lat);
            state.lng = parseFloat(data.lng);
            state.kalmanLat.update(state.lat);
            state.kalmanLng.update(state.lng);
            state.speed = data.speed != null ? parseFloat(data.speed) : 0;
            state.meta = {
                address: data.address,
                odometer: data.odometer,
                tracker_timestamp: data.tracker_timestamp,
                machine_status: data.machine_status,
                status: data.status,
            };
        }
    }

    destroy() {
        this.stopLoop();
        this.vehicles.clear();
    }
}

export { bearing, haversineMeters, easeInOutCubic };
