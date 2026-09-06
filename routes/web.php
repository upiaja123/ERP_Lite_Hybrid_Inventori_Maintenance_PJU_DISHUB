<?php

use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JenisController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HakAksesController;
use App\Http\Controllers\LaporanBarangKeluarController;
use App\Http\Controllers\LaporanBarangMasukController;
use App\Http\Controllers\LaporanStokController;
use App\Http\Controllers\ManajemenUserController;
use App\Http\Controllers\UbahPasswordController;
use App\Http\Controllers\MerkController;
use App\Http\Controllers\WattController;
use App\Http\Controllers\TimController;
use App\Http\Controllers\LokasiPjuController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\KelurahanController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockMutasiController;
use App\Http\Controllers\PjuAssetController;
use App\Http\Controllers\PemasanganPjuController;
use App\Http\Controllers\PencopotanPjuController;
use App\Http\Controllers\MaintenancePjuController;
use App\Http\Controllers\GaransiPjuController;
use App\Http\Controllers\ReturVendorController;
use App\Http\Controllers\LaporanMasterController;
use App\Http\Controllers\ImportController;

Route::get('/fix-password', function() {
    \App\Models\User::query()->update(['password' => \Illuminate\Support\Facades\Hash::make('password')]);
    return 'Semua password telah direset menjadi "password" dengan format hash yang benar!';
});

Route::get('/patch-db', function() {
    $statements = [
        // Patch Barangs
        "ALTER TABLE `barangs` CHANGE `keterangan` `deskripsi` TEXT NULL;" => false,
        "ALTER TABLE `barangs` ADD COLUMN `gambar` TEXT NULL AFTER `deskripsi`;" => false,
        "ALTER TABLE `barangs` ADD COLUMN `stok_minimum` DOUBLE(15,2) NOT NULL DEFAULT 0 AFTER `stok`;" => false,
        "ALTER TABLE `barangs` ADD COLUMN `supplier_id` BIGINT UNSIGNED NULL AFTER `watt_id`;" => false,
        "ALTER TABLE `barangs` ADD COLUMN `user_id` BIGINT UNSIGNED NULL AFTER `satuan_id`;" => false,

        // Patch Barang Masuks (Convert from Header/Detail to Flat)
        "ALTER TABLE `barang_masuks` CHANGE `tanggal` `tanggal_masuk` DATE NOT NULL;" => false,
        "ALTER TABLE `barang_masuks` ADD COLUMN `barang_id` BIGINT UNSIGNED NOT NULL AFTER `tanggal_masuk`;" => false,
        "ALTER TABLE `barang_masuks` ADD COLUMN `jumlah_masuk` DOUBLE(15,2) NOT NULL DEFAULT 0 AFTER `barang_id`;" => false,
        "ALTER TABLE `barang_masuks` CHANGE `created_by` `user_id` BIGINT UNSIGNED NULL;" => false,

        // Patch Barang Keluars (Convert from Header/Detail to Flat)
        "ALTER TABLE `barang_keluars` CHANGE `tanggal` `tanggal_keluar` DATE NOT NULL;" => false,
        "ALTER TABLE `barang_keluars` ADD COLUMN `barang_id` BIGINT UNSIGNED NOT NULL AFTER `tanggal_keluar`;" => false,
        "ALTER TABLE `barang_keluars` ADD COLUMN `jumlah_keluar` DOUBLE(15,2) NOT NULL DEFAULT 0 AFTER `barang_id`;" => false,
        "ALTER TABLE `barang_keluars` CHANGE `created_by` `user_id` BIGINT UNSIGNED NULL;" => false,
    ];

    $log = [];
    foreach ($statements as $query => $executed) {
        try {
            \Illuminate\Support\Facades\DB::statement($query);
            $log[] = "SUCCESS: " . $query;
        } catch (\Exception $e) {
            $log[] = "SKIPPED/ERROR (mungkin kolom sudah ada): " . $query . " | Error: " . $e->getMessage();
        }
    }
    
    return response()->json([
        'message' => 'Database patching completed.',
        'logs' => $log
    ]);
});

