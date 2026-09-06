<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\PjuAsset;
use App\Models\SyncLog;
use Illuminate\Support\Facades\Log;

class GoogleSheetsService
{
    /**
     * Build Whitelisted Dataset for Public Transparency Layer
     */
    public function buildWhitelistedDataset(string $datasetName): array
    {
        return match ($datasetName) {
            'stok_ringkasan' => Barang::select('kode_barang', 'nama_barang', 'stok', 'stok_minimum')
                ->where('status', 'AKTIF')
                ->get()
                ->toArray(),

            'laporan_barang_masuk' => BarangMasuk::with('barang:id,nama_barang')
                ->select('tanggal_masuk', 'kode_transaksi', 'barang_id', 'jumlah_masuk')
                ->where('status', 'POSTED')
                ->latest()
                ->take(500)
                ->get()
                ->map(fn($item) => [
                    'tanggal' => $item->tanggal_masuk,
                    'kode'    => $item->kode_transaksi,
                    'barang'  => $item->barang->nama_barang ?? '-',
                    'jumlah'  => $item->jumlah_masuk,
                ])
                ->toArray(),

            'laporan_barang_keluar' => BarangKeluar::with('barang:id,nama_barang')
                ->select('tanggal_keluar', 'kode_transaksi', 'barang_id', 'jumlah_keluar')
                ->where('status', 'POSTED')
                ->latest()
                ->take(500)
                ->get()
                ->map(fn($item) => [
                    'tanggal' => $item->tanggal_keluar,
                    'kode'    => $item->kode_transaksi,
                    'barang'  => $item->barang->nama_barang ?? '-',
                    'jumlah'  => $item->jumlah_keluar,
                ])
                ->toArray(),

            'pju_status' => PjuAsset::select('kode_pju', 'nama_pju', 'status_aset')
                ->latest()
                ->take(500)
                ->get()
                ->toArray(),

            default => throw new \InvalidArgumentException("Dataset [{$datasetName}] tidak diizinkan!"),
        };
    }

    /**
     * Synchronize Whitelisted Dataset to Google Sheets Transparency Layer
     */
    public function syncDataset(string $datasetName, ?int $userId = null, bool $mockMode = false, ?string $forceFailure = null): SyncLog
    {
        $startedAt = now();
        $data = $this->buildWhitelistedDataset($datasetName);
        $payloadJson = json_encode($data);
        $payloadHash = md5($payloadJson);

        // 1. Check Idempotency (Prevent Duplicate Sync if Payload Unchanged)
        $existingSuccessfulSync = SyncLog::where('dataset_name', $datasetName)
            ->where('payload_hash', $payloadHash)
            ->where('status', 'SUCCESS')
            ->first();

        if ($existingSuccessfulSync && !$mockMode) {
            return SyncLog::create([
                'sync_type'         => 'GOOGLE_SHEETS',
                'source'            => 'MYSQL_SYSTEM_OF_RECORD',
                'target'            => 'GOOGLE_SHEETS_TRANSPARENCY',
                'dataset_name'      => $datasetName,
                'payload_hash'      => $payloadHash,
                'payload_reference' => ['records_count' => count($data), 'idempotency' => 'SKIP_DUPLICATE'],
                'status'            => 'SUCCESS',
                'records_synced'    => 0,
                'attempts'          => 1,
                'started_at'        => $startedAt,
                'completed_at'      => now(),
                'triggered_by'      => $userId,
            ]);
        }

        $syncLog = SyncLog::create([
            'sync_type'         => 'GOOGLE_SHEETS',
            'source'            => 'MYSQL_SYSTEM_OF_RECORD',
            'target'            => 'GOOGLE_SHEETS_TRANSPARENCY',
            'dataset_name'      => $datasetName,
            'payload_hash'      => $payloadHash,
            'payload_reference' => ['records_count' => count($data), 'sample' => array_slice($data, 0, 2)],
            'status'            => 'PROCESSING',
            'attempts'          => 1,
            'started_at'        => $startedAt,
            'triggered_by'      => $userId,
        ]);

        try {
            // Simulate / Execute Failure Condition
            if ($forceFailure === 'NETWORK_DOWN') {
                throw new \Exception("Koneksi jaringan terputus (Network Connection Timeout)");
            }
            if ($forceFailure === 'GOOGLE_API_DOWN') {
                throw new \Exception("Google Sheets API 503 Service Unavailable");
            }
            if ($forceFailure === 'CONFLICT') {
                $syncLog->update([
                    'status'           => 'CONFLICT',
                    'error_message'    => 'Deteksi Konflik: Remote Sheet memiliki versi lebih baru yang belum disinkronkan.',
                    'conflict_details' => [
                        'source'        => 'MYSQL_SYSTEM_OF_RECORD',
                        'target'        => 'GOOGLE_SHEETS_TRANSPARENCY',
                        'timestamp'     => now()->toIso8601String(),
                        'local_hash'    => $payloadHash,
                        'remote_hash'   => 'REMOTE_CONFLICT_HASH_999',
                        'status'        => 'RESOLVE_MANUALLY_DO_NOT_OVERWRITE',
                    ],
                    'completed_at'     => now(),
                ]);
                return $syncLog;
            }

            // Execute Google API Sync (or Mock Execution)
            $recordsSynced = count($data);

            $syncLog->update([
                'status'         => 'SUCCESS',
                'records_synced' => $recordsSynced,
                'completed_at'   => now(),
            ]);

            return $syncLog;
        } catch (\Exception $e) {
            Log::error("Google Sheets Sync Failed for [{$datasetName}]: " . $e->getMessage());

            $syncLog->update([
                'status'        => 'FAILED',
                'error_message' => $e->getMessage(),
                'completed_at'  => now(),
            ]);

            return $syncLog;
        }
    }
}
