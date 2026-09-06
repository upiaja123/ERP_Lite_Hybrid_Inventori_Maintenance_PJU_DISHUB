<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('barang_masuks')) {
            Schema::create('barang_masuks', function (Blueprint $table) {
                $table->id();
                $table->string('kode_transaksi')->unique();
                $table->string('no_dokumen')->nullable();
                $table->date('tanggal_masuk');
                $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
                $table->double('jumlah_masuk', 15, 2)->default(0);
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
                $table->string('status')->default('POSTED');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('approval_notes')->nullable();
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('barang_keluars')) {
            Schema::create('barang_keluars', function (Blueprint $table) {
                $table->id();
                $table->string('kode_transaksi')->unique();
                $table->string('no_dokumen')->nullable();
                $table->date('tanggal_keluar');
                $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
                $table->double('jumlah_keluar', 15, 2)->default(0);
                $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
                $table->string('status')->default('POSTED');
                $table->unsignedBigInteger('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('approval_notes')->nullable();
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('barang_keluars');
        Schema::dropIfExists('barang_masuks');
    }
};
