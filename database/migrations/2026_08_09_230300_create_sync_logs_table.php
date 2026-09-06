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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sync_type')->default('GOOGLE_SHEETS'); // GOOGLE_SHEETS, API_EXPORT, etc.
            $table->string('dataset_name');
            $table->enum('status', ['PENDING', 'PROCESSING', 'SUCCESS', 'FAILED', 'CONFLICT'])->default('PENDING');
            $table->integer('records_synced')->default(0);
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
