/**
 * MapStyleManager - Professional map tile style switching
 * Supports multiple providers, localStorage persistence, RTL, dark mode
 */

const STORAGE_KEY = 'vehicle_map_style';
const DEFAULT_STYLE = 'carto_dark';

export const MAP_STYLES = {
    carto_dark: {
        id: 'carto_dark',
        name: 'Carto Dark',
        icon: 'fa-moon',
        url: 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
        options: {
            attribution: '&copy; <a href="https://carto.com/">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20,
            maxNativeZoom: 19,
        },
        opacity: 1,
    },
    osm_humanitarian: {
        id: 'osm_humanitarian',
        name: 'Humanitarian',
        icon: 'fa-people-group',
        url: 'https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png',
        options: {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, Tiles style by <a href="https://hot.openstreetmap.org/">Humanitarian OSM Team</a>',
            maxZoom: 19,
        },
        opacity: 1,
    },
    stadia_alidade: {
        id: 'stadia_alidade',
        name: 'Stadia',
        icon: 'fa-map',
        url: 'https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png',
        options: {
            attribution: '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>',
            maxZoom: 20,
            maxNativeZoom: 20,
        },
        opacity: 1,
    },
    esri_imagery: {
        id: 'esri_imagery',
        name: 'Satellite',
        icon: 'fa-satellite',
        url: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        options: {
            attribution: '&copy; <a href="https://www.esri.com/">Esri</a>',
            maxZoom: 19,
            maxNativeZoom: 19,
        },
        opacity: 1,
    },
};

export class MapStyleManager {
    constructor(map, options = {}) {
        this.map = map;
        this.currentLayer = null;
        this.storageKey = options.storageKey ?? STORAGE_KEY;
        this.defaultStyle = options.defaultStyle ?? DEFAULT_STYLE;
        this.onStyleChange = options.onStyleChange ?? (() => {});
    }

    getSavedStyle() {
        try {
            const saved = localStorage.getItem(this.storageKey);
            if (saved && MAP_STYLES[saved]) return saved;
        } catch (_) {}
        if (this.defaultStyle && MAP_STYLES[this.defaultStyle]) {
            return this.defaultStyle;
        }
        if (typeof window !== 'undefined' && window.matchMedia?.('(prefers-color-scheme: dark)')?.matches) {
            return 'carto_dark';
        }
        return 'osm_humanitarian';
    }

    saveStyle(styleId) {
        try {
            localStorage.setItem(this.storageKey, styleId);
        } catch (_) {}
    }

    applyStyle(styleId) {
        const style = MAP_STYLES[styleId];
        if (!style) return false;

        if (this.currentLayer) {
            this.map.removeLayer(this.currentLayer);
            this.currentLayer = null;
        }

        const layer = L.tileLayer(style.url, {
            ...style.options,
            opacity: style.opacity ?? 1,
        });
        layer.addTo(this.map);
        this.currentLayer = layer;
        this.saveStyle(styleId);
        this.onStyleChange(styleId, style);
        return true;
    }

    init() {
        const styleId = this.getSavedStyle();
        this.applyStyle(styleId);
        return styleId;
    }

    getStyles() {
        return Object.values(MAP_STYLES);
    }
}

export default MapStyleManager;
