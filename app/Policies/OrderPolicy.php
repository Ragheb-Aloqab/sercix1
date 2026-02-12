<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Company $user): bool
    {
        return $this->isAdmin($user)
            || $this->isCompany($user)
            || $this->isTechnician($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Company $user, Order $order): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }
        if ($this->isCompany($user)) {
            return (int) $order->company_id === (int) $user->id;
        }
        if ($this->isTechnician($user)) {
            return (int) $order->technician_id === (int) $user->id;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Company $user): bool
    {
        return $this->isCompany($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Company $user, Order $order): bool
    {
        return $this->view($user, $order);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Company $user, Order $order): bool
    {
        return false;
    }

    public function assignTechnician(User|Company $user, Order $order): bool
    {
        return $this->isAdmin($user);
    }

    public function changeStatus(User|Company $user, Order $order): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }
        if ($this->isTechnician($user)) {
            return (int) $order->technician_id === (int) $user->id;
        }
        return false;
    }

    public function managePayment(User|Company $user, Order $order): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }
        if ($this->isCompany($user)) {
            return (int) $order->company_id === (int) $user->id;
        }
        return false;
    }

    public function manageAttachments(User|Company $user, Order $order): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }
        if ($this->isTechnician($user)) {
            return (int) $order->technician_id === (int) $user->id;
        }
        return false;
    }

    public function cancel(User|Company $user, Order $order): bool
    {
        if ($this->isCompany($user)) {
            return (int) $order->company_id === (int) $user->id;
        }
        return false;
    }

    public function restore(User|Company $user, Order $order): bool
    {
        return false;
    }

    public function forceDelete(User|Company $user, Order $order): bool
    {
        return false;
    }

    private function isAdmin(User|Company $user): bool
    {
        return $user instanceof User && $user->role === 'admin';
    }

    private function isCompany(User|Company $user): bool
    {
        return $user instanceof Company;
    }

    private function isTechnician(User|Company $user): bool
    {
        return $user instanceof User && $user->role === 'technician';
    }
}
