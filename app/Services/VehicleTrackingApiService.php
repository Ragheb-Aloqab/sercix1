<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Vehicle;
use App\Models\VehicleLocation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VehicleTrackingApiService
{
    /**
     * Fetch vehicle location from company's tracking API.
     * Expects API to accept IMEI and return JSON with location data.
     * Common response formats supported:
     * - { "lat": 24.xxx, "lng": 46.xxx, "speed": 60, "timestamp": "...", "address": "..." }
     * - { "data": { "latitude": ..., "longitude": ..., ... } }
     * - { "location": { "lat": ..., "lng": ... }, "speed": ... }
     *
     * @return array{success: bool, data?: array, error?: string}
     */
    public function fetchVehicleLocation(Company $company, string $imei): array
    {
        $baseUrl = rtrim($company->tracking_base_url ?? '', '/');
        $apiKey = $company->tracking_api_key;

        if (empty($baseUrl) || empty($apiKey)) {
            return ['success' => false, 'error' => __('tracking.api_not_configured')];
        }

        // Tawasol/Bostman format: ?data={"service":"objects","api_key":"...","imeis":"...","filters":"*"}
        $dataParam = json_encode([
            'service' => 'objects',
            'api_key' => $apiKey,
            'imeis' => $imei,
            'filters' => '*',
        ]);
        $tawasolUrl = $baseUrl . '?data=' . urlencode($dataParam);

        $headers = ['Accept' => 'application/json'];

        try {
            $normalized = null;
            $response = Http::withHeaders($headers)->timeout(10)->get($tawasolUrl);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    $deviceData = $this->extractDeviceData($data, $imei);
                    $normalized = $this->normalizeApiResponse($deviceData, $imei);
                }
            }

            if ($normalized === null) {
                $status = $response->status();
                $body = $response->body();
                Log::warning('Vehicle tracking API error', [
                    'company_id' => $company->id,
                    'imei' => $imei,
                    'status' => $status,
                    'base_url' => $baseUrl,
                    'body' => substr($body, 0, 500),
                ]);
                return [
                    'success' => false,
                    'error' => __('tracking.api_error') . ($response && $response->successful() ? ' (' . __('tracking.invalid_response') . ')' : ' (HTTP ' . $status . ')'),
                ];
            }

            return ['success' => true, 'data' => $normalized];
        } catch (\Throwable $e) {
            Log::error('Vehicle tracking API exception', [
                'company_id' => $company->id,
                'imei' => $imei,
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Extract device object from API response.
     * Handles Bostman-style { status, data: [...] } where data is an array of devices.
     */
    protected function extractDeviceData(array $data, string $imei): array
    {
        $payload = $data['data'] ?? $data;

        if (is_array($payload) && isset($payload[0]) && is_array($payload[0])) {
            foreach ($payload as $item) {
                $itemImei = (string) ($item['imei'] ?? $item['device_id'] ?? '');
                if ($itemImei === $imei) {
                    return $item;
                }
            }
            return [];
        }

        if (is_array($payload) && isset($payload['imei'])) {
            return $payload;
        }

        return $data;
    }

    /**
     * Normalize various API response formats to a standard structure.
     * Handles GeoJSON [lng, lat], swapped coordinates, and common API formats.
     */
    protected function normalizeApiResponse(array $data, string $imei): ?array
    {
        $lat = null;
        $lng = null;

        // GeoJSON format: coordinates are [longitude, latitude]
        if (isset($data['coordinates']) && is_array($data['coordinates']) && count($data['coordinates']) >= 2) {
            $lng = (float) $data['coordinates'][0];
            $lat = (float) $data['coordinates'][1];
        }
        // geometry.coordinates (nested GeoJSON)
        if (($lat === null || $lng === null) && isset($data['geometry']['coordinates']) && is_array($data['geometry']['coordinates'])) {
            $coords = $data['geometry']['coordinates'];
            $lng = (float) ($coords[0] ?? 0);
            $lat = (float) ($coords[1] ?? 0);
        }
        // Standard formats (check data wrapper for nested responses)
        if ($lat === null || $lng === null) {
            $lat = $data['lat'] ?? $data['latitude'] ?? $data['data']['lat'] ?? $data['data']['latitude'] ?? $data['location']['lat'] ?? $data['position']['lat'] ?? null;
            $lng = $data['lng'] ?? $data['lon'] ?? $data['longitude'] ?? $data['data']['lng'] ?? $data['data']['longitude'] ?? $data['location']['lng'] ?? $data['position']['lng'] ?? null;
        }

        if ($lat === null || $lng === null) {
            return null;
        }

        $lat = (float) $lat;
        $lng = (float) $lng;

        // Auto-fix swapped lat/lng: latitude must be [-90, 90], longitude [-180, 180]
        if ($lat < -90 || $lat > 90) {
            [$lat, $lng] = [$lng, $lat];
        }

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return null;
        }

        $speed = $data['speed'] ?? $data['data']['speed'] ?? $data['location']['speed'] ?? null;
        $speed = $speed !== null ? (float) $speed : null;
        $timestamp = $data['dt_tracker'] ?? $data['dt_server'] ?? $data['timestamp'] ?? $data['updated_at'] ?? $data['data']['timestamp'] ?? $data['last_update'] ?? $data['gps_time'] ?? null;
        $address = $data['address'] ?? $data['data']['address'] ?? $data['location']['address'] ?? null;
        $status = $data['status'] ?? $data['data']['status'] ?? null;
        $status = $status ? (string) $status : $this->inferStatusFromSpeed($speed);
        $params = $data['params'] ?? [];
        $odometer = $data['odometer'] ?? $data['data']['odometer'] ?? $data['mileage'] ?? null
            ?? ($params['odometer'] ?? $params['odo'] ?? $params['mileage'] ?? $params['km'] ?? $params['total_km'] ?? $params['total_odo'] ?? null)
            ?? $this->extractOdometerFromSensList($data['sens_list'] ?? []);
        $engineHours = $data['engine_hours'] ?? $data['data']['engine_hours'] ?? null;
        $fuelLevel = $data['fuel_level'] ?? $data['data']['fuel_level'] ?? $data['fuel'] ?? null;
        $machineStatus = $this->extractMachineStatus($data);

        return [
            'lat' => $lat,
            'lng' => $lng,
            'speed' => $speed,
            'address' => $address ? (string) $address : null,
            'status' => $status,
            'odometer' => $odometer !== null ? (float) $odometer : null,
            'engine_hours' => $engineHours !== null ? (float) $engineHours : null,
            'fuel_level' => $fuelLevel !== null ? (float) $fuelLevel : null,
            'machine_status' => $machineStatus,
            'tracker_timestamp' => $timestamp,
            'raw_data' => $data,
        ];
    }

    /**
     * Fetch and persist vehicle location to database.
     */
    public function fetchAndStoreLocation(Vehicle $vehicle): array
    {
        $imei = $vehicle->imei;
        if (empty($imei)) {
            return ['success' => false, 'error' => __('tracking.imei_required')];
        }

        $company = $vehicle->company;
        if (!$company) {
            return ['success' => false, 'error' => __('tracking.company_not_found')];
        }

        $result = $this->fetchVehicleLocation($company, $imei);

        if (!$result['success'] || !isset($result['data'])) {
            return $result;
        }

        return $this->storeLocation($vehicle, $result['data']);
    }

    /**
     * Fetch locations for all trackable vehicles in one API call (batch).
     */
    public function fetchAllForCompany(Company $company): array
    {
        $vehicles = $company->vehicles()
            ->whereNotNull('imei')
            ->where('imei', '!=', '')
            ->get();

        if ($vehicles->isEmpty()) {
            return [];
        }

        $imeis = $vehicles->pluck('imei')->filter()->values()->implode(',');
        $imeiToVehicle = $vehicles->keyBy('imei');

        $baseUrl = rtrim($company->tracking_base_url ?? '', '/');
        $apiKey = $company->tracking_api_key;

        if (empty($baseUrl) || empty($apiKey)) {
            return array_fill_keys($vehicles->pluck('id')->toArray(), ['success' => false, 'error' => __('tracking.api_not_configured')]);
        }

        $dataParam = json_encode([
            'service' => 'objects',
            'api_key' => $apiKey,
            'imeis' => $imeis,
            'filters' => '*',
        ]);
        $url = $baseUrl . '?data=' . urlencode($dataParam);

        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])->timeout(12)->get($url);
            $data = $response->successful() ? $response->json() : null;

            if (!is_array($data)) {
                return array_fill_keys($vehicles->pluck('id')->toArray(), ['success' => false, 'error' => __('tracking.api_error')]);
            }

            $payload = $data['data'] ?? $data;
            $items = is_array($payload) && isset($payload[0]) ? $payload : (isset($payload['imei']) ? [$payload] : []);

            $results = [];
            foreach ($vehicles as $vehicle) {
                $results[$vehicle->id] = ['success' => false, 'error' => __('tracking.invalid_response')];
            }

            foreach ($items as $item) {
                $itemImei = (string) ($item['imei'] ?? $item['device_id'] ?? '');
                $vehicle = $imeiToVehicle->get($itemImei);
                if (!$vehicle) {
                    continue;
                }
                $normalized = $this->normalizeApiResponse($item, $itemImei);
                if ($normalized === null) {
                    continue;
                }
                $stored = $this->storeLocation($vehicle, $normalized);
                $results[$vehicle->id] = $stored;
            }

            return $results;
        } catch (\Throwable $e) {
            Log::error('Vehicle tracking API batch exception', ['company_id' => $company->id, 'message' => $e->getMessage()]);
            return array_fill_keys($vehicles->pluck('id')->toArray(), ['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Store normalized location to database.
     */
    protected function storeLocation(Vehicle $vehicle, array $data): array
    {
        $trackerTs = $data['tracker_timestamp'] ?? null;
        if (is_string($trackerTs)) {
            try {
                $trackerTs = new \DateTimeImmutable($trackerTs);
            } catch (\Throwable $e) {
                $trackerTs = now();
            }
        } elseif (!$trackerTs instanceof \DateTimeInterface) {
            $trackerTs = now();
        }

        VehicleLocation::create([
            'vehicle_id' => $vehicle->id,
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'speed' => $data['speed'],
            'address' => $data['address'],
            'status' => $data['status'],
            'odometer' => $data['odometer'],
            'engine_hours' => $data['engine_hours'],
            'fuel_level' => $data['fuel_level'],
            'raw_data' => $data['raw_data'] ?? null,
            'tracker_timestamp' => $trackerTs,
        ]);

        return [
            'success' => true,
            'data' => array_merge($data, [
                'tracker_timestamp' => $trackerTs instanceof \DateTimeInterface ? $trackerTs->format('c') : (string) $trackerTs,
            ]),
        ];
    }

    /**
     * Extract odometer from Bostman sens_list (type odometer, odo, mileage).
     */
    protected function extractOdometerFromSensList(array $sensList): ?float
    {
        if (! is_array($sensList)) {
            return null;
        }
        foreach ($sensList as $s) {
            $type = strtolower($s['type'] ?? '');
            if (in_array($type, ['odometer', 'odo', 'mileage', 'km'], true)) {
                $val = $s['value'] ?? $s['val'] ?? $s['val_f'] ?? null;
                if ($val !== null && $val !== '') {
                    return (float) $val;
                }
            }
        }
        return null;
    }

    /**
     * Extract machine/engine status from Bostman sens_list or params.io1.
     */
    protected function extractMachineStatus(array $data): ?string
    {
        $sensList = $data['sens_list'] ?? [];
        if (is_array($sensList)) {
            foreach ($sensList as $s) {
                $type = strtolower($s['type'] ?? '');
                if ($type === 'acc' || $type === 'ignition') {
                    $val = (string) ($s['value'] ?? $s['val'] ?? '');
                    $text1 = $s['text_1'] ?? 'ON';
                    $text0 = $s['text_0'] ?? 'OFF';
                    return ($val === '1' || $val === 'true') ? $text1 : $text0;
                }
            }
        }
        $params = $data['params'] ?? [];
        if (is_array($params) && isset($params['io1'])) {
            $io1 = (string) $params['io1'];
            return ($io1 === '1' || $io1 === 'true') ? 'ON' : 'OFF';
        }
        return null;
    }

    private function inferStatusFromSpeed(?float $speed): string
    {
        if ($speed === null) {
            return 'idle';
        }
        if ($speed > 5) {
            return 'moving';
        }
        if ($speed == 0) {
            return 'stopped';
        }
        return 'idle';
    }
}
