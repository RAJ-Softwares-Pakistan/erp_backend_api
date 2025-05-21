<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Auth\Access\HandlesAuthorization;

class WarehousePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any warehouses.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the warehouse.
     */
    public function view(User $user, Warehouse $warehouse): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create warehouses.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the warehouse.
     */
    public function update(User $user, Warehouse $warehouse): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the warehouse.
     */
    public function delete(User $user, Warehouse $warehouse): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the warehouse.
     */
    public function restore(User $user, Warehouse $warehouse): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the warehouse.
     */
    public function forceDelete(User $user, Warehouse $warehouse): bool
    {
        return true;
    }
}
