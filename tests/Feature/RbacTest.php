<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    /**
     * Test user role check
     */
    public function test_user_has_role_check(): void
    {
        $role = Role::firstOrCreate(['role' => 'admin gudang']);
        $user = new User([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role_id' => $role->id,
            'status' => 'ACTIVE'
        ]);
        $user->setRelation('role', $role);

        $this->assertTrue($user->hasRole('admin gudang'));
        $this->assertFalse($user->hasRole('superadmin'));
    }

    /**
     * Test superadmin has all permissions
     */
    public function test_superadmin_bypasses_permission_checks(): void
    {
        $role = Role::firstOrCreate(['role' => 'superadmin']);
        $user = new User([
            'name' => 'Super Admin User',
            'email' => 'admin@example.com',
            'role_id' => $role->id,
            'status' => 'ACTIVE'
        ]);
        $user->setRelation('role', $role);

        $this->assertTrue($user->hasPermissionTo('inventory.view'));
        $this->assertTrue($user->hasPermissionTo('admin.user.manage'));
    }

    /**
     * Test inactive user is denied permission
     */
    public function test_inactive_user_is_denied(): void
    {
        $role = Role::firstOrCreate(['role' => 'admin gudang']);
        $user = new User([
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'role_id' => $role->id,
            'status' => 'INACTIVE'
        ]);
        $user->setRelation('role', $role);

        $this->assertFalse($user->isActive());
        $this->assertFalse($user->hasPermissionTo('inventory.view'));
    }
}
