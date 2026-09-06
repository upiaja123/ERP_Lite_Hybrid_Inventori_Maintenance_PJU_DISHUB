<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('satuans')) {
            Schema::create('satuans', function (Blueprint $table) {
                $table->id();
                $table->string('satuan')->unique();
                $table->string('nama_satuan')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('jenis')) {
            Schema::create('jenis', function (Blueprint $table) {
                $table->id();
                $table->string('nama_jenis')->unique();
                $table->string('jenis_barang')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->string('kode_customer')->nullable();
                $table->string('customer')->nullable();
                $table->string('nama_customer')->nullable();
                $table->text('alamat')->nullable();
                $table->string('telepon')->nullable();
                $table->text('deskripsi')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('jenis');
        Schema::dropIfExists('satuans');
    }
};
