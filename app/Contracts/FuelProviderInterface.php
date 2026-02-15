<?php

namespace App\Contracts;

use App\Models\Vehicle;

/**
 * Contract for future integration with external fuel provider APIs.
 * Implement this interface when adding support for fuel cards, fleet management APIs, etc.
 */
interface FuelProviderInterface
{
    /**
     * Unique provider identifier (e.g. 'api_fleet_card', 'api_wataniya').
     */
    public function getProviderKey(): string;

    /**
     * Fetch fuel transactions for a vehicle within a date range.
     *
     * @return array<int, array{external_id: string, liters: float, cost: float, refilled_at: \Carbon\Carbon, odometer_km?: int, metadata?: array}>
     */
    public function fetchTransactionsForVehicle(Vehicle $vehicle, \Carbon\Carbon $from, \Carbon\Carbon $to): array;

    /**
     * Sync a single transaction from the provider into our fuel_refills table.
     * Returns the created/updated FuelRefill model.
     */
    public function syncTransaction(array $transaction, Vehicle $vehicle): \App\Models\FuelRefill;
}
