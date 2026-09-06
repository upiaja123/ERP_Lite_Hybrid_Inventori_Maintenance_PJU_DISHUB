<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Jenis;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\LokasiPju;
use App\Models\Merk;
use App\Models\PjuAsset;
use App\Models\PencopotanPju;
use App\Models\PemasanganPju;
use App\Models\Satuan;
use App\Models\Supplier;
use App\Models\Tim;
use App\Models\User;
use App\Models\Watt;
use App\Services\InventoryService;
use Tests\TestCase;

class ExcelTraceabilityTest extends TestCase
{
    /**
     * TEST: Representation of 'form lampu panasonic 30 Gudang 6.xlsx'
     * Fields: Year, Brand (Panasonic), Watt (30W), Location/Warehouse 6, Quantity, Unit, Minimum Stock, Ledger Inbound
     */
    public function test_form_lampu_panasonic_30_gudang_6_excel_representation(): void
    {
        $adminRole = \App\Models\Role::firstOrCreate(['role' => 'admin gudang']);
        $admin = User::firstOrCreate(
            ['email' => 'excel_admin@dishub.test'],
            ['name' => 'Admin Excel', 'password' => bcrypt('password'), 'role_id' => $adminRole->id, 'status' => 'ACTIVE']
        );

        $merk = Merk::firstOrCreate(['nama_merk' => 'Panasonic'], ['merk' => 'Panasonic']);
        $watt = Watt::firstOrCreate(['nilai_watt' => 30], ['satuan_daya' => 'Watt']);
        $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Lampu LED PJU Panasonic'], ['jenis_barang' => 'Lampu LED PJU Panasonic']);
        $satuan = Satuan::firstOrCreate(['satuan' => 'Unit']);
        $supplier = Supplier::firstOrCreate(['nama_supplier' => 'PT Panasonic Gobel Indonesia'], ['supplier' => 'PT Panasonic Gobel Indonesia']);

        // 1. Create item representing Panasonic 30W from Warehouse 6
        $barang = Barang::create([
            'kode_barang'        => 'BRG-PANA-30W-' . uniqid(),
            'nama_barang'        => 'Lampu LED PJU Panasonic 30 Watt - Gudang 6',
            'deskripsi'          => 'Data Source: form lampu panasonic 30 Gudang 6.xlsx',
            'stok_minimum'       => 10,
            'stok'               => 0,
            'jenis_id'           => $jenis->id,
            'satuan_id'          => $satuan->id,
            'merk_id'            => $merk->id,
            'watt_id'            => $watt->id,
            'supplier_id'        => $supplier->id,
            'barcode_type'       => 'BATCH',
            'barcode_value'      => 'PAN-30W-GDG6-' . uniqid(),
            'tanggal_pengadaan'  => '2024-01-15',
            'masa_garansi_bulan' => 24,
            'status'             => 'AKTIF',
            'user_id'            => $admin->id
        ]);

        $this->assertDatabaseHas('barangs', [
            'id' => $barang->id,
            'nama_barang' => 'Lampu LED PJU Panasonic 30 Watt - Gudang 6',
            'merk_id' => $merk->id,
            'watt_id' => $watt->id
        ]);

        // 2. Inbound transaction from Excel source
        $inventoryService = app(InventoryService::class);
        $inbound = $inventoryService->recordInbound([
            'tanggal_masuk'  => '2024-02-01',
            'barang_id'      => $barang->id,
            'jumlah_masuk'   => 50,
            'supplier_id'    => $supplier->id,
            'no_dokumen'     => 'MIGRATION-EXCEL-PANASONIC-30W',
            'kode_transaksi' => 'TRX-IN-PANA-' . uniqid(),
        ], $admin->id);

        $barang->refresh();
        $this->assertEquals(50, $barang->stok);

        // 3. Verify ledger entry
        $this->assertDatabaseHas('stock_ledgers', [
            'barang_id' => $barang->id,
            'transaction_type' => 'IN',
            'quantity_after' => 50
        ]);
    }

