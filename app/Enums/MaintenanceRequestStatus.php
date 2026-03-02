<?php

namespace App\Enums;

enum MaintenanceRequestStatus: string
{
    case NEW_REQUEST = 'new_request';
    case REJECTED = 'rejected';
    case WAITING_FOR_QUOTES = 'waiting_for_quotes';
    case QUOTE_SUBMITTED = 'quote_submitted';
    case CENTER_APPROVED = 'center_approved';
    case IN_PROGRESS = 'in_progress';
    case WAITING_FOR_INVOICE_APPROVAL = 'waiting_for_invoice_approval';
    case CLOSED = 'closed';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function transitions(): array
    {
        return [
            self::NEW_REQUEST->value => [
                self::REJECTED->value,
                self::WAITING_FOR_QUOTES->value,
            ],
            self::REJECTED->value => [],
            self::WAITING_FOR_QUOTES->value => [
                self::QUOTE_SUBMITTED->value,
            ],
            self::QUOTE_SUBMITTED->value => [
                self::CENTER_APPROVED->value,
                self::WAITING_FOR_QUOTES->value, // re-request quotes
            ],
            self::CENTER_APPROVED->value => [
                self::IN_PROGRESS->value,
            ],
            self::IN_PROGRESS->value => [
                self::WAITING_FOR_INVOICE_APPROVAL->value,
            ],
            self::WAITING_FOR_INVOICE_APPROVAL->value => [
                self::CLOSED->value,           // approve invoice
                self::IN_PROGRESS->value,      // reject invoice → center re-uploads
            ],
            self::CLOSED->value => [],
        ];
    }

    public static function canTransition(?string $from, string $to): bool
    {
        if (!$from) {
            return in_array($to, self::all(), true);
        }
        $allowed = self::transitions()[$from] ?? [];
        return in_array($to, $allowed, true);
    }

    public static function isTerminal(string $status): bool
    {
        return in_array($status, [self::REJECTED->value, self::CLOSED->value], true);
    }

    public function label(): string
    {
        return match ($this) {
            self::NEW_REQUEST => __('maintenance.status_new_request'),
            self::REJECTED => __('maintenance.status_rejected'),
            self::WAITING_FOR_QUOTES => __('maintenance.status_waiting_for_quotes'),
            self::QUOTE_SUBMITTED => __('maintenance.status_quote_submitted'),
            self::CENTER_APPROVED => __('maintenance.status_center_approved'),
            self::IN_PROGRESS => __('maintenance.status_in_progress'),
            self::WAITING_FOR_INVOICE_APPROVAL => __('maintenance.status_waiting_for_invoice_approval'),
            self::CLOSED => __('maintenance.status_closed'),
        };
    }
}
