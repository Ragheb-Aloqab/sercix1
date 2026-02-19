<?php

namespace App\Support;

final class OrderStatus
{
    public const PENDING_APPROVAL = 'pending_approval';
    public const APPROVED = 'approved';
    public const IN_PROGRESS = 'in_progress';
    public const PENDING_CONFIRMATION = 'pending_confirmation';
    public const COMPLETED = 'completed';
    public const REJECTED = 'rejected';
    public const CANCELLED = 'cancelled';

    /** @deprecated Use PENDING_APPROVAL */
    public const PENDING_COMPANY = 'pending_approval';

    /** @deprecated Use APPROVED */
    public const APPROVED_BY_COMPANY = 'approved';

    public const ALL = [
        self::PENDING_APPROVAL,
        self::APPROVED,
        self::IN_PROGRESS,
        self::PENDING_CONFIRMATION,
        self::COMPLETED,
        self::REJECTED,
        self::CANCELLED,
    ];

    /** Valid transitions: from => [to, ...] */
    public const TRANSITIONS = [
        self::PENDING_APPROVAL => [self::APPROVED, self::REJECTED, self::CANCELLED],
        self::APPROVED => [self::IN_PROGRESS, self::CANCELLED],
        self::IN_PROGRESS => [self::PENDING_CONFIRMATION, self::CANCELLED],
        self::PENDING_CONFIRMATION => [self::COMPLETED],
        self::COMPLETED => [],
        self::REJECTED => [],
        self::CANCELLED => [],
    ];

    /** Statuses where only company can transition (from pending_approval) */
    public const COMPANY_APPROVAL_ONLY = [self::PENDING_APPROVAL];

    /** Statuses where driver can set in_progress */
    public const DRIVER_CAN_START = [self::APPROVED];

    /** Statuses where driver can upload invoice */
    public const DRIVER_CAN_UPLOAD_INVOICE = [self::IN_PROGRESS];

    /** Statuses where company can confirm completion */
    public const COMPANY_CAN_CONFIRM = [self::PENDING_CONFIRMATION];

    public static function canTransition(?string $from, string $to): bool
    {
        if (!$from) {
            return in_array($to, self::ALL, true);
        }

        $allowed = self::TRANSITIONS[$from] ?? [];
        return in_array($to, $allowed, true);
    }

    public static function nextOptions(?string $from): array
    {
        return self::TRANSITIONS[$from] ?? [];
    }

    public static function isDriverReadOnly(string $status): bool
    {
        return $status === self::PENDING_APPROVAL;
    }

    public static function isTerminal(string $status): bool
    {
        return in_array($status, [self::COMPLETED, self::REJECTED, self::CANCELLED], true);
    }
}
