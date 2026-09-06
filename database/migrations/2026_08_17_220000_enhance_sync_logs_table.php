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
        Schema::table('sync_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('sync_logs', 'source')) {
                $table->string('source')->default('MYSQL_SYSTEM_OF_RECORD')->after('sync_type');
            }
            if (!Schema::hasColumn('sync_logs', 'target')) {
                $table->string('target')->default('GOOGLE_SHEETS_TRANSPARENCY')->after('source');
            }
            if (!Schema::hasColumn('sync_logs', 'payload_hash')) {
                $table->string('payload_hash')->nullable()->index()->after('dataset_name');
            }
            if (!Schema::hasColumn('sync_logs', 'payload_reference')) {
                $table->json('payload_reference')->nullable()->after('payload_hash');
            }
            if (!Schema::hasColumn('sync_logs', 'conflict_details')) {
                $table->json('conflict_details')->nullable()->after('error_message');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sync_logs', function (Blueprint $table) {
            $columns = ['source', 'target', 'payload_hash', 'payload_reference', 'conflict_details'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('sync_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
