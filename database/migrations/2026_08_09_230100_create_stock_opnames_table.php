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
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->string('kode_opname')->unique();
            $table->date('tanggal_opname');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->double('stok_sistem', 15, 2);
            $table->double('stok_fisik', 15, 2);
            $table->double('selisih', 15, 2); // stok_fisik - stok_sistem
            $table->enum('status_opname', ['DRAFT', 'SUBMITTED', 'APPROVED', 'REJECTED', 'ADJUSTED'])->default('DRAFT');
            $table->text('alasan_selisih')->nullable();
            $table->foreignId('petugas_id')->constrained('users')->onDelete('cascade');
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
        Schema::dropIfExists('stock_opnames');
    }
};
