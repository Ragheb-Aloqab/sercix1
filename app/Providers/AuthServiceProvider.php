<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\CompanyBranch;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vehicle;
use App\Policies\CompanyBranchPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\OrderPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\VehiclePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        Vehicle::class => VehiclePolicy::class,
        Invoice::class => InvoicePolicy::class,
        Payment::class => PaymentPolicy::class,
        CompanyBranch::class => CompanyBranchPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Admin bypass: admins can do everything
        Gate::before(function (User|Company|null $user, string $ability) {
            if ($user instanceof User && $user->role === 'admin') {
                return true;
            }
            return null; // Let policy handle it
        });
    }
}
