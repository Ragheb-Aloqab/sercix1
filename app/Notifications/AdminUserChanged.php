<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminUserChanged extends Notification
{
    use Queueable;

    public function __construct(
        public string $actorType, // company|technician
        public int $actorId,
        public string $title,
        public array $changes = []
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'actor_type' => $this->actorType,
            'actor_id'   => $this->actorId,
            'title'      => $this->title,
            'changes'    => $this->changes,
            'at'         => now()->toDateTimeString(),
        ];
    }
}
