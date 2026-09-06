<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Inventory Permissions
            ['name' => 'inventory.view', 'group' => 'inventory', 'description' => 'Melihat data barang & stok'],
            ['name' => 'inventory.create', 'group' => 'inventory', 'description' => 'Tambah barang & transaksi'],
            ['name' => 'inventory.update', 'group' => 'inventory', 'description' => 'Edit data barang'],
            ['name' => 'inventory.delete', 'group' => 'inventory', 'description' => 'Hapus data barang'],
            ['name' => 'inventory.approve', 'group' => 'inventory', 'description' => 'Menyetujui transaksi barang'],
            ['name' => 'inventory.void', 'group' => 'inventory', 'description' => 'Membatalkan (void) transaksi'],
            ['name' => 'inventory.export', 'group' => 'inventory', 'description' => 'Export data inventory'],

            // PJU Asset Permissions
            ['name' => 'pju.view', 'group' => 'pju', 'description' => 'Melihat aset PJU & lokasi'],
            ['name' => 'pju.create', 'group' => 'pju', 'description' => 'Tambah aset PJU & lokasi'],
            ['name' => 'pju.update', 'group' => 'pju', 'description' => 'Update aset PJU & lokasi'],
            ['name' => 'pju.distribute', 'group' => 'pju', 'description' => 'Distribusi PJU ke lokasi'],
            ['name' => 'pju.install', 'group' => 'pju', 'description' => 'Record pemasangan PJU'],

            // Maintenance Permissions
            ['name' => 'maintenance.view', 'group' => 'maintenance', 'description' => 'Melihat laporan & WO maintenance'],
            ['name' => 'maintenance.create', 'group' => 'maintenance', 'description' => 'Buat laporan kerusakan & WO'],
            ['name' => 'maintenance.assign', 'group' => 'maintenance', 'description' => 'Assign teknisi maintenance'],
            ['name' => 'maintenance.complete', 'group' => 'maintenance', 'description' => 'Selesaikan maintenance'],

            // Vendor & Retur Permissions
            ['name' => 'vendor.view', 'group' => 'vendor', 'description' => 'Melihat supplier & garansi'],
            ['name' => 'vendor.retur.create', 'group' => 'vendor', 'description' => 'Buat retur vendor'],
            ['name' => 'vendor.retur.approve', 'group' => 'vendor', 'description' => 'Approve retur vendor'],

            // Laporan Permissions
            ['name' => 'laporan.view', 'group' => 'laporan', 'description' => 'Melihat laporan'],
            ['name' => 'laporan.export', 'group' => 'laporan', 'description' => 'Export PDF & Excel laporan'],

            // Admin Permissions
            ['name' => 'admin.user.manage', 'group' => 'admin', 'description' => 'Manajemen pengguna/user'],
            ['name' => 'admin.role.manage', 'group' => 'admin', 'description' => 'Manajemen role & permission'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }

        // Roles (Standard Indonesian & English Documentation Aliases)
        $superadmin = Role::firstOrCreate(['role' => 'superadmin']);
        $systemAdmin = Role::firstOrCreate(['role' => 'System Administrator']);
        $adminGudang = Role::firstOrCreate(['role' => 'admin gudang']);
        $kepalaGudang = Role::firstOrCreate(['role' => 'kepala gudang']);
        $warehouseManager = Role::firstOrCreate(['role' => 'Warehouse Manager']);
        $teknisi = Role::firstOrCreate(['role' => 'teknisi']);
        $deptManager = Role::firstOrCreate(['role' => 'Department Manager']);
        $viewer = Role::firstOrCreate(['role' => 'viewer']);

        // Assign permissions to Admin Gudang
        $adminPermissions = Permission::whereIn('group', ['inventory', 'pju', 'vendor', 'laporan'])->get();
        $adminGudang->permissions()->sync($adminPermissions->pluck('id'));

        // Assign permissions to Kepala Gudang & Warehouse Manager
        $managerPermissions = Permission::whereIn('group', ['inventory', 'pju', 'maintenance', 'vendor', 'laporan'])->get();
        $kepalaGudang->permissions()->sync($managerPermissions->pluck('id'));
        $warehouseManager->permissions()->sync($managerPermissions->pluck('id'));

        // Assign permissions to Teknisi
        $teknisiPermissions = Permission::whereIn('name', ['pju.view', 'pju.install', 'maintenance.view', 'maintenance.complete'])->get();
        $teknisi->permissions()->sync($teknisiPermissions->pluck('id'));

        // Assign permissions to Department Manager (Approval + Export + View)
        $deptManagerPermissions = Permission::whereIn('name', [
            'inventory.view', 'inventory.approve', 'pju.view', 'maintenance.view', 'laporan.view', 'laporan.export', 'vendor.view'
        ])->get();
        $deptManager->permissions()->sync($deptManagerPermissions->pluck('id'));

        // Assign permissions to Viewer (View Only, No Export, No Approval)
        $viewerPermissions = Permission::whereIn('name', ['inventory.view', 'pju.view', 'maintenance.view', 'laporan.view'])->get();
        $viewer->permissions()->sync($viewerPermissions->pluck('id'));

        // System Administrator has all permissions
        $allPermissions = Permission::all();
        $systemAdmin->permissions()->sync($allPermissions->pluck('id'));
    }
}
