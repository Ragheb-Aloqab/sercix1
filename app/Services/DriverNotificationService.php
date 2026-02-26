<?php

namespace App\Services;

use App\Models\DriverNotification;
use App\Models\Order;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Log;

class DriverNotificationService
{
    /**
     * Normalize driver phone for storage (use canonical format).
     */
    private function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }
        return trim($phone);
    }

    /**
     * Get phone variants for a driver (e.g. +966501234567 and 0501234567).
     */
    private function phoneVariants(?string $phone): array
    {
        if (empty($phone)) {
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
        return array_unique(array_filter($variants));
    }

    /**
     * Notify a driver by phone. Creates a notification for each phone variant to ensure delivery.
     */
    public function notify(string $driverPhone, string $type, array $data): void
    {
        $phone = $this->normalizePhone($driverPhone);
        if (!$phone) {
            return;
        }

        try {
            DriverNotification::create([
                'driver_phone' => $phone,
                'type' => $type,
                'data' => $data,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Driver notification failed', [
                'driver_phone' => $phone,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify driver when their order status changes.
     */
    public function notifyOrderStatusChanged(Order $order): void
    {
        $phone = $order->driver_phone ?? null;
        if (!$phone) {
            return;
        }

        $url = route('driver.request.show', $order);
        $statusLabel = __("common.status_{$order->status}");

        $this->notify($phone, 'order_status_updated', [
            'title' => __('messages.order_status_updated') ?: 'Order status updated',
            'message' => __('driver.request') . ' #' . $order->id . ': ' . $statusLabel,
            'order_id' => $order->id,
            'status' => $order->status,
            'url' => $url,
            'route' => $url,
        ]);
    }

    /**
     * Notify driver when their order is approved.
     */
    public function notifyOrderApproved(Order $order): void
    {
        $phone = $order->driver_phone ?? null;
        if (!$phone) {
            return;
        }

        $url = route('driver.request.show', $order);

        $this->notify($phone, 'order_approved', [
            'title' => __('messages.order_approved') ?: 'Order approved',
            'message' => __('driver.request') . ' #' . $order->id . ' ' . (__('messages.order_approved') ?: 'has been approved.'),
            'order_id' => $order->id,
            'url' => $url,
            'route' => $url,
        ]);
    }

    /**
     * Notify driver when their order is completed.
     */
    public function notifyOrderCompleted(Order $order): void
    {
        $phone = $order->driver_phone ?? null;
        if (!$phone) {
            return;
        }

        $url = route('driver.request.show', $order);

        $this->notify($phone, 'order_completed', [
            'title' => __('messages.order_completed') ?: 'Order completed',
            'message' => __('driver.request') . ' #' . $order->id . ' ' . (__('messages.order_completed') ?: 'has been completed.'),
            'order_id' => $order->id,
            'url' => $url,
            'route' => $url,
        ]);
    }

    /**
     * Notify driver when inspection is required.
     */
    public function notifyInspectionRequired(Vehicle $vehicle, string $dueDate): void
    {
        $phone = $vehicle->driver_phone ?? null;
        if (!$phone) {
            return;
        }

        $url = route('driver.inspections.index');

        $this->notify($phone, 'inspection_required', [
            'title' => __('inspections.notification_required') ?: 'Vehicle inspection required',
            'message' => __('inspections.notification_required_body', [
                'vehicle' => $vehicle->display_name,
                'due_date' => $dueDate,
            ]),
            'vehicle_id' => $vehicle->id,
            'url' => $url,
            'route' => $url,
        ]);
    }
}
