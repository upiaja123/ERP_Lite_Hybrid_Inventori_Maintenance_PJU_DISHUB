<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Barang;

class BarangPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function view(User $user, Barang $barang): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('inventory.create');
    }

    public function update(User $user, Barang $barang): bool
    {
        return $user->hasPermissionTo('inventory.update');
    }

    public function delete(User $user, Barang $barang): bool
    {
        return $user->hasPermissionTo('inventory.delete');
    }
}
