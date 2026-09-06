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
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->index();
            $table->enum('transaction_type', ['IN', 'OUT', 'ADJUSTMENT', 'RETURN', 'VOID_IN', 'VOID_OUT', 'OPENING'])->default('IN');
            $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
            $table->double('quantity', 15, 2);
            $table->double('quantity_before', 15, 2)->default(0);
            $table->double('quantity_after', 15, 2)->default(0);
            $table->string('reference_type')->nullable(); // e.g., App\Models\BarangMasuk
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->string('status')->default('POSTED'); // POSTED, VOID
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });

        // Add non-breaking columns to barang_masuks table
        if (Schema::hasTable('barang_masuks')) {
            Schema::table('barang_masuks', function (Blueprint $table) {
                if (!Schema::hasColumn('barang_masuks', 'status')) {
                    $table->string('status')->default('POSTED')->after('supplier_id');
                }
                if (!Schema::hasColumn('barang_masuks', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('status');
                }
                if (!Schema::hasColumn('barang_masuks', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('barang_masuks', 'approval_notes')) {
                    $table->text('approval_notes')->nullable()->after('approved_at');
                }
                if (!Schema::hasColumn('barang_masuks', 'no_dokumen')) {
                    $table->string('no_dokumen')->nullable()->after('kode_transaksi');
                }
            });
        }

        // Add non-breaking columns to barang_keluars table
        if (Schema::hasTable('barang_keluars')) {
            Schema::table('barang_keluars', function (Blueprint $table) {
                if (!Schema::hasColumn('barang_keluars', 'status')) {
                    $table->string('status')->default('POSTED')->after('customer_id');
                }
                if (!Schema::hasColumn('barang_keluars', 'approved_by')) {
                    $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->after('status');
                }
                if (!Schema::hasColumn('barang_keluars', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('barang_keluars', 'approval_notes')) {
                    $table->text('approval_notes')->nullable()->after('approved_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');

        if (Schema::hasTable('barang_masuks')) {
            Schema::table('barang_masuks', function (Blueprint $table) {
                $columns = ['status', 'approved_by', 'approved_at', 'approval_notes', 'no_dokumen'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('barang_masuks', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('barang_keluars')) {
            Schema::table('barang_keluars', function (Blueprint $table) {
                $columns = ['status', 'approved_by', 'approved_at', 'approval_notes'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('barang_keluars', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
