<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Kecamatan (Master Wilayah)
        Schema::create('kecamatans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kecamatan')->nullable()->unique();
            $table->string('nama_kecamatan')->index();
            $table->timestamps();
        });

        // 2. Tabel Kelurahan (Master Wilayah)
        Schema::create('kelurahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->onDelete('cascade');
            $table->string('kode_kelurahan')->nullable()->unique();
            $table->string('nama_kelurahan')->index();
            $table->timestamps();
        });

        // 3. Tabel Lokasi PJU (Dynamic Location)
        Schema::create('lokasi_pjus', function (Blueprint $table) {
            $table->id();
            $table->string('kode_lokasi')->unique();
            $table->string('nama_lokasi')->nullable(); // Nama titik / Landmark
            $table->text('alamat_jalan');
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->onDelete('set null');
            $table->foreignId('kelurahan_id')->nullable()->constrained('kelurahans')->onDelete('set null');
            $table->string('pole_number')->nullable()->index(); // Nomor Tiang (Nullable)
            $table->decimal('latitude', 10, 8)->nullable();   // GPS Optional (Nullable)
            $table->decimal('longitude', 11, 8)->nullable();  // GPS Optional (Nullable)
            $table->text('google_maps_url')->nullable();      // Helper Link Google Maps
            $table->string('tim_operasional')->nullable();    // Tim Dishub / Subkon
            $table->enum('status', ['AKTIF', 'NONAKTIF', 'PERBAIKAN'])->default('AKTIF');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('kelurahan_id');
        });

        // 4. Tabel Aset PJU (Individual Asset Lifecycle Tracking)
        Schema::create('pju_assets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pju')->unique();
            $table->string('nama_pju');
            $table->foreignId('barang_id')->nullable()->constrained('barangs')->onDelete('set null');
            $table->foreignId('lokasi_id')->nullable()->constrained('lokasi_pjus')->onDelete('set null');
            $table->string('jenis_lampu')->nullable();
            $table->integer('daya_watt')->nullable();
            $table->string('merk')->nullable();
            $table->string('tipe_spesifikasi')->nullable();
            $table->string('no_seri')->nullable()->index();
            $table->year('tahun_pengadaan')->nullable();
            $table->string('no_kontrak')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->enum('status_aset', ['GUDANG', 'TERPASANG', 'RUSAK', 'MAINTENANCE', 'RETUR', 'NONAKTIF'])->default('GUDANG');
            $table->date('garansi_mulai')->nullable();
            $table->date('garansi_berakhir')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pju_assets');
        Schema::dropIfExists('lokasi_pjus');
        Schema::dropIfExists('kelurahans');
        Schema::dropIfExists('kecamatans');
    }
};
