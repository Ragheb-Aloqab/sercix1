<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Technician\DashboardController;
use App\Http\Controllers\Technician\TasksController;
use App\Http\Controllers\Technician\OrderStatusController;
use App\Http\Controllers\Technician\AttachmentsController;
use App\Http\Controllers\Technician\LocationController;
use App\Http\Controllers\Technician\NotificationsController;
use App\Livewire\Tech\Settings;

Route::middleware(['auth:web', 'active', 'role:technician'])
    ->prefix('tech')
    ->name('tech.')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Tasks (assigned orders)
        Route::get('/tasks', [TasksController::class, 'index'])
            ->name('tasks.index');

        Route::get('/tasks/{order}', [TasksController::class, 'show'])
            ->name('tasks.show')
            ->whereNumber('order');

        // Alias ( orders.show)
        Route::get('/orders/{order}', [TasksController::class, 'show'])
            ->name('orders.show')
            ->whereNumber('order');

        //  Accept / Reject ( PATCH)
        Route::patch('/tasks/{order}/accept', [TasksController::class, 'accept'])
            ->name('tasks.accept')
            ->whereNumber('order');

        Route::patch('/tasks/{order}/reject', [TasksController::class, 'reject'])
            ->name('tasks.reject')
            ->whereNumber('order');

        // Update order status
        Route::patch('/tasks/{order}/status', [OrderStatusController::class, 'update'])
            ->name('tasks.status.update')
            ->whereNumber('order');

        // Attachments (before / after)
        Route::post('/tasks/{order}/attachments/before', [AttachmentsController::class, 'storeBefore'])
            ->name('tasks.attachments.before')
            ->whereNumber('order');

        Route::post('/tasks/{order}/attachments/after', [AttachmentsController::class, 'storeAfter'])
            ->name('tasks.attachments.after')
            ->whereNumber('order');

        Route::delete('/tasks/{order}/attachments/{attachment}', [AttachmentsController::class, 'destroy'])
            ->name('tasks.attachments.destroy')
            ->whereNumber('order')
            ->whereNumber('attachment');

        // Confirm completion (optional)
        Route::post('/tasks/{order}/confirm-complete', [TasksController::class, 'confirmComplete'])
            ->name('tasks.confirmComplete')
            ->whereNumber('order');

        // Live location ping
        Route::post('/location/ping', [LocationController::class, 'store'])
            ->name('location.ping');

        // Notifications
        Route::get('/notifications', [NotificationsController::class, 'index'])
            ->name('notifications.index');

        Route::patch('/notifications/{notification}/read', [NotificationsController::class, 'markRead'])
            ->name('notifications.read');

        // Settings (Livewire)
        Route::get('/settings', Settings::class)
            ->name('settings');
    });
