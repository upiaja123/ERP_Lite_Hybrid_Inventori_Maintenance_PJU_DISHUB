<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('barangs')) {
            Schema::create('barangs', function (Blueprint $table) {
                $table->id();
                $table->string('kode_barang')->unique();
                $table->enum('barcode_type', ['INDIVIDUAL', 'BATCH'])->default('BATCH');
                $table->string('barcode_value')->nullable()->index();
                $table->string('nama_barang');
                $table->foreignId('jenis_id')->nullable()->constrained('jenis')->onDelete('set null');
                $table->unsignedBigInteger('merk_id')->nullable();
                $table->unsignedBigInteger('watt_id')->nullable();
                $table->foreignId('satuan_id')->nullable()->constrained('satuans')->onDelete('set null');
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->double('stok', 15, 2)->default(0);
                $table->double('stok_minimum', 15, 2)->default(10);
                $table->enum('status', ['AKTIF', 'NONAKTIF'])->default('AKTIF');
                $table->date('tanggal_pengadaan')->nullable();
                $table->integer('masa_garansi_bulan')->default(12);
                $table->decimal('harga_satuan', 15, 2)->nullable();
                $table->string('lokasi_penyimpanan')->nullable();
                $table->text('deskripsi')->nullable();
                $table->text('gambar')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('barangs');
    }
};
