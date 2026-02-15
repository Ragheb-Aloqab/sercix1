<?php

namespace App\Support;

final class OrderStatus
{
    public const PENDING_COMPANY = 'pending_company';
    public const APPROVED_BY_COMPANY = 'approved_by_company';
    public const PENDING_ASSIGNMENT = 'pending_assignment';
    public const ASSIGNED_TO_TECHNICIAN = 'assigned_to_technician';
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';

    public const ALL = [
        self::PENDING_COMPANY,
        self::APPROVED_BY_COMPANY,
        self::PENDING_ASSIGNMENT,
        self::ASSIGNED_TO_TECHNICIAN,
        self::IN_PROGRESS,
        self::COMPLETED,
        self::CANCELLED,
    ];

    public const TRANSITIONS = [
        self::PENDING_COMPANY => [self::APPROVED_BY_COMPANY, self::CANCELLED],
        self::APPROVED_BY_COMPANY => [self::PENDING_ASSIGNMENT, self::ASSIGNED_TO_TECHNICIAN, self::CANCELLED],
        self::PENDING_ASSIGNMENT => [self::ASSIGNED_TO_TECHNICIAN, self::CANCELLED],
        self::ASSIGNED_TO_TECHNICIAN => [self::IN_PROGRESS, self::CANCELLED],
        self::IN_PROGRESS => [self::COMPLETED, self::CANCELLED],
        self::COMPLETED => [],
        self::CANCELLED => [],
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
