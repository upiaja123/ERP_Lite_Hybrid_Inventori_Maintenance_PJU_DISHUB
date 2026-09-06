<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Jenis
        Schema::table('jenis', function (Blueprint $table) {
            if (!Schema::hasColumn('jenis', 'nama_jenis')) {
                $table->string('nama_jenis')->nullable()->after('id');
            }
            if (!Schema::hasColumn('jenis', 'jenis_barang')) {
                $table->string('jenis_barang')->nullable()->after('id');
            }
            if (!Schema::hasColumn('jenis', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
            }
        });

        // Sync values
        DB::statement("UPDATE jenis SET jenis_barang = nama_jenis WHERE jenis_barang IS NULL AND nama_jenis IS NOT NULL");
        DB::statement("UPDATE jenis SET nama_jenis = jenis_barang WHERE nama_jenis IS NULL AND jenis_barang IS NOT NULL");

        // 2. Satuans
        Schema::table('satuans', function (Blueprint $table) {
            if (!Schema::hasColumn('satuans', 'nama_satuan')) {
                $table->string('nama_satuan')->nullable()->after('satuan');
            }
            if (!Schema::hasColumn('satuans', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
            }
        });

        // 3. Suppliers
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'supplier')) {
                $table->string('supplier')->nullable()->after('id');
            }
            if (!Schema::hasColumn('suppliers', 'nama_supplier')) {
                $table->string('nama_supplier')->nullable()->after('id');
            }
            if (!Schema::hasColumn('suppliers', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
            }
        });

        DB::statement("UPDATE suppliers SET supplier = nama_supplier WHERE supplier IS NULL AND nama_supplier IS NOT NULL");
        DB::statement("UPDATE suppliers SET nama_supplier = supplier WHERE nama_supplier IS NULL AND supplier IS NOT NULL");

        // 4. Customers
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'customer')) {
                $table->string('customer')->nullable()->after('id');
            }
            if (!Schema::hasColumn('customers', 'nama_customer')) {
                $table->string('nama_customer')->nullable()->after('id');
            }
            if (!Schema::hasColumn('customers', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
            }
        });

        DB::statement("UPDATE customers SET customer = nama_customer WHERE customer IS NULL AND nama_customer IS NOT NULL");
        DB::statement("UPDATE customers SET nama_customer = customer WHERE nama_customer IS NULL AND customer IS NOT NULL");
    }

    public function down(): void
    {
    }
};
