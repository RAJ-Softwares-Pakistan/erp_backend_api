<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vendor;

class VendorPolicy
{
    public function before(User $user, string $ability)
    {
        if ($user->role === config('roles.roles.super_admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Vendor $vendor): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Vendor $vendor): bool
    {
        return true;
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return true;
    }
}