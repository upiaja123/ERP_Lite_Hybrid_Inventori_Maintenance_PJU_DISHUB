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
        // 1. Tabel Laporan Kerusakan PJU
        Schema::create('kerusakan_pjus', function (Blueprint $table) {
            $table->id();
            $table->string('no_laporan')->unique();
            $table->foreignId('pju_asset_id')->nullable()->constrained('pju_assets')->onDelete('cascade');
            $table->foreignId('lokasi_id')->nullable()->constrained('lokasi_pjus')->onDelete('set null');
            $table->date('tanggal_laporan');
            $table->foreignId('pelapor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nama_pelapor')->nullable();
            $table->string('jenis_kerusakan');
            $table->text('deskripsi_kerusakan');
            $table->enum('status', ['DILAPORKAN', 'DIPROSES', 'SELESAI', 'RETUR_VENDOR', 'NONAKTIF'])->default('DILAPORKAN');
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Tabel Maintenance Work Order PJU
        Schema::create('maintenance_pjus', function (Blueprint $table) {
            $table->id();
            $table->string('no_work_order')->unique();
            $table->foreignId('kerusakan_id')->nullable()->constrained('kerusakan_pjus')->onDelete('set null');
            $table->foreignId('pju_asset_id')->nullable()->constrained('pju_assets')->onDelete('cascade');
            $table->foreignId('lokasi_id')->nullable()->constrained('lokasi_pjus')->onDelete('set null');
            $table->foreignId('teknisi_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->enum('jenis_maintenance', ['PREVENTIF', 'KOREKTIF', 'DARURAT'])->default('KOREKTIF');
            $table->text('tindakan_perbaikan');
            $table->json('spare_part_digunakan')->nullable();
            $table->enum('hasil', ['PENDING', 'BERHASIL', 'BUTUH_RETUR', 'GAGAL'])->default('PENDING');
            $table->enum('status', ['SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED'])->default('SCHEDULED');
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Tabel Pencopotan PJU
        Schema::create('pencopotan_pjus', function (Blueprint $table) {
            $table->id();
            $table->string('no_pencopotan')->unique();
            $table->foreignId('pju_asset_id')->constrained('pju_assets')->onDelete('cascade');
            $table->foreignId('lokasi_id')->nullable()->constrained('lokasi_pjus')->onDelete('set null');
            $table->date('tanggal_copot');
            $table->foreignId('teknisi_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('alasan', ['RUSAK_BERAT', 'MAINTENANCE', 'RETUR_VENDOR', 'PEREMAJAAN'])->default('RUSAK_BERAT');
            $table->string('kondisi_barang');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Tabel Garansi PJU
        Schema::create('garansi_pjus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pju_asset_id')->constrained('pju_assets')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('no_kontrak_garansi')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_berakhir');
            $table->enum('status', ['BERLAKU', 'EXPIRED', 'CLAIMED'])->default('BERLAKU');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        // 5. Tabel Retur Vendor
        Schema::create('retur_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('no_retur')->unique();
            $table->foreignId('pju_asset_id')->constrained('pju_assets')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->text('alasan_retur');
            $table->date('tanggal_retur');
            $table->date('tanggal_kembali')->nullable();
            $table->string('kondisi_kembali')->nullable();
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'PROSES_VENDOR', 'SELESAI_GANTI', 'DITOLAK_VENDOR', 'VOID'])->default('DRAFT');
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Tabel Attachments / Media Uploads
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('attachable_type');
            $table->unsignedBigInteger('attachable_id');
            $table->string('filename');
            $table->string('file_path');
            $table->string('file_type')->nullable(); // image/jpeg, pdf, etc.
            $table->integer('file_size')->nullable();
            $table->string('category')->default('GENERAL'); // FOTO_KERUSAKAN, FOTO_PASANG, PROOF_RETUR
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('retur_vendors');
        Schema::dropIfExists('garansi_pjus');
        Schema::dropIfExists('pencopotan_pjus');
        Schema::dropIfExists('maintenance_pjus');
        Schema::dropIfExists('kerusakan_pjus');
    }
};
