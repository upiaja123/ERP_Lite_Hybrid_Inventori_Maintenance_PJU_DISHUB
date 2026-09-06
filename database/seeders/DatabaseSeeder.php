<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RbacSeeder::class,
            MasterDataSeeder::class,
        ]);

        // Ensure default admin/users exist for development & testing
        $superadminRole = Role::where('role', 'superadmin')->first();
        $adminGudangRole = Role::where('role', 'admin gudang')->first();
        $kepalaGudangRole = Role::where('role', 'kepala gudang')->first();
        $teknisiRole = Role::where('role', 'teknisi')->first();
        $viewerRole = Role::where('role', 'viewer')->first();

        $users = [
            [
                'name'     => 'Super Admin',
                'username' => 'superadmin',
                'email'    => 'admin@dishub.go.id',
                'password' => Hash::make('password'),
                'status'   => 'ACTIVE',
                'role'     => $superadminRole,
            ],
            [
                'name'     => 'Admin Gudang',
                'username' => 'admingudang',
                'email'    => 'gudang@dishub.go.id',
                'password' => Hash::make('password'),
                'status'   => 'ACTIVE',
                'role'     => $adminGudangRole,
            ],
            [
                'name'     => 'Kepala Gudang',
                'username' => 'kepalagudang',
                'email'    => 'kepala@dishub.go.id',
                'password' => Hash::make('password'),
                'status'   => 'ACTIVE',
                'role'     => $kepalaGudangRole,
            ],
            [
                'name'     => 'Teknisi Satu',
                'username' => 'teknisi1',
                'email'    => 'teknisi@dishub.go.id',
                'password' => Hash::make('password'),
                'status'   => 'ACTIVE',
                'role'     => $teknisiRole,
            ],
            [
                'name'     => 'Viewer Dishub',
                'username' => 'viewer',
                'email'    => 'viewer@dishub.go.id',
                'password' => Hash::make('password'),
                'status'   => 'ACTIVE',
                'role'     => $viewerRole,
            ],
        ];

        foreach ($users as $uData) {
            $role = $uData['role'];
            unset($uData['role']);

            $user = User::firstOrCreate(
                ['email' => $uData['email']],
                array_merge($uData, ['role_id' => $role ? $role->id : null])
            );

            if ($role && !$user->roles->contains('id', $role->id)) {
                $user->roles()->syncWithoutDetaching([$role->id]);
            }
        }
    }
}
