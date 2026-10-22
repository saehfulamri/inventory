<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isManagementRole($user);
    }

    public function create(User $user): bool
    {
        return $this->isManagementRole($user);
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->isManagementRole($user);
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $this->isManagementRole($user);
    }

    private function isManagementRole(User $user): bool
    {
        return in_array($user->role, [Role::Admin, Role::Warehouse], true);
    }
}
