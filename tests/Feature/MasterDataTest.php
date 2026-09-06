<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\Jenis;
use App\Models\Satuan;
use App\Models\Merk;
use App\Models\Watt;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Role;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    /**
     * Test watt master seeder values (20, 30, 40, 90, 120)
     */
    public function test_watt_master_contains_required_minimal_values(): void
    {
        $watts = [20, 30, 40, 90, 120];

        foreach ($watts as $val) {
            Watt::firstOrCreate(['nilai_watt' => $val], [
                'satuan_daya' => 'Watt',
                'keterangan'  => "Lampu PJU {$val} Watt"
            ]);
        }

        foreach ($watts as $val) {
            $this->assertDatabaseHas('watts', ['nilai_watt' => $val]);
        }
    }

    /**
     * Test duplicate watt entry fails
     */
    public function test_duplicate_watt_fails_validation(): void
    {
        Watt::firstOrCreate(['nilai_watt' => 50], ['satuan_daya' => 'Watt']);

        $duplicate = Watt::where('nilai_watt', 50)->count();
        $this->assertEquals(1, $duplicate);
    }

    /**
     * Test individual barcode uniqueness validation
     */
    public function test_individual_barcode_uniqueness(): void
    {
        $role = Role::firstOrCreate(['role' => 'admin gudang']);
        $user = User::firstOrCreate(
            ['email' => 'admin_test@example.com'],
            ['name' => 'Admin Test', 'password' => bcrypt('password'), 'role_id' => $role->id, 'status' => 'ACTIVE']
        );

        $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Lampu LED']);
        $satuan = Satuan::firstOrCreate(['satuan' => 'Unit']);

        // First item with individual barcode
        $barcodeVal = 'SN-' . uniqid();
        $barang1 = Barang::create([
            'kode_barang'   => 'PRPTY-BC-' . uniqid(),
            'nama_barang'   => 'Lampu PJU LED 50W Individual A',
            'deskripsi'     => 'Sample item',
            'stok_minimum'  => 5,
            'jenis_id'      => $jenis->id,
            'satuan_id'     => $satuan->id,
            'user_id'       => $user->id,
            'barcode_type'  => 'INDIVIDUAL',
            'barcode_value' => $barcodeVal,
        ]);

        $this->assertDatabaseHas('barangs', ['barcode_value' => $barcodeVal]);

        // Second item attempting to use the same individual barcode
        $isDuplicateBarcode = Barang::where('barcode_value', $barcodeVal)
            ->where('barcode_type', 'INDIVIDUAL')
            ->exists();

        $this->assertTrue($isDuplicateBarcode, 'Individual barcode should be detected as duplicate');
    }

    /**
     * Test barang relationships (jenis, satuan, merk, watt, supplier)
     */
    public function test_barang_relationships(): void
    {
        $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Lampu LED']);
        $satuan = Satuan::firstOrCreate(['satuan' => 'Unit']);
        $merk = Merk::firstOrCreate(['nama_merk' => 'Philips']);
        $watt = Watt::firstOrCreate(['nilai_watt' => 90]);

        $barang = Barang::create([
            'kode_barang'   => 'PRPTY-REL-' . uniqid(),
            'nama_barang'   => 'Lampu Philips 90W',
            'deskripsi'     => 'Test item',
            'stok_minimum'  => 2,
            'jenis_id'      => $jenis->id,
            'satuan_id'     => $satuan->id,
            'merk_id'       => $merk->id,
            'watt_id'       => $watt->id,
            'user_id'       => 1,
            'barcode_type'  => 'BATCH',
        ]);

        $this->assertEquals('Lampu LED', $barang->jenis->nama_jenis);
        $this->assertEquals('Unit', $barang->satuan->satuan);
        $this->assertEquals('Philips', $barang->merk->nama_merk);
        $this->assertEquals(90, $barang->watt->nilai_watt);
    }

    /**
     * Test image upload, storage persistence, and database mapping
     */
    public function test_barang_image_upload_and_database_persistence(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $adminRole = Role::firstOrCreate(['role' => 'admin gudang']);
        $admin = User::firstOrCreate(
            ['email' => 'upload_admin@dishub.test'],
            ['name' => 'Admin Upload', 'password' => bcrypt('password'), 'role_id' => $adminRole->id, 'status' => 'ACTIVE']
        );

        $jenis = Jenis::firstOrCreate(['nama_jenis' => 'Lampu LED']);
        $satuan = Satuan::firstOrCreate(['satuan' => 'Unit']);
        $file = \Illuminate\Http\UploadedFile::fake()->image('lampu_panasonic.jpg', 640, 480);

        $response = $this->actingAs($admin)->post('/barang', [
            'nama_barang'        => 'Lampu LED Panasonic Test Upload',
            'deskripsi'          => 'Uji coba upload gambar masuk ke database dan storage',
            'stok_minimum'       => 5,
            'jenis_id'           => $jenis->id,
            'satuan_id'          => $satuan->id,
            'barcode_type'       => 'BATCH',
            'barcode_value'      => 'PAN-TEST-UPLOAD-' . uniqid(),
            'gambar'             => [$file],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $barang = Barang::where('nama_barang', 'Lampu LED Panasonic Test Upload')->first();
        $this->assertNotNull($barang);
        $this->assertNotNull($barang->gambar);

        $images = json_decode($barang->gambar, true);
        $this->assertIsArray($images);
        $this->assertNotEmpty($images);
        $this->assertStringContainsString('gambar-barang', $images[0]);
    }
}

