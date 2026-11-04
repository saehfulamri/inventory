<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === Role::Admin;
    }

    public function create(User $user): bool
    {
        return $user->role === Role::Admin;
    }

    public function update(User $user, User $model): bool
    {
        return $user->role === Role::Admin && $user->id !== $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->role === Role::Admin && $user->id !== $model->id;
    }
}
