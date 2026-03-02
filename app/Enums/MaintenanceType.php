<?php

namespace App\Enums;

enum MaintenanceType: string
{
    case PERIODIC = 'periodic';
    case EMERGENCY = 'emergency';
    case INSPECTION = 'inspection';
    case PARTS = 'parts';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::PERIODIC => __('maintenance.type_periodic'),
            self::EMERGENCY => __('maintenance.type_emergency'),
            self::INSPECTION => __('maintenance.type_inspection'),
            self::PARTS => __('maintenance.type_parts'),
        };
    }
}
