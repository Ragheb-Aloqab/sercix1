<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;
use App\Models\User;
class OrderAssignedToTechnician
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;
    public User $technician;
    /**
     * Create a new event instance.
     */

    public function __construct(Order $order, User $technician)
    {
        $this->order = $order;
        $this->technician = $technician;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
