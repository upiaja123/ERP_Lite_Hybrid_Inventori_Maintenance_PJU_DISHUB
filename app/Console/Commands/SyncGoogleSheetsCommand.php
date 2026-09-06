<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetsService;
use Illuminate\Console\Command;

class SyncGoogleSheetsCommand extends Command
{
    protected $signature = 'sync:google-sheets {dataset? : Dataset name to sync}';
    protected $description = 'Trigger background synchronization of public datasets to Google Sheets';

    public function handle(GoogleSheetsService $sheetsService): int
    {
        $dataset = $this->argument('dataset');
        $datasets = $dataset ? [$dataset] : ['stok_ringkasan', 'laporan_barang_masuk', 'laporan_barang_keluar', 'pju_status'];

        $this->info("Memulai sinkronisasi " . count($datasets) . " dataset ke Google Sheets Transparency Layer...");

        foreach ($datasets as $ds) {
            $log = $sheetsService->syncDataset($ds);
            $this->line("Dataset [{$ds}]: Status = {$log->status}, Synced = {$log->records_synced} records.");
        }

        $this->info("Sinkronisasi Selesai.");
        return Command::SUCCESS;
    }
}