Route::middleware('auth')->group(function () {

    Route::group(['middleware' => 'checkRole:superadmin'], function () {
        Route::get('/data-pengguna/get-data', [ManajemenUserController::class, 'getDataPengguna']);
        Route::get('/api/role/', [ManajemenUserController::class, 'getRole']);
        Route::resource('/data-pengguna', ManajemenUserController::class);

        Route::get('/hak-akses/get-data', [HakAksesController::class, 'getDataRole']);
        Route::resource('/hak-akses', HakAksesController::class);
    });

    Route::group(['middleware' => 'checkRole:superadmin,kepala gudang'], function () {
        Route::resource('/aktivitas-user', ActivityLogController::class);

        // EXCEL MIGRATION & IMPORT PIPELINE (CLASS 08)
        Route::get('/import', [ImportController::class, 'index'])->name('import.index');
        Route::post('/import/preview', [ImportController::class, 'previewAndValidate'])->name('import.preview');
        Route::post('/import/approve', [ImportController::class, 'approveAndImport'])->name('import.approve');
    });

    // GENERAL AUTHENTICATED ACCESS (ALL ROLES)
    Route::group(['middleware' => 'checkRole:kepala gudang,superadmin,admin gudang,teknisi,viewer'], function () {
        Route::resource('/dashboard', DashboardController::class);
        Route::get('/', [DashboardController::class, 'index']);

        Route::get('/ubah-password', [UbahPasswordController::class, 'index']);
        Route::post('/ubah-password', [UbahPasswordController::class, 'changePassword']);
    });

    // LAPORAN & REPORTING (SUPERADMIN, KEPALA GUDANG, ADMIN GUDANG, VIEWER)
    Route::group(['middleware' => 'checkRole:superadmin,kepala gudang,admin gudang,viewer'], function () {
        Route::get('/laporan/generate', [LaporanMasterController::class, 'generateReport'])->name('laporan.generate');

        Route::get('/laporan-stok/get-data', [LaporanStokController::class, 'getData']);
        Route::get('/laporan-stok/print-stok', [LaporanStokController::class, 'printStok']);
        Route::get('/api/satuan/', [LaporanStokController::class, 'getSatuan']);
        Route::resource('/laporan-stok', LaporanStokController::class);

        Route::get('/laporan-barang-masuk/get-data', [LaporanBarangMasukController::class, 'getData']);
        Route::get('/laporan-barang-masuk/print-barang-masuk', [LaporanBarangMasukController::class, 'printBarangMasuk']);
        Route::get('/api/supplier/', [LaporanBarangMasukController::class, 'getSupplier']);
        Route::resource('/laporan-barang-masuk', LaporanBarangMasukController::class);

        Route::get('/laporan-barang-keluar/get-data', [LaporanBarangKeluarController::class, 'getData']);
        Route::get('/laporan-barang-keluar/print-barang-keluar', [LaporanBarangKeluarController::class, 'printBarangKeluar']);
        Route::get('/api/customer/', [LaporanBarangKeluarController::class, 'getCustomer']);
        Route::resource('/laporan-barang-keluar', LaporanBarangKeluarController::class);
    });

    // MASTER DATA & ASSET READ (ALL ROLES)
    Route::group(['middleware' => 'checkRole:superadmin,admin gudang,kepala gudang,teknisi,viewer'], function () {
        Route::get('/barang/cetak-pdf/{id}', [BarangController::class, 'cetakPdf'])->name('barang.cetak.pdf');
        Route::get('/barang/get-data', [BarangController::class, 'getDataBarang']);
        Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
        Route::get('/barang/{barang}', [BarangController::class, 'show'])->name('barang.show')->where(['barang' => '[0-9]+']);

        Route::get('/jenis-barang/get-data', [JenisController::class, 'getDataJenisBarang']);
        Route::get('/jenis-barang', [JenisController::class, 'index'])->name('jenis-barang.index');

        Route::get('/satuan-barang/get-data', [SatuanController::class, 'getDataSatuanBarang']);
        Route::get('/satuan-barang', [SatuanController::class, 'index'])->name('satuan-barang.index');

        Route::get('/merk/get-data', [MerkController::class, 'getData']);
        Route::get('/merk', [MerkController::class, 'index'])->name('merk.index');

        Route::get('/watt/get-data', [WattController::class, 'getData']);
        Route::get('/watt', [WattController::class, 'index'])->name('watt.index');

        Route::get('/tim/get-data', [TimController::class, 'getData']);
        Route::get('/tim', [TimController::class, 'index'])->name('tim.index');

        Route::get('/supplier/get-data', [SupplierController::class, 'getDataSupplier']);
        Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');

        Route::get('/customer/get-data', [CustomerController::class, 'getDataCustomer']);
        Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');

        Route::get('/lokasi-pju/get-data', [LokasiPjuController::class, 'getData']);
        Route::get('/lokasi-pju', [LokasiPjuController::class, 'index'])->name('lokasi-pju.index');

        Route::get('/kecamatan/get-data', [KecamatanController::class, 'getData']);
        Route::get('/kecamatan', [KecamatanController::class, 'index'])->name('kecamatan.index');

        Route::get('/kelurahan/get-data', [KelurahanController::class, 'getData']);
        Route::get('/kelurahan', [KelurahanController::class, 'index'])->name('kelurahan.index');

        // PJU Asset & Operations Read
        Route::get('/pju-asset/get-data', [PjuAssetController::class, 'getData']);
        Route::get('/pju-asset', [PjuAssetController::class, 'index'])->name('pju-asset.index');
        Route::get('/pju-asset/{pju_asset}', [PjuAssetController::class, 'show'])->name('pju-asset.show')->where(['pju_asset' => '[0-9]+']);

        Route::get('/pemasangan-pju/get-data', [PemasanganPjuController::class, 'getData']);
        Route::get('/pemasangan-pju', [PemasanganPjuController::class, 'index'])->name('pemasangan-pju.index');

        Route::get('/pencopotan-pju/get-data', [PencopotanPjuController::class, 'getData']);
        Route::get('/pencopotan-pju', [PencopotanPjuController::class, 'index'])->name('pencopotan-pju.index');

        Route::get('/maintenance-pju/get-data', [MaintenancePjuController::class, 'getData']);
        Route::get('/maintenance-pju', [MaintenancePjuController::class, 'index'])->name('maintenance-pju.index');

        Route::get('/garansi-pju/get-data', [GaransiPjuController::class, 'getData']);
        Route::get('/garansi-pju', [GaransiPjuController::class, 'index'])->name('garansi-pju.index');
    });

    // MASTER DATA WRITE/MUTATIONS (SUPERADMIN, ADMIN GUDANG, KEPALA GUDANG)
    Route::group(['middleware' => 'checkRole:superadmin,admin gudang,kepala gudang'], function () {
        Route::get('/barang/create', [BarangController::class, 'create'])->name('barang.create');
        Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
        Route::get('/barang/{barang}/edit', [BarangController::class, 'edit'])->name('barang.edit')->where(['barang' => '[0-9]+']);
        Route::match(['put', 'patch'], '/barang/{barang}', [BarangController::class, 'update'])->name('barang.update')->where(['barang' => '[0-9]+']);
        Route::delete('/barang/{barang}', [BarangController::class, 'destroy'])->name('barang.destroy')->where(['barang' => '[0-9]+']);

        Route::post('/jenis-barang', [JenisController::class, 'store'])->name('jenis-barang.store');
        Route::delete('/jenis-barang/{jeni}', [JenisController::class, 'destroy'])->name('jenis-barang.destroy');

        Route::post('/satuan-barang', [SatuanController::class, 'store'])->name('satuan-barang.store');
        Route::delete('/satuan-barang/{satuan_barang}', [SatuanController::class, 'destroy'])->name('satuan-barang.destroy');

        Route::post('/merk', [MerkController::class, 'store'])->name('merk.store');
        Route::delete('/merk/{merk}', [MerkController::class, 'destroy'])->name('merk.destroy');

        Route::post('/watt', [WattController::class, 'store'])->name('watt.store');
        Route::delete('/watt/{watt}', [WattController::class, 'destroy'])->name('watt.destroy');

        Route::post('/tim', [TimController::class, 'store'])->name('tim.store');
        Route::delete('/tim/{tim}', [TimController::class, 'destroy'])->name('tim.destroy');

        Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
        Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.destroy');

        Route::post('/customer', [CustomerController::class, 'store'])->name('customer.store');
        Route::delete('/customer/{customer}', [CustomerController::class, 'destroy'])->name('customer.destroy');

        Route::post('/lokasi-pju', [LokasiPjuController::class, 'store'])->name('lokasi-pju.store');
        Route::delete('/lokasi-pju/{lokasi_pju}', [LokasiPjuController::class, 'destroy'])->name('lokasi-pju.destroy');

        Route::post('/kecamatan', [KecamatanController::class, 'store'])->name('kecamatan.store');
        Route::delete('/kecamatan/{kecamatan}', [KecamatanController::class, 'destroy'])->name('kecamatan.destroy');

        Route::post('/kelurahan', [KelurahanController::class, 'store'])->name('kelurahan.store');
        Route::delete('/kelurahan/{kelurahan}', [KelurahanController::class, 'destroy'])->name('kelurahan.destroy');

        Route::post('/pju-asset', [PjuAssetController::class, 'store'])->name('pju-asset.store');
        Route::match(['put', 'patch'], '/pju-asset/{pju_asset}', [PjuAssetController::class, 'update'])->name('pju-asset.update');
        Route::delete('/pju-asset/{pju_asset}', [PjuAssetController::class, 'destroy'])->name('pju-asset.destroy');
    });

    // TRANSAKSI & INVENTORY READ (SUPERADMIN, ADMIN GUDANG, KEPALA GUDANG, VIEWER)
    Route::group(['middleware' => 'checkRole:superadmin,admin gudang,kepala gudang,viewer'], function () {
        Route::get('/api/barang-masuk', [BarangMasukController::class, 'getAutoCompleteData']);
        Route::get('/barang-masuk/get-data', [BarangMasukController::class, 'getDataBarangMasuk']);
        Route::get('/api/satuan-masuk', [BarangMasukController::class, 'getSatuan']);
        Route::get('/barang-masuk/get-barang-detail', [BarangMasukController::class, 'getBarangDetail']);
        Route::get('/barang-masuk', [BarangMasukController::class, 'index'])->name('barang-masuk.index');

        Route::get('/api/barang-keluar/', [BarangKeluarController::class, 'getAutoCompleteData']);
        Route::get('/barang-keluar/get-data', [BarangKeluarController::class, 'getDataBarangKeluar']);
        Route::get('/api/satuan/', [BarangKeluarController::class, 'getSatuan']);
        Route::get('/barang-keluar', [BarangKeluarController::class, 'index'])->name('barang-keluar.index');

        Route::get('/stock-opname/get-data', [StockOpnameController::class, 'getData']);
        Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('stock-opname.index');

        Route::get('/stock-mutasi/get-data', [StockMutasiController::class, 'getData']);
        Route::get('/stock-mutasi', [StockMutasiController::class, 'index'])->name('stock-mutasi.index');

        Route::get('/retur-vendor/get-data', [ReturVendorController::class, 'getData']);
        Route::get('/retur-vendor', [ReturVendorController::class, 'index'])->name('retur-vendor.index');
    });

    // TRANSAKSI & INVENTORY WRITE (SUPERADMIN, ADMIN GUDANG)
    Route::group(['middleware' => 'checkRole:superadmin,admin gudang'], function () {
        Route::post('/barang-masuk', [BarangMasukController::class, 'store'])->name('barang-masuk.store');
        Route::get('/barang-keluar/create', [BarangKeluarController::class, 'create'])->name('barang-keluar.create');
        Route::post('/barang-keluar', [BarangKeluarController::class, 'store'])->name('barang-keluar.store');
        Route::post('/stock-opname', [StockOpnameController::class, 'store'])->name('stock-opname.store');
        Route::post('/stock-mutasi', [StockMutasiController::class, 'store'])->name('stock-mutasi.store');
        Route::post('/retur-vendor', [ReturVendorController::class, 'store'])->name('retur-vendor.store');
    });

    // TRANSAKSI VOID & DELETE (SUPERADMIN, ADMIN GUDANG, KEPALA GUDANG)
    Route::group(['middleware' => 'checkRole:superadmin,admin gudang,kepala gudang'], function () {
        Route::delete('/barang-masuk/{barang_masuk}', [BarangMasukController::class, 'destroy'])->name('barang-masuk.destroy');
        Route::delete('/barang-keluar/{barang_keluar}', [BarangKeluarController::class, 'destroy'])->name('barang-keluar.destroy');
        Route::delete('/stock-opname/{stock_opname}', [StockOpnameController::class, 'destroy'])->name('stock-opname.destroy');
    });

    // APPROVALS WORKFLOW (SUPERADMIN, KEPALA GUDANG ONLY)
    Route::group(['middleware' => 'checkRole:superadmin,kepala gudang'], function () {
        Route::post('/stock-opname/{stockOpname}/approve', [StockOpnameController::class, 'approve'])->name('stock-opname.approve');
        Route::post('/retur-vendor/{returVendor}/complete', [ReturVendorController::class, 'complete'])->name('retur-vendor.complete');
    });

    // FIELD OPERATIONS WRITE (SUPERADMIN, ADMIN GUDANG, TEKNISI)
    Route::group(['middleware' => 'checkRole:superadmin,admin gudang,teknisi'], function () {
        Route::post('/pemasangan-pju', [PemasanganPjuController::class, 'store'])->name('pemasangan-pju.store');
        Route::post('/pencopotan-pju', [PencopotanPjuController::class, 'store'])->name('pencopotan-pju.store');
    });

    // MAINTENANCE WRITE & COMPLETE (SUPERADMIN, KEPALA GUDANG, TEKNISI)
    Route::group(['middleware' => 'checkRole:superadmin,kepala gudang,teknisi'], function () {
        Route::post('/maintenance-pju', [MaintenancePjuController::class, 'store'])->name('maintenance-pju.store');
        Route::post('/maintenance-pju/{maintenancePju}/complete', [MaintenancePjuController::class, 'complete'])->name('maintenance-pju.complete');
    });

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
