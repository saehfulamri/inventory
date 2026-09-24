<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isSaleRole($user);
    }

    public function view(User $user, Sale $sale): bool
    {
        return $this->isSaleRole($user);
    }

    public function create(User $user): bool
    {
        return $this->isSaleRole($user);
    }

    private function isSaleRole(User $user): bool
    {
        return in_array($user->role, [Role::Admin, Role::Cashier], true);
    }
}
