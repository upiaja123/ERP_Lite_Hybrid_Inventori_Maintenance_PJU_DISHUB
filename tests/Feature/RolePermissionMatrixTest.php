<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Jenis;
use App\Models\LokasiPju;
use App\Models\MaintenancePju;
use App\Models\PjuAsset;
use App\Models\Role;
use App\Models\Satuan;
use App\Models\StockOpname;
use App\Models\Supplier;
use App\Models\User;
use App\Models\ReturVendor;
use Tests\TestCase;

class RolePermissionMatrixTest extends TestCase
{
    protected User $superadmin;
    protected User $kepalaGudang;
    protected User $adminGudang;
    protected User $teknisi;
    protected User $viewer;

    protected function setUp(): void
    {
        parent::setUp();

        $roleSuper = Role::firstOrCreate(['role' => 'superadmin']);
        $roleKepala = Role::firstOrCreate(['role' => 'kepala gudang']);
        $roleAdmin = Role::firstOrCreate(['role' => 'admin gudang']);
        $roleTeknisi = Role::firstOrCreate(['role' => 'teknisi']);
        $roleViewer = Role::firstOrCreate(['role' => 'viewer']);

        $this->superadmin = User::firstOrCreate(
            ['email' => 'matrix_super@test.com'],
            ['name' => 'Superadmin Matrix', 'password' => bcrypt('password'), 'role_id' => $roleSuper->id, 'status' => 'ACTIVE']
        );
        $this->superadmin->roles()->syncWithoutDetaching([$roleSuper->id]);

        $this->kepalaGudang = User::firstOrCreate(
            ['email' => 'matrix_kepala@test.com'],
            ['name' => 'Kepala Gudang Matrix', 'password' => bcrypt('password'), 'role_id' => $roleKepala->id, 'status' => 'ACTIVE']
        );
        $this->kepalaGudang->roles()->syncWithoutDetaching([$roleKepala->id]);

        $this->adminGudang = User::firstOrCreate(
            ['email' => 'matrix_admin@test.com'],
            ['name' => 'Admin Gudang Matrix', 'password' => bcrypt('password'), 'role_id' => $roleAdmin->id, 'status' => 'ACTIVE']
        );
        $this->adminGudang->roles()->syncWithoutDetaching([$roleAdmin->id]);

        $this->teknisi = User::firstOrCreate(
            ['email' => 'matrix_teknisi@test.com'],
            ['name' => 'Teknisi Matrix', 'password' => bcrypt('password'), 'role_id' => $roleTeknisi->id, 'status' => 'ACTIVE']
        );
        $this->teknisi->roles()->syncWithoutDetaching([$roleTeknisi->id]);

        $this->viewer = User::firstOrCreate(
            ['email' => 'matrix_viewer@test.com'],
            ['name' => 'Viewer Matrix', 'password' => bcrypt('password'), 'role_id' => $roleViewer->id, 'status' => 'ACTIVE']
        );
        $this->viewer->roles()->syncWithoutDetaching([$roleViewer->id]);
    }

    /**
     * TEST: User Management Access Isolation
     */
    public function test_user_management_accessible_only_by_superadmin(): void
    {
        $this->actingAs($this->superadmin)->get('/data-pengguna')->assertStatus(200);
        $this->actingAs($this->kepalaGudang)->get('/data-pengguna')->assertStatus(403);
        $this->actingAs($this->adminGudang)->get('/data-pengguna')->assertStatus(403);
        $this->actingAs($this->teknisi)->get('/data-pengguna')->assertStatus(403);
        $this->actingAs($this->viewer)->get('/data-pengguna')->assertStatus(403);
    }

    /**
     * TEST: Activity Log & Import Access
     */
    public function test_activity_log_accessible_by_superadmin_and_kepala_gudang(): void
    {
        $this->actingAs($this->superadmin)->get('/aktivitas-user')->assertStatus(200);
        $this->actingAs($this->kepalaGudang)->get('/aktivitas-user')->assertStatus(200);
        $this->actingAs($this->adminGudang)->get('/aktivitas-user')->assertStatus(403);
        $this->actingAs($this->teknisi)->get('/aktivitas-user')->assertStatus(403);
        $this->actingAs($this->viewer)->get('/aktivitas-user')->assertStatus(403);
    }

