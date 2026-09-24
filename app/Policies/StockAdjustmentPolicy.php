<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class StockAdjustmentPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, [Role::Admin, Role::Warehouse], true);
    }
}
