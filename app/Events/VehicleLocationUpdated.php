<?php

namespace App\Events;

use App\Models\Vehicle;
use App\Models\VehicleLocation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Vehicle $vehicle,
        public VehicleLocation $location
    ) {}

    public function broadcastWhen(): bool
    {
        $driver = config('broadcasting.default', 'null');
        return in_array($driver, ['reverb', 'pusher', 'ably'], true);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('company.' . $this->vehicle->company_id . '.vehicles'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'location.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'vehicle_id' => $this->vehicle->id,
            'lat' => (float) $this->location->lat,
            'lng' => (float) $this->location->lng,
            'speed' => $this->location->speed ? (float) $this->location->speed : null,
            'address' => $this->location->address,
            'status' => $this->location->status,
            'tracker_timestamp' => $this->location->tracker_timestamp?->format('c'),
            'odometer' => $this->location->odometer ? (float) $this->location->odometer : null,
            'machine_status' => $this->extractMachineStatusFromRaw($this->location->raw_data),
        ];
    }

    private function extractMachineStatusFromRaw(?array $raw): ?string
    {
        if (!$raw) {
            return null;
        }
        $sensList = $raw['sens_list'] ?? [];
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
        $params = $raw['params'] ?? [];
        if (isset($params['io1'])) {
            $io1 = (string) $params['io1'];
            return ($io1 === '1' || $io1 === 'true') ? 'ON' : 'OFF';
        }
        return null;
    }
}
