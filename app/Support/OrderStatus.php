<?php

namespace App\Support;

final class OrderStatus
{
    public const PENDING   = 'pending';
    public const REQUESTED = 'requested';  // driver submitted, waiting company approval
    public const ASSIGNED  = 'assigned';
    public const ON_THE_WAY = 'on_the_way';
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';
    public const ON_HOLD = 'on_hold';
    public const PAID      = 'paid';

    public const ALL = [
        self::PENDING,
        self::REQUESTED,
        self::ASSIGNED,
        self::ON_THE_WAY,
        self::IN_PROGRESS,
        self::COMPLETED,
        self::CANCELLED,
        self::ON_HOLD,
        self::PAID,
    ];

    // المسموح به من كل حالة إلى حالات أخرى
    public const TRANSITIONS = [
        self::PENDING => [self::ASSIGNED, self::CANCELLED],
        self::REQUESTED => [self::PENDING, self::CANCELLED],
        self::ASSIGNED => [self::ON_THE_WAY, self::CANCELLED],
        self::ON_THE_WAY => [self::IN_PROGRESS, self::CANCELLED],
        self::IN_PROGRESS => [self::COMPLETED, self::CANCELLED],
        self::COMPLETED => [self::PAID],
        self::PAID => [],

        // لو احتجتها
        self::CANCELLED => [],
        self::ON_HOLD => [],
    ];

    public static function canTransition(?string $from, string $to): bool
    {
        if (!$from) return in_array($to, self::ALL, true);

        $allowed = self::TRANSITIONS[$from] ?? [];
        return in_array($to, $allowed, true);
    }

    public static function nextOptions(?string $from): array
    {
        return self::TRANSITIONS[$from] ?? [];
    }
}
