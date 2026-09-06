<?php

namespace App\Policies;

use App\Models\User;
use App\Models\BarangMasuk;

class BarangMasukPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('inventory.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('inventory.create');
    }

    public function approve(User $user, BarangMasuk $barangMasuk): bool
    {
        return $user->hasPermissionTo('inventory.approve');
    }

    public function void(User $user, BarangMasuk $barangMasuk): bool
    {
        return $user->hasPermissionTo('inventory.void');
    }

    public function delete(User $user, BarangMasuk $barangMasuk): bool
    {
        return $user->hasPermissionTo('inventory.delete');
    }
}