    /**
     * TEST: Dashboard Accessible by All Active Roles
     */
    public function test_dashboard_accessible_by_all_authenticated_roles(): void
    {
        $this->actingAs($this->superadmin)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->kepalaGudang)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->adminGudang)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->teknisi)->get('/dashboard')->assertStatus(200);
        $this->actingAs($this->viewer)->get('/dashboard')->assertStatus(200);
    }

    /**
     * TEST: Reports Accessible by Superadmin, Kepala Gudang, Admin Gudang, Viewer (Teknisi Denied)
     */
    public function test_reports_accessible_by_authorized_roles_and_denied_for_teknisi(): void
    {
        $this->actingAs($this->superadmin)->get('/laporan-stok')->assertStatus(200);
        $this->actingAs($this->kepalaGudang)->get('/laporan-stok')->assertStatus(200);
        $this->actingAs($this->adminGudang)->get('/laporan-stok')->assertStatus(200);
        $this->actingAs($this->viewer)->get('/laporan-stok')->assertStatus(200);
        $this->actingAs($this->teknisi)->get('/laporan-stok')->assertStatus(403);
    }

    /**
     * TEST: Master Data Read Accessible by All Roles
     */
    public function test_master_data_read_accessible_by_all_roles(): void
    {
        $this->actingAs($this->superadmin)->get('/barang')->assertStatus(200);
        $this->actingAs($this->kepalaGudang)->get('/barang')->assertStatus(200);
        $this->actingAs($this->adminGudang)->get('/barang')->assertStatus(200);
        $this->actingAs($this->teknisi)->get('/barang')->assertStatus(200);
        $this->actingAs($this->viewer)->get('/barang')->assertStatus(200);
    }

    /**
     * TEST: Master Data Mutations Denied for Teknisi and Viewer
     */
    public function test_master_data_create_denied_for_teknisi_and_viewer(): void
    {
        $payload = [
            'nama_barang' => 'Test Forbidden Barang',
            'deskripsi' => 'Test Desc',
            'stok_minimum' => 5,
            'jenis_id' => 1,
            'satuan_id' => 1,
            'barcode_type' => 'BATCH',
            'status' => 'AKTIF'
        ];

        $this->actingAs($this->teknisi)->postJson('/barang', $payload)->assertStatus(403);
        $this->actingAs($this->viewer)->postJson('/barang', $payload)->assertStatus(403);
    }

    /**
     * TEST: Stock Opname Approval Allowed Only for Superadmin & Kepala Gudang
     */
    public function test_stock_opname_approval_allowed_only_for_management(): void
    {
        $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Lampu PJU Matrix'], ['jenis_barang' => 'Lampu PJU Matrix']);
        $satuan = Satuan::firstOrCreate(['satuan' => 'Unit']);
        $barang = Barang::firstOrCreate(
            ['kode_barang' => 'BRG-MATRIX-OPN'],
            [
                'nama_barang' => 'Barang Opname Matrix',
                'deskripsi' => 'Test',
                'stok' => 10,
                'stok_minimum' => 2,
                'jenis_id' => $jenis->id,
                'satuan_id' => $satuan->id,
                'barcode_type' => 'BATCH',
                'status' => 'AKTIF'
            ]
        );

        $opname = StockOpname::create([
            'kode_opname' => 'OPN-' . uniqid(),
            'barang_id' => $barang->id,
            'petugas_id' => $this->adminGudang->id,
            'tanggal_opname' => now()->toDateString(),
            'stok_sistem' => 10,
            'stok_fisik' => 8,
            'selisih' => -2,
            'status_opname' => 'SUBMITTED'
        ]);

        // Teknisi cannot approve
        $this->actingAs($this->teknisi)->postJson("/stock-opname/{$opname->id}/approve")->assertStatus(403);
        // Admin Gudang cannot approve
        $this->actingAs($this->adminGudang)->postJson("/stock-opname/{$opname->id}/approve")->assertStatus(403);
        // Viewer cannot approve
        $this->actingAs($this->viewer)->postJson("/stock-opname/{$opname->id}/approve")->assertStatus(403);

        // Kepala Gudang CAN approve
        $this->actingAs($this->kepalaGudang)->postJson("/stock-opname/{$opname->id}/approve")->assertStatus(200);
    }

    /**
     * TEST: Field Operations PJU Pemasangan
     */
    public function test_pemasangan_allowed_for_teknisi_and_admin_denied_for_viewer(): void
    {
        $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Lampu PJU Pasang'], ['jenis_barang' => 'Lampu PJU Pasang']);
        $satuan = Satuan::firstOrCreate(['satuan' => 'Unit']);
        $barang = Barang::firstOrCreate(
            ['kode_barang' => 'BRG-MATRIX-PSG'],
            [
                'nama_barang' => 'Barang Pasang Matrix',
                'deskripsi' => 'Test',
                'stok' => 5,
                'stok_minimum' => 1,
                'jenis_id' => $jenis->id,
                'satuan_id' => $satuan->id,
                'barcode_type' => 'BATCH',
                'status' => 'AKTIF'
            ]
        );

        $lokasi = LokasiPju::firstOrCreate(
            ['kode_lokasi' => 'LOK-TEST-001'],
            ['nama_lokasi' => 'Lokasi Test Matrix', 'alamat_jalan' => 'Jl. Matrix Pasang No. 1', 'status' => 'AKTIF']
        );

        $asset = PjuAsset::create([
            'kode_pju' => 'PJU-' . uniqid(),
            'nama_pju' => 'Lampu PJU Matrix 40W',
            'barang_id' => $barang->id,
            'no_seri' => 'SN-MATRIX-PASANG-001',
            'status_aset' => 'GUDANG',
        ]);

        $payload = [
            'pju_asset_id' => $asset->id,
            'lokasi_id' => $lokasi->id,
            'tanggal_pasang' => now()->toDateString(),
            'teknisi_id' => $this->teknisi->id,
        ];

        // Viewer DENIED
        $this->actingAs($this->viewer)->postJson('/pemasangan-pju', $payload)->assertStatus(403);

        // Teknisi ALLOWED
        $this->actingAs($this->teknisi)->postJson('/pemasangan-pju', $payload)->assertStatus(200);
    }

    /**
     * TEST: Inbound Barang Masuk Create Denied for Teknisi and Viewer
     */
    public function test_barang_masuk_create_allowed_for_admin_and_denied_for_teknisi_and_viewer(): void
    {
        $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Lampu PJU Inbound'], ['jenis_barang' => 'Lampu PJU Inbound']);
        $satuan = Satuan::firstOrCreate(['satuan' => 'Unit']);
        $supplier = Supplier::firstOrCreate(['nama_supplier' => 'PT Supplier Inbound Matrix'], ['supplier' => 'PT Supplier Inbound Matrix']);
        $barang = Barang::firstOrCreate(
            ['kode_barang' => 'BRG-MATRIX-INB'],
            [
                'nama_barang' => 'Barang Inbound Matrix',
                'deskripsi' => 'Test',
                'stok' => 0,
                'stok_minimum' => 1,
                'jenis_id' => $jenis->id,
                'satuan_id' => $satuan->id,
                'barcode_type' => 'BATCH',
                'status' => 'AKTIF'
            ]
        );

        $payload = [
            'tanggal_masuk' => now()->toDateString(),
            'barang_id' => $barang->id,
            'jumlah_masuk' => 10,
            'supplier_id' => $supplier->id,
            'no_dokumen' => 'DOC-INB-TEST-001'
        ];

        // Teknisi DENIED (403)
        $this->actingAs($this->teknisi)->postJson('/barang-masuk', $payload)->assertStatus(403);

        // Viewer DENIED (403)
        $this->actingAs($this->viewer)->postJson('/barang-masuk', $payload)->assertStatus(403);

        // Admin Gudang ALLOWED (200)
        $this->actingAs($this->adminGudang)->postJson('/barang-masuk', $payload)->assertStatus(200);
    }

    /**
     * TEST: Retur Vendor Complete Allowed Only for Superadmin & Kepala Gudang
     */
    public function test_retur_vendor_complete_allowed_only_for_management(): void
    {
        $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Lampu PJU Retur'], ['jenis_barang' => 'Lampu PJU Retur']);
        $satuan = Satuan::firstOrCreate(['satuan' => 'Unit']);
        $supplier = Supplier::firstOrCreate(['nama_supplier' => 'PT Supplier Retur Matrix'], ['supplier' => 'PT Supplier Retur Matrix']);
        $barang = Barang::firstOrCreate(
            ['kode_barang' => 'BRG-MATRIX-RTR'],
            [
                'nama_barang' => 'Barang Retur Matrix',
                'deskripsi' => 'Test',
                'stok' => 5,
                'stok_minimum' => 1,
                'jenis_id' => $jenis->id,
                'satuan_id' => $satuan->id,
                'barcode_type' => 'BATCH',
                'status' => 'AKTIF'
            ]
        );

        $asset = PjuAsset::create([
            'kode_pju' => 'PJU-RTR-' . uniqid(),
            'nama_pju' => 'Lampu PJU Retur Matrix',
            'barang_id' => $barang->id,
            'no_seri' => 'SN-MATRIX-RETUR-001',
            'status_aset' => 'RUSAK',
        ]);

        $retur = ReturVendor::create([
            'no_retur' => 'RTR-' . uniqid(),
            'pju_asset_id' => $asset->id,
            'supplier_id' => $supplier->id,
            'alasan_retur' => 'Driver Rusak',
            'tanggal_retur' => now()->toDateString(),
            'status' => 'PROSES_VENDOR',
            'created_by' => $this->adminGudang->id,
        ]);

        $completePayload = [
            'tanggal_kembali' => now()->toDateString(),
            'kondisi_kembali' => 'Bagus / Diganti Baru',
            'status' => 'SELESAI_GANTI'
        ];

        // Teknisi DENIED (403)
        $this->actingAs($this->teknisi)->postJson("/retur-vendor/{$retur->id}/complete", $completePayload)->assertStatus(403);

        // Admin Gudang DENIED (403)
        $this->actingAs($this->adminGudang)->postJson("/retur-vendor/{$retur->id}/complete", $completePayload)->assertStatus(403);

        // Viewer DENIED (403)
        $this->actingAs($this->viewer)->postJson("/retur-vendor/{$retur->id}/complete", $completePayload)->assertStatus(403);

        // Kepala Gudang ALLOWED (200)
        $this->actingAs($this->kepalaGudang)->postJson("/retur-vendor/{$retur->id}/complete", $completePayload)->assertStatus(200);
    }
}
