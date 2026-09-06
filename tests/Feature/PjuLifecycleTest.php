<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Jenis;
use App\Models\Satuan;
use App\Models\Supplier;
use App\Models\LokasiPju;
use App\Models\PjuAsset;
use App\Models\User;
use App\Models\Role;
use App\Services\InventoryService;
use App\Services\PjuLifecycleService;
use App\Services\ApprovalWorkflowService;
use App\Services\ItemCodeGeneratorService;
use Tests\TestCase;

class PjuLifecycleTest extends TestCase
{
    protected PjuLifecycleService $lifecycleService;
    protected InventoryService $inventoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $codeGenerator = new ItemCodeGeneratorService();
        $approvalService = new ApprovalWorkflowService();
        $this->inventoryService = new InventoryService($approvalService, $codeGenerator);
        $this->lifecycleService = new PjuLifecycleService($codeGenerator);
    }

    /**
     * FULL END-TO-END PJU LIFECYCLE TEST:
     * Barang Masuk -> Pasang -> Rusak -> Copot -> Retur Vendor -> Kembali ke Gudang -> Pasang Kembali
     */
    public function test_complete_pju_asset_lifecycle(): void
    {
        // 0. Base Setup
        $role = Role::firstOrCreate(['role' => 'admin gudang']);
        $user = User::firstOrCreate(
            ['email' => 'teknisi_lifecycle@example.com'],
            ['name' => 'Teknisi Lifecycle', 'password' => bcrypt('password'), 'role_id' => $role->id, 'status' => 'ACTIVE']
        );

        $supplier = Supplier::firstOrCreate(['nama_supplier' => 'PT Vendor Garansi PJU'], ['alamat' => 'Jl. Industri 1']);
        $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Armatur Lampu']);
        $satuan = Satuan::firstOrCreate(['satuan' => 'Unit']);

        $barang = Barang::create([
            'kode_barang'   => 'PRPTY-LIFECYCLE-001',
            'nama_barang'   => 'Lampu PJU Smart LED 120W',
            'deskripsi'     => 'Aset PJU Smart LED',
            'stok_minimum'  => 1,
            'jenis_id'      => $jenis->id,
            'satuan_id'     => $satuan->id,
            'user_id'       => $user->id,
            'barcode_type'  => 'INDIVIDUAL',
            'barcode_value' => 'SN-LIFECYCLE-999',
            'stok'          => 0,
        ]);

        // 1. Barang Masuk (Gudang)
        $this->inventoryService->recordInbound([
            'tanggal_masuk'  => date('Y-m-d'),
            'barang_id'      => $barang->id,
            'jumlah_masuk'   => 1,
            'supplier_id'    => $supplier->id,
            'kode_transaksi' => 'BM-LIFECYCLE-01',
            'status'         => 'POSTED',
        ], $user->id);

        $asset = PjuAsset::create([
            'kode_pju'        => 'PJU-LIFECYCLE-001',
            'nama_pju'        => 'Aset Lampu PJU SN-999',
            'barang_id'       => $barang->id,
            'no_seri'         => 'SN-LIFECYCLE-999',
            'supplier_id'     => $supplier->id,
            'status_aset'     => 'GUDANG',
            'created_by'      => $user->id,
        ]);

        $this->assertEquals('GUDANG', $asset->status_aset, 'Asset initial status must be GUDANG');

        // 2. Pasang di Lokasi A
        $lokasiA = LokasiPju::create([
            'kode_lokasi'  => 'LOK-A-001',
            'nama_lokasi'  => 'Titik A Jl. Sudirman',
            'alamat_jalan' => 'Jl. Sudirman Km 5',
            'pole_number'  => 'T-A01',
            'status'       => 'AKTIF',
        ]);

        $pemasangan = $this->lifecycleService->installAsset([
            'pju_asset_id'   => $asset->id,
            'lokasi_id'      => $lokasiA->id,
            'tanggal_pasang' => date('Y-m-d'),
            'teknisi_id'     => $user->id,
        ], $user->id);

        $asset->refresh();
        $this->assertEquals('TERPASANG', $asset->status_aset);
        $this->assertEquals($lokasiA->id, $asset->lokasi_id);

        // 3. Rusak (Laporan Kerusakan)
        $kerusakan = $this->lifecycleService->reportDamage([
            'pju_asset_id'        => $asset->id,
            'tanggal_laporan'     => date('Y-m-d'),
            'jenis_kerusakan'     => 'Mati Total',
            'deskripsi_kerusakan' => 'Korsleting petir',
        ], $user->id);

        $asset->refresh();
        $this->assertEquals('RUSAK', $asset->status_aset);

        // 4. Maintenance Work Order
        $maintenance = $this->lifecycleService->createWorkOrder([
            'pju_asset_id' => $asset->id,
            'kerusakan_id' => $kerusakan->id,
            'teknisi_id'   => $user->id,
        ], $user->id);

        $asset->refresh();
        $this->assertEquals('MAINTENANCE', $asset->status_aset);

        // 5. Copot (Pencopotan dari Lokasi A untuk Retur)
        $pencopotan = $this->lifecycleService->uninstallAsset([
            'pju_asset_id'   => $asset->id,
            'alasan'         => 'RETUR_VENDOR',
            'kondisi_barang' => 'RUSAK_TOTAL',
            'teknisi_id'     => $user->id,
            'force'          => true,
        ], $user->id);

        $asset->refresh();
        $this->assertEquals('RETUR', $asset->status_aset);

        // 6. Retur Vendor (Proses ke Supplier)
        $retur = $this->lifecycleService->processVendorReturn([
            'pju_asset_id' => $asset->id,
            'supplier_id'  => $supplier->id,
            'alasan_retur' => 'Klaim Garansi Lampu Mati Total',
        ], $user->id);

        // 7. Kembali dari Vendor (Diperbaiki / Diganti baru -> Masuk GUDANG)
        $completedRetur = $this->lifecycleService->completeVendorReturn($retur, [
            'tanggal_kembali' => date('Y-m-d'),
            'kondisi_kembali' => 'BARANG_BARU_PENGGANTI',
            'status'          => 'SELESAI_GANTI',
        ], $user->id);

        $asset->refresh();
        $this->assertEquals('GUDANG', $asset->status_aset);
        $this->assertNull($asset->lokasi_id, 'Asset returned to Gudang must have null lokasi_id');

        // 8. Pasang Kembali di Lokasi B
        $lokasiB = LokasiPju::create([
            'kode_lokasi'  => 'LOK-B-002',
            'nama_lokasi'  => 'Titik B Jl. Gatot Subroto',
            'alamat_jalan' => 'Jl. Gatot Subroto Km 10',
            'pole_number'  => 'T-B02',
            'status'       => 'AKTIF',
        ]);

        $reInstall = $this->lifecycleService->installAsset([
            'pju_asset_id'   => $asset->id,
            'lokasi_id'      => $lokasiB->id,
            'tanggal_pasang' => date('Y-m-d'),
            'teknisi_id'     => $user->id,
        ], $user->id);

        $asset->refresh();
        $this->assertEquals('TERPASANG', $asset->status_aset);
        $this->assertEquals($lokasiB->id, $asset->lokasi_id);

        // 9. Full Traceability Audit Verification
        $history = $this->lifecycleService->getAssetTraceabilityHistory($asset->id);

        $this->assertCount(2, $history['pemasangan'], 'Should have 2 installation records');
        $this->assertCount(1, $history['kerusakan'], 'Should have 1 damage report');
        $this->assertCount(1, $history['maintenance'], 'Should have 1 maintenance record');
        $this->assertCount(1, $history['pencopotan'], 'Should have 1 uninstallation record');
        $this->assertCount(1, $history['retur'], 'Should have 1 vendor return record');
    }
}
