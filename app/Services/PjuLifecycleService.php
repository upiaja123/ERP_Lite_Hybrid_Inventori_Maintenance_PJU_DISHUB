<?php

namespace App\Services;

use App\Models\PjuAsset;
use App\Models\LokasiPju;
use App\Models\PemasanganPju;
use App\Models\KerusakanPju;
use App\Models\MaintenancePju;
use App\Models\PencopotanPju;
use App\Models\GaransiPju;
use App\Models\ReturVendor;
use App\Services\ItemCodeGeneratorService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PjuLifecycleService
{
    protected ItemCodeGeneratorService $codeGenerator;

    public function __construct(ItemCodeGeneratorService $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * PASANG: Install PJU Asset to a Location
     */
    public function installAsset(array $data, int $userId): PemasanganPju
    {
        return DB::transaction(function () use ($data, $userId) {
            $asset = PjuAsset::where('id', $data['pju_asset_id'])->lockForUpdate()->firstOrFail();
            $lokasi = LokasiPju::where('id', $data['lokasi_id'])->firstOrFail();

            if (!in_array($asset->status_aset, ['GUDANG', 'MAINTENANCE'])) {
                throw new InvalidArgumentException("Aset PJU {$asset->kode_pju} tidak dapat dipasang karena berstatus {$asset->status_aset}!");
            }

            $noPemasangan = $data['no_pemasangan'] ?? $this->codeGenerator->generate('pju_asset', 'pemasangan_pjus', 'no_pemasangan');

            $pemasangan = PemasanganPju::create([
                'no_pemasangan'   => $noPemasangan,
                'pju_asset_id'    => $asset->id,
                'lokasi_id'       => $lokasi->id,
                'teknisi_id'      => $data['teknisi_id'] ?? $userId,
                'maintenance_id'  => $data['maintenance_id'] ?? null,
                'tanggal_pasang'  => $data['tanggal_pasang'] ?? now(),
                'kondisi_pasang'  => $data['kondisi_pasang'] ?? 'BAIK',
                'foto_pemasangan' => $data['foto_pemasangan'] ?? null,
                'catatan'         => $data['catatan'] ?? null,
                'status'          => 'TERPASANG',
            ]);

            // Update Lifecycle Aset PJU
            $asset->status_aset = 'TERPASANG';
            $asset->lokasi_id = $lokasi->id;
            $asset->save();

            activity()
                ->performedOn($asset)
                ->causedBy($userId)
                ->withProperties(['lokasi_id' => $lokasi->id, 'no_pemasangan' => $noPemasangan])
                ->log("PJU Asset #{$asset->kode_pju} berhasil DIPASANG di lokasi {$lokasi->alamat_jalan}");

            return $pemasangan;
        });
    }

    /**
     * LAPOR KERUSAKAN: Record Damage Report
     */
    public function reportDamage(array $data, int $userId): KerusakanPju
    {
        return DB::transaction(function () use ($data, $userId) {
            $asset = PjuAsset::where('id', $data['pju_asset_id'])->lockForUpdate()->firstOrFail();

            $noLaporan = $data['no_laporan'] ?? $this->codeGenerator->generate('pju_asset', 'kerusakan_pjus', 'no_laporan');

            $kerusakan = KerusakanPju::create([
                'no_laporan'          => $noLaporan,
                'pju_asset_id'       => $asset->id,
                'lokasi_id'          => $asset->lokasi_id,
                'tanggal_laporan'    => $data['tanggal_laporan'] ?? now(),
                'pelapor_id'         => $userId,
                'nama_pelapor'       => $data['nama_pelapor'] ?? null,
                'jenis_kerusakan'    => $data['jenis_kerusakan'],
                'deskripsi_kerusakan'=> $data['deskripsi_kerusakan'],
                'status'             => 'DILAPORKAN',
            ]);

            $asset->status_aset = 'RUSAK';
            $asset->save();

            return $kerusakan;
        });
    }

    /**
     * MAINTENANCE: Create Work Order & Transition Status
     * Workflow: Reported -> Assigned -> In Progress -> Completed -> Verified -> Closed
     */
    public function createWorkOrder(array $data, int $userId): MaintenancePju
    {
        return DB::transaction(function () use ($data, $userId) {
            $asset = PjuAsset::where('id', $data['pju_asset_id'])->firstOrFail();

            $noWo = $data['no_work_order'] ?? $this->codeGenerator->generate('maintenance', 'maintenance_pjus', 'no_work_order');

            $maintenance = MaintenancePju::create([
                'no_work_order'        => $noWo,
                'kerusakan_id'         => $data['kerusakan_id'] ?? null,
                'pju_asset_id'         => $asset->id,
                'lokasi_id'            => $asset->lokasi_id,
                'teknisi_id'           => $data['teknisi_id'] ?? $userId,
                'tanggal_mulai'        => $data['tanggal_mulai'] ?? now(),
                'jenis_maintenance'    => $data['jenis_maintenance'] ?? 'KOREKTIF',
                'tindakan_perbaikan'   => $data['tindakan_perbaikan'] ?? 'Pemeriksaan rutin',
                'spare_part_digunakan' => $data['spare_part_digunakan'] ?? [],
                'status'               => 'SCHEDULED',
            ]);

            $asset->status_aset = 'MAINTENANCE';
            $asset->save();

            if (isset($data['kerusakan_id'])) {
                KerusakanPju::where('id', $data['kerusakan_id'])->update(['status' => 'DIPROSES']);
            }

            return $maintenance;
        });
    }

    /**
     * Complete Maintenance Work Order
     */
    public function completeWorkOrder(MaintenancePju $maintenance, array $results, int $userId): MaintenancePju
    {
        return DB::transaction(function () use ($maintenance, $results, $userId) {
            $maintenance->update([
                'tanggal_selesai'    => $results['tanggal_selesai'] ?? now(),
                'tindakan_perbaikan' => $results['tindakan_perbaikan'] ?? $maintenance->tindakan_perbaikan,
                'hasil'              => $results['hasil'] ?? 'BERHASIL',
                'status'             => 'COMPLETED',
            ]);

            $asset = $maintenance->asset;
            if ($asset) {
                if ($results['hasil'] === 'BERHASIL') {
                    $asset->status_aset = 'TERPASANG';
                } elseif ($results['hasil'] === 'BUTUH_RETUR') {
                    $asset->status_aset = 'RUSAK';
                }
                $asset->save();
            }

            if ($maintenance->kerusakan_id) {
                KerusakanPju::where('id', $maintenance->kerusakan_id)->update(['status' => 'SELESAI']);
            }

            return $maintenance;
        });
    }

    /**
     * COPOT: Uninstall PJU Asset from a Location
     */
    public function uninstallAsset(array $data, int $userId): PencopotanPju
    {
        return DB::transaction(function () use ($data, $userId) {
            $asset = PjuAsset::where('id', $data['pju_asset_id'])->lockForUpdate()->firstOrFail();

            if ($asset->status_aset !== 'TERPASANG' && !isset($data['force'])) {
                throw new InvalidArgumentException("Aset PJU {$asset->kode_pju} tidak berstatus TERPASANG!");
            }

            $noPencopotan = $data['no_pencopotan'] ?? $this->codeGenerator->generate('pju_asset', 'pencopotan_pjus', 'no_pencopotan');

            $pencopotan = PencopotanPju::create([
                'no_pencopotan'  => $noPencopotan,
                'pju_asset_id'   => $asset->id,
                'lokasi_id'      => $asset->lokasi_id,
                'tanggal_copot'  => $data['tanggal_copot'] ?? now(),
                'teknisi_id'     => $data['teknisi_id'] ?? $userId,
                'alasan'         => $data['alasan'] ?? 'RUSAK_BERAT',
                'kondisi_barang' => $data['kondisi_barang'] ?? 'RUSAK',
                'catatan'        => $data['catatan'] ?? null,
            ]);

            // Update Lifecycle Aset PJU
            $newStatus = match ($data['alasan'] ?? 'RUSAK_BERAT') {
                'MAINTENANCE'   => 'MAINTENANCE',
                'RETUR_VENDOR'  => 'RETUR',
                'PEREMAJAAN'    => 'GUDANG',
                default         => 'RUSAK',
            };

            $asset->status_aset = $newStatus;
            if ($newStatus === 'GUDANG') {
                $asset->lokasi_id = null;
            }
            $asset->save();

            activity()
                ->performedOn($asset)
                ->causedBy($userId)
                ->withProperties(['no_pencopotan' => $noPencopotan, 'alasan' => $data['alasan']])
                ->log("PJU Asset #{$asset->kode_pju} DICOPOT dari lokasi dengan alasan {$data['alasan']}");

            return $pencopotan;
        });
    }

    /**
     * WARRANTY: Register & Evaluate Warranty Status
     */
    public function registerWarranty(array $data): GaransiPju
    {
        $startDate = \Carbon\Carbon::parse($data['tanggal_mulai']);
        $endDate = \Carbon\Carbon::parse($data['tanggal_berakhir']);

        $status = 'BERLAKU';
        if (now()->greaterThan($endDate)) {
            $status = 'EXPIRED';
        } elseif (now()->diffInDays($endDate, false) <= 30) {
            $status = 'EXPIRING';
        }

        return GaransiPju::create([
            'pju_asset_id'       => $data['pju_asset_id'],
            'supplier_id'        => $data['supplier_id'],
            'no_kontrak_garansi' => $data['no_kontrak_garansi'] ?? null,
            'tanggal_mulai'      => $startDate,
            'tanggal_berakhir'   => $endDate,
            'status'             => $status,
            'catatan'            => $data['catatan'] ?? null,
        ]);
    }

    /**
     * VENDOR RETURN: Process Return to Vendor
     * Workflow: Draft -> Submitted -> Approved -> Sent -> Vendor Received -> Inspection -> Repair/Replace/Reject -> Completed
     */
    public function processVendorReturn(array $data, int $userId): ReturVendor
    {
        return DB::transaction(function () use ($data, $userId) {
            $asset = PjuAsset::where('id', $data['pju_asset_id'])->lockForUpdate()->firstOrFail();

            $noRetur = $data['no_retur'] ?? $this->codeGenerator->generate('retur', 'retur_vendors', 'no_retur');

            $retur = ReturVendor::create([
                'no_retur'      => $noRetur,
                'pju_asset_id'  => $asset->id,
                'supplier_id'   => $data['supplier_id'] ?? $asset->supplier_id,
                'alasan_retur'  => $data['alasan_retur'],
                'tanggal_retur' => $data['tanggal_retur'] ?? now(),
                'status'        => 'PROSES_VENDOR',
                'catatan'       => $data['catatan'] ?? null,
                'created_by'    => $userId,
            ]);

            $asset->status_aset = 'RETUR';
            $asset->save();

            return $retur;
        });
    }

    /**
     * Complete Vendor Return (Item Repaired/Replaced & Returned to Gudang)
     */
    public function completeVendorReturn(ReturVendor $retur, array $data, int $userId): ReturVendor
    {
        return DB::transaction(function () use ($retur, $data, $userId) {
            $retur->update([
                'tanggal_kembali' => $data['tanggal_kembali'] ?? now(),
                'kondisi_kembali' => $data['kondisi_kembali'] ?? 'SUDAH_PERBAIKAN',
                'status'          => $data['status'] ?? 'SELESAI_GANTI',
            ]);

            $asset = $retur->asset;
            if ($asset) {
                // Barang kembali dari vendor -> Status menjadi GUDANG (siap dipasang kembali)
                $asset->status_aset = 'GUDANG';
                $asset->lokasi_id = null;
                $asset->save();

                activity()
                    ->performedOn($asset)
                    ->causedBy($userId)
                    ->withProperties(['no_retur' => $retur->no_retur])
                    ->log("PJU Asset #{$asset->kode_pju} KEMBALI dari Retur Vendor dan masuk ke GUDANG");
            }

            return $retur;
        });
    }

    /**
     * TRACEABILITY: Get Full Chronological Lifecycle Audit Trail for an Asset
     */
    public function getAssetTraceabilityHistory(int $pjuAssetId): array
    {
        $asset = PjuAsset::with(['barang', 'lokasi', 'supplier'])->findOrFail($pjuAssetId);

        $pemasangan  = PemasanganPju::where('pju_asset_id', $pjuAssetId)->with(['lokasi', 'teknisi'])->get();
        $kerusakan   = KerusakanPju::where('pju_asset_id', $pjuAssetId)->with(['lokasi', 'pelapor'])->get();
        $maintenance = MaintenancePju::where('pju_asset_id', $pjuAssetId)->with(['lokasi', 'teknisi'])->get();
        $pencopotan  = PencopotanPju::where('pju_asset_id', $pjuAssetId)->with(['lokasi', 'teknisi'])->get();
        $garansi     = GaransiPju::where('pju_asset_id', $pjuAssetId)->with('supplier')->get();
        $retur       = ReturVendor::where('pju_asset_id', $pjuAssetId)->with('supplier')->get();

        return [
            'asset'       => $asset,
            'pemasangan'  => $pemasangan,
            'kerusakan'   => $kerusakan,
            'maintenance' => $maintenance,
            'pencopotan'  => $pencopotan,
            'garansi'     => $garansi,
            'retur'       => $retur,
        ];
    }
}
