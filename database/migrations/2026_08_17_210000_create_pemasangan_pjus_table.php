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
        Schema::create('pemasangan_pjus', function (Blueprint $table) {
            $table->id();
            $table->string('no_pemasangan')->unique();
            $table->foreignId('pju_asset_id')->constrained('pju_assets')->onDelete('cascade');
            $table->foreignId('lokasi_id')->constrained('lokasi_pjus')->onDelete('cascade');
            $table->foreignId('teknisi_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('maintenance_id')->nullable()->constrained('maintenance_pjus')->onDelete('set null');
            $table->date('tanggal_pasang');
            $table->string('kondisi_pasang')->default('BAIK');
            $table->string('foto_pemasangan')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['PENDING', 'TERPASANG', 'BATAL'])->default('TERPASANG');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemasangan_pjus');
    }
};
