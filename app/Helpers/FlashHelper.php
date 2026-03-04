<?php

namespace App\Helpers;

class FlashHelper
{
    public static function success(string $message): array
    {
        return ['type' => 'success', 'message' => $message];
    }

    public static function error(string $message): array
    {
        return ['type' => 'error', 'message' => $message];
    }

    public static function warning(string $message): array
    {
        return ['type' => 'warning', 'message' => $message];
    }

    public static function info(string $message): array
    {
        return ['type' => 'info', 'message' => $message];
    }
}
