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
        // 1. Master Merk (Brand)
        if (!Schema::hasTable('merks')) {
            Schema::create('merks', function (Blueprint $table) {
                $table->id();
                $table->string('nama_merk')->unique();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // 2. Master Watt (Daya Wattage PJU)
        if (!Schema::hasTable('watts')) {
            Schema::create('watts', function (Blueprint $table) {
                $table->id();
                $table->integer('nilai_watt')->unique(); // e.g., 20, 30, 40, 90, 120
                $table->string('satuan_daya')->default('Watt');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // 3. Master Tim (Tim Operasional / Crew)
        if (!Schema::hasTable('tims')) {
            Schema::create('tims', function (Blueprint $table) {
                $table->id();
                $table->string('kode_tim')->unique();
                $table->string('nama_tim');
                $table->string('penanggung_jawab')->nullable();
                $table->string('kontak')->nullable();
                $table->string('wilayah_tugas')->nullable();
                $table->enum('status', ['AKTIF', 'NONAKTIF'])->default('AKTIF');
                $table->timestamps();
            });
        }

        // 4. Update Barangs Table for Merk, Watt, Barcode (Individual & Batch), Warranty
        Schema::table('barangs', function (Blueprint $table) {
            if (!Schema::hasColumn('barangs', 'merk_id')) {
                $table->foreignId('merk_id')->nullable()->after('jenis_id')->constrained('merks')->onDelete('set null');
            }
            if (!Schema::hasColumn('barangs', 'watt_id')) {
                $table->foreignId('watt_id')->nullable()->after('merk_id')->constrained('watts')->onDelete('set null');
            }
            if (!Schema::hasColumn('barangs', 'barcode_type')) {
                $table->enum('barcode_type', ['INDIVIDUAL', 'BATCH'])->default('BATCH')->after('kode_barang');
            }
            if (!Schema::hasColumn('barangs', 'barcode_value')) {
                $table->string('barcode_value')->nullable()->after('barcode_type')->index();
            }
            if (!Schema::hasColumn('barangs', 'tanggal_pengadaan')) {
                $table->date('tanggal_pengadaan')->nullable()->after('barcode_value');
            }
            if (!Schema::hasColumn('barangs', 'masa_garansi_bulan')) {
                $table->integer('masa_garansi_bulan')->default(12)->after('tanggal_pengadaan');
            }
            if (!Schema::hasColumn('barangs', 'status')) {
                $table->enum('status', ['AKTIF', 'NONAKTIF'])->default('AKTIF')->after('stok');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $columns = ['merk_id', 'watt_id', 'barcode_type', 'barcode_value', 'tanggal_pengadaan', 'masa_garansi_bulan', 'status'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('barangs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('tims');
        Schema::dropIfExists('watts');
        Schema::dropIfExists('merks');
    }
};
