<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('admin.user.manage') || $user->hasRole('superadmin');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('admin.user.manage') || $user->hasRole('superadmin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermissionTo('admin.user.manage') || $user->hasRole('superadmin');
    }

    public function delete(User $user, User $model): bool
    {
        // Prevent deleting own account
        if ($user->id === $model->id) {
            return false;
        }

        return $user->hasPermissionTo('admin.user.manage') || $user->hasRole('superadmin');
    }
}
