<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Purchase;
use App\Models\User;

class PurchasePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isManagementRole($user);
    }

    public function view(User $user, Purchase $purchase): bool
    {
        return $this->isManagementRole($user);
    }

    public function create(User $user): bool
    {
        return $this->isManagementRole($user);
    }

    public function finalize(User $user, Purchase $purchase): bool
    {
        return $this->isManagementRole($user);
    }

    private function isManagementRole(User $user): bool
    {
        return in_array($user->role, [Role::Admin, Role::Warehouse], true);
    }
}
