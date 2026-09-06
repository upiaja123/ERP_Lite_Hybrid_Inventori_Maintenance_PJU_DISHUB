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
        Schema::create('stock_mutasis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mutasi')->unique();
            $table->date('tanggal_mutasi');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->double('jumlah', 15, 2);
            
            // Asal & Tujuan Mutasi
            $table->enum('tipe_mutasi', ['WAREHOUSE_TO_WAREHOUSE', 'WAREHOUSE_TO_FIELD', 'LOCATION_TO_LOCATION'])->default('WAREHOUSE_TO_FIELD');
            $table->string('asal_lokasi')->default('GUDANG UTAMA');
            $table->foreignId('tujuan_lokasi_id')->nullable()->constrained('lokasi_pjus')->onDelete('set null');
            $table->string('tujuan_lokasi_nama')->nullable();
            
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'APPROVED', 'IN_TRANSIT', 'POSTED', 'CANCELLED'])->default('DRAFT');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_mutasis');
    }
};
