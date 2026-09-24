<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isManagementRole($user);
    }

    public function create(User $user): bool
    {
        return $this->isManagementRole($user);
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $this->isManagementRole($user);
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $this->isManagementRole($user);
    }

    private function isManagementRole(User $user): bool
    {
        return in_array($user->role, [Role::Admin, Role::Warehouse], true);
    }
}
