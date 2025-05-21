<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Organization;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Organization $organization): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Organization $organization): bool
    {
        return true;
    }

    public function delete(User $user, Organization $organization): bool
    {
        return true;
    }

    public function restore(User $user, Organization $organization): bool
    {
        return true;
    }

    public function forceDelete(User $user, Organization $organization): bool
    {
        return true;
    }
}