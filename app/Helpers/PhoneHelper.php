<?php

namespace App\Helpers;

/**
 * Centralized phone normalization for driver/company lookup.
 * Used by driver session, login, and notifications to match +966 / 0xx variants.
 */
class PhoneHelper
{
    /**
     * Return all normalized variants of a phone number for DB matching.
     * E.g. +966501234567 -> ['+966501234567', '0501234567'].
     *
     * @return array<int, string>
     */
    public static function variants(?string $phone): array
    {
        if ($phone === null || $phone === '') {
            return [];
        }
        $variants = [trim($phone)];
        if (str_starts_with($phone, '+966')) {
            $variants[] = '0' . substr($phone, 4);
        }
        if (str_starts_with($phone, '0') && strlen(preg_replace('/[^0-9]/', '', $phone)) >= 10) {
            $digits = preg_replace('/[^0-9]/', '', $phone);
            $variants[] = '+966' . substr($digits, 1, 9);
        }
        return array_values(array_unique(array_filter($variants)));
    }
}