    /**
     * TEST: Representation of 'Data Copotan Keseluruhan.xlsx'
     * Fields: Year, Barcode/Serial Number, Lamp Type, Watt, Quantity, Location/Street, Kelurahan, Team, Tanggal Copot, Tanggal Pasang, Vendor Status
     */
    public function test_data_copotan_keseluruhan_excel_representation(): void
    {
        $teknisiRole = \App\Models\Role::firstOrCreate(['role' => 'teknisi']);
        $teknisi = User::firstOrCreate(
            ['email' => 'excel_teknisi@dishub.test'],
            ['name' => 'Teknisi Copotan Excel', 'password' => bcrypt('password'), 'role_id' => $teknisiRole->id, 'status' => 'ACTIVE']
        );

        $kecamatan = Kecamatan::firstOrCreate(['nama_kecamatan' => 'Kecamatan Banyumanik']);
        $kelurahan = Kelurahan::firstOrCreate(
            ['nama_kelurahan' => 'Kelurahan Srondol Kulon'],
            ['kecamatan_id' => $kecamatan->id]
        );

        $tim = Tim::firstOrCreate(
            ['kode_tim' => 'TIM-PJU-01'],
            ['nama_tim' => 'Tim PJU Reaksi Cepat 01', 'keterangan' => 'Tim Copotan Lapangan']
        );

        $lokasi = LokasiPju::firstOrCreate(
            ['kode_lokasi' => 'LOK-COPOT-' . uniqid()],
            [
                'nama_lokasi' => 'Titik Tiang Jl. Setiabudi No. 120',
                'alamat_jalan' => 'Jl. Setiabudi No. 120',
                'kecamatan_id' => $kecamatan->id,
                'kelurahan_id' => $kelurahan->id,
                'pole_number' => 'TIANG-PJU-042',
                'tim_operasional' => $tim->nama_tim,
                'status' => 'AKTIF'
            ]
        );

        $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Lampu LED 90W'], ['jenis_barang' => 'Lampu LED 90W']);
        $satuan = Satuan::firstOrCreate(['satuan' => 'Unit']);
        $barang = Barang::create([
            'kode_barang'   => 'BRG-90W-' . uniqid(),
            'nama_barang'   => 'Lampu LED PJU 90 Watt Copotan',
            'deskripsi'     => 'Source Data Copotan Keseluruhan.xlsx',
            'stok'          => 10,
            'stok_minimum'  => 2,
            'jenis_id'      => $jenis->id,
            'satuan_id'     => $satuan->id,
            'barcode_type'  => 'INDIVIDUAL',
            'barcode_value' => 'BC-COPOT-90W-' . uniqid(),
            'status'        => 'AKTIF'
        ]);

        // 1. Asset PJU individual with Serial Number & Year
        $asset = PjuAsset::create([
            'kode_pju'        => 'PJU-COPOT-' . uniqid(),
            'nama_pju'        => 'Lampu PJU 90W Tiang 042',
            'barang_id'       => $barang->id,
            'lokasi_id'       => $lokasi->id,
            'jenis_lampu'     => 'LED Multi-Chip',
            'daya_watt'       => 90,
            'merk'            => 'Philips',
            'no_seri'         => 'SN-COPOT-2023-0421',
            'tahun_pengadaan' => 2023,
            'status_aset'     => 'TERPASANG',
        ]);

        // 2. Uninstallation / Pencopotan (Copot) from Excel row
        $copot = PencopotanPju::create([
            'no_pencopotan'  => 'COPOT-2024-' . uniqid(),
            'pju_asset_id'   => $asset->id,
            'lokasi_id'      => $lokasi->id,
            'tanggal_copot'  => '2024-03-10',
            'teknisi_id'     => $teknisi->id,
            'alasan'         => 'RUSAK_BERAT',
            'kondisi_barang' => 'Kaca pecah, LED mati total akibat tersambar petir',
            'catatan'        => 'Sesuai catatan baris Excel Data Copotan',
            'created_by'     => $teknisi->id
        ]);

        $asset->update(['status_aset' => 'RUSAK', 'lokasi_id' => null]);

        $this->assertDatabaseHas('pencopotan_pjus', [
            'id' => $copot->id,
            'pju_asset_id' => $asset->id,
            'alasan' => 'RUSAK_BERAT'
        ]);

        $this->assertEquals('RUSAK', $asset->fresh()->status_aset);
    }
}
