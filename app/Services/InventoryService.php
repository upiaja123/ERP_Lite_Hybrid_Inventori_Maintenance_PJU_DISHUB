<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\StockLedger;
use App\Models\StockOpname;
use App\Models\StockMutasi;
use App\Services\ApprovalWorkflowService;
use App\Services\ItemCodeGeneratorService;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    protected ApprovalWorkflowService $approvalService;
    protected ItemCodeGeneratorService $codeGenerator;

    public function __construct(
        ApprovalWorkflowService $approvalService,
        ItemCodeGeneratorService $codeGenerator
    ) {
        $this->approvalService = $approvalService;
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Record Barang Masuk (Inbound Workflow: Draft -> Submitted -> Approved -> Received -> Posted)
     */
    public function recordInbound(array $data, int $userId): BarangMasuk
    {
        return DB::transaction(function () use ($data, $userId) {
            $barang = Barang::where('id', $data['barang_id'])->lockForUpdate()->firstOrFail();

            $transactionNumber = $data['kode_transaksi'] ?? $this->codeGenerator->generate('barang_masuk', 'barang_masuks', 'kode_transaksi');
            $quantity = (float) $data['jumlah_masuk'];

            $isApprovalRequired = $this->approvalService->isApprovalRequired('barang_masuk');
            $initialStatus = $data['status'] ?? ($isApprovalRequired ? 'DRAFT' : 'POSTED');

            $barangMasuk = BarangMasuk::create([
                'tanggal_masuk'  => $data['tanggal_masuk'],
                'barang_id'      => $barang->id,
                'jumlah_masuk'   => $quantity,
                'supplier_id'    => $data['supplier_id'],
                'kode_transaksi' => $transactionNumber,
                'no_dokumen'     => $data['no_dokumen'] ?? null,
                'user_id'        => $userId,
                'status'         => $initialStatus,
            ]);

            // Jika status diset ke POSTED, buat ledger entry & update stok
            if ($initialStatus === 'POSTED') {
                $this->postInboundLedger($barang, $barangMasuk, $quantity, $userId);
            }

            return $barangMasuk->load(['barang.satuan', 'supplier']);
        });
    }

    /**
     * Post Inbound Ledger Entry (Posting Point)
     */
    public function postInboundLedger(Barang $barang, BarangMasuk $barangMasuk, float $quantity, int $userId): StockLedger
    {
        $qtyBefore = (float) $barang->stok;
        $qtyAfter = $qtyBefore + $quantity;

        $ledger = StockLedger::create([
            'transaction_number' => $barangMasuk->kode_transaksi,
            'transaction_type'   => 'IN',
            'barang_id'          => $barang->id,
            'quantity'           => $quantity,
            'quantity_before'    => $qtyBefore,
            'quantity_after'     => $qtyAfter,
            'reference_type'     => get_class($barangMasuk),
            'reference_id'       => $barangMasuk->id,
            'user_id'            => $userId,
            'notes'              => "Barang Masuk #{$barangMasuk->kode_transaksi}",
            'status'             => 'POSTED',
        ]);

        $barang->stok = $qtyAfter;
        $barang->save();

        $barangMasuk->status = 'POSTED';
        $barangMasuk->save();

        return $ledger;
    }

    /**
     * Record Barang Keluar (Outbound Workflow: Draft -> Submitted -> Approved -> Released -> Posted)
     */
    public function recordOutbound(array $data, int $userId): BarangKeluar
    {
        return DB::transaction(function () use ($data, $userId) {
            if (isset($data['nama_barang']) && !isset($data['barang_id'])) {
                $barang = Barang::where('nama_barang', $data['nama_barang'])->lockForUpdate()->first();
            } else {
                $barang = Barang::where('id', $data['barang_id'])->lockForUpdate()->first();
            }

            if (!$barang) {
                throw new \InvalidArgumentException("Barang tidak ditemukan!");
            }

            $quantity = (float) $data['jumlah_keluar'];

            // VALIDASI STOK (Strict Ledger Check)
            if ($quantity > $barang->stok && !config('erp.stock.allow_negative', false)) {
                throw new InsufficientStockException(
                    "Stok {$barang->nama_barang} tidak mencukupi! (Stok tersedia: {$barang->stok}, diminta: {$quantity})",
                    $barang->id,
                    $quantity,
                    $barang->stok
                );
            }

            $transactionNumber = $data['kode_transaksi'] ?? $this->codeGenerator->generate('barang_keluar', 'barang_keluars', 'kode_transaksi');
            $isApprovalRequired = $this->approvalService->isApprovalRequired('barang_keluar');
            $initialStatus = $data['status'] ?? ($isApprovalRequired ? 'DRAFT' : 'POSTED');

            $barangKeluar = BarangKeluar::create([
                'tanggal_keluar' => $data['tanggal_keluar'],
                'barang_id'      => $barang->id,
                'jumlah_keluar'  => $quantity,
                'customer_id'    => $data['customer_id'],
                'kode_transaksi' => $transactionNumber,
                'user_id'        => $userId,
                'status'         => $initialStatus,
            ]);

            if ($initialStatus === 'POSTED') {
                $this->postOutboundLedger($barang, $barangKeluar, $quantity, $userId);
            }

            return $barangKeluar;
        });
    }

    /**
     * Post Outbound Ledger Entry (Posting Point)
     */
    public function postOutboundLedger(Barang $barang, BarangKeluar $barangKeluar, float $quantity, int $userId): StockLedger
    {
        $qtyBefore = (float) $barang->stok;
        $qtyAfter = max(0, $qtyBefore - $quantity);

        $ledger = StockLedger::create([
            'transaction_number' => $barangKeluar->kode_transaksi,
            'transaction_type'   => 'OUT',
            'barang_id'          => $barang->id,
            'quantity'           => $quantity,
            'quantity_before'    => $qtyBefore,
            'quantity_after'     => $qtyAfter,
            'reference_type'     => get_class($barangKeluar),
            'reference_id'       => $barangKeluar->id,
            'user_id'            => $userId,
            'notes'              => "Barang Keluar #{$barangKeluar->kode_transaksi}",
            'status'             => 'POSTED',
        ]);

        $barang->stok = $qtyAfter;
        $barang->save();

        $barangKeluar->status = 'POSTED';
        $barangKeluar->save();

        return $ledger;
    }

    /**
     * Record Stock Mutation (Warehouse -> Warehouse, Warehouse -> Field, Location -> Location)
     */
    public function recordMutation(array $data, int $userId): StockMutasi
    {
        return DB::transaction(function () use ($data, $userId) {
            $barang = Barang::where('id', $data['barang_id'])->lockForUpdate()->firstOrFail();
            $quantity = (float) $data['jumlah'];

            if ($quantity > $barang->stok && !config('erp.stock.allow_negative', false)) {
                throw new InsufficientStockException(
                    "Stok tidak mencukupi untuk mutasi!",
                    $barang->id,
                    $quantity,
                    $barang->stok
                );
            }

            $kodeMutasi = $data['kode_mutasi'] ?? $this->codeGenerator->generate('distribusi', 'stock_mutasis', 'kode_mutasi');

            $mutasi = StockMutasi::create([
                'kode_mutasi'        => $kodeMutasi,
                'tanggal_mutasi'     => $data['tanggal_mutasi'] ?? now(),
                'barang_id'          => $barang->id,
                'jumlah'             => $quantity,
                'tipe_mutasi'        => $data['tipe_mutasi'] ?? 'WAREHOUSE_TO_FIELD',
                'asal_lokasi'        => $data['asal_lokasi'] ?? 'GUDANG UTAMA',
                'tujuan_lokasi_id'   => $data['tujuan_lokasi_id'] ?? null,
                'tujuan_lokasi_nama' => $data['tujuan_lokasi_nama'] ?? null,
                'status'             => 'POSTED',
                'catatan'            => $data['catatan'] ?? null,
                'created_by'         => $userId,
            ]);

            // Mutasi mengurangi stok gudang asal dan mencatat ledger TRANSFER_OUT
            $qtyBefore = (float) $barang->stok;
            $qtyAfter = max(0, $qtyBefore - $quantity);

            StockLedger::create([
                'transaction_number' => $mutasi->kode_mutasi,
                'transaction_type'   => 'TRANSFER_OUT',
                'barang_id'          => $barang->id,
                'quantity'           => $quantity,
                'quantity_before'    => $qtyBefore,
                'quantity_after'     => $qtyAfter,
                'reference_type'     => get_class($mutasi),
                'reference_id'       => $mutasi->id,
                'user_id'            => $userId,
                'notes'              => "Mutasi Stok #{$mutasi->kode_mutasi} ({$mutasi->asal_lokasi} -> {$mutasi->tujuan_lokasi_nama})",
                'status'             => 'POSTED',
            ]);

            $barang->stok = $qtyAfter;
            $barang->save();

            return $mutasi;
        });
    }

    /**
     * Record Stock Opname Session & Difference Calculation
     */
    public function recordStockOpname(array $data, int $userId): StockOpname
    {
        return DB::transaction(function () use ($data, $userId) {
            $barang = Barang::where('id', $data['barang_id'])->lockForUpdate()->firstOrFail();

            $stokSistem = (float) $barang->stok;
            $stokFisik = (float) $data['stok_fisik'];
            $selisih = $stokFisik - $stokSistem;

            $kodeOpname = $data['kode_opname'] ?? $this->codeGenerator->generate('barang', 'stock_opnames', 'kode_opname');

            return StockOpname::create([
                'kode_opname'    => $kodeOpname,
                'tanggal_opname' => $data['tanggal_opname'] ?? now(),
                'barang_id'      => $barang->id,
                'stok_sistem'    => $stokSistem,
                'stok_fisik'     => $stokFisik,
                'selisih'        => $selisih,
                'status_opname'  => 'SUBMITTED',
                'alasan_selisih' => $data['alasan_selisih'] ?? null,
                'petugas_id'     => $userId,
            ]);
        });
    }

    /**
     * Approve Stock Opname & Create Adjustment Ledger (No Manual Stock Update!)
     */
    public function approveStockOpname(StockOpname $opname, int $approverId): StockOpname
    {
        return DB::transaction(function () use ($opname, $approverId) {
            $barang = Barang::where('id', $opname->barang_id)->lockForUpdate()->firstOrFail();

            $qtyBefore = (float) $barang->stok;
            $adjustmentQty = (float) $opname->selisih;
            $qtyAfter = (float) $opname->stok_fisik;

            // Catat Ledger Entry tipe ADJUSTMENT
            StockLedger::create([
                'transaction_number' => $opname->kode_opname . '-ADJ',
                'transaction_type'   => 'ADJUSTMENT',
                'barang_id'          => $barang->id,
                'quantity'           => $adjustmentQty,
                'quantity_before'    => $qtyBefore,
                'quantity_after'     => $qtyAfter,
                'reference_type'     => get_class($opname),
                'reference_id'       => $opname->id,
                'user_id'            => $approverId,
                'notes'              => "Stock Opname Adjustment #{$opname->kode_opname} (Selisih: {$adjustmentQty})",
                'status'             => 'POSTED',
            ]);

            $barang->stok = $qtyAfter;
            $barang->save();

            $opname->status_opname = 'ADJUSTED';
            $opname->approved_by = $approverId;
            $opname->approved_at = now();
            $opname->save();

            return $opname;
        });
    }

    /**
     * Void Barang Masuk Transaction with counter-ledger entry
     */
    public function voidBarangMasuk(BarangMasuk $barangMasuk, int $userId): bool
    {
        return DB::transaction(function () use ($barangMasuk, $userId) {
            $barang = Barang::where('id', $barangMasuk->barang_id)->lockForUpdate()->first();

            if ($barang && $barangMasuk->status === 'POSTED') {
                $qtyBefore = (float) $barang->stok;
                $qtyAfter = max(0, $qtyBefore - $barangMasuk->jumlah_masuk);

                StockLedger::create([
                    'transaction_number' => $barangMasuk->kode_transaksi . '-VOID',
                    'transaction_type'   => 'VOID_IN',
                    'barang_id'          => $barang->id,
                    'quantity'           => $barangMasuk->jumlah_masuk,
                    'quantity_before'    => $qtyBefore,
                    'quantity_after'     => $qtyAfter,
                    'reference_type'     => get_class($barangMasuk),
                    'reference_id'       => $barangMasuk->id,
                    'user_id'            => $userId,
                    'notes'              => "Pembatalan (Void) Barang Masuk #{$barangMasuk->kode_transaksi}",
                    'status'             => 'VOID',
                ]);

                $barang->stok = $qtyAfter;
                $barang->save();
            }

            $barangMasuk->status = 'VOID';
            $barangMasuk->save();

            return true;
        });
    }

    /**
     * Void Barang Keluar Transaction with counter-ledger entry
     */
    public function voidBarangKeluar(BarangKeluar $barangKeluar, int $userId): bool
    {
        return DB::transaction(function () use ($barangKeluar, $userId) {
            $barang = Barang::where('id', $barangKeluar->barang_id)->lockForUpdate()->first();

            if ($barang && $barangKeluar->status === 'POSTED') {
                $qtyBefore = (float) $barang->stok;
                $qtyAfter = $qtyBefore + $barangKeluar->jumlah_keluar;

                StockLedger::create([
                    'transaction_number' => $barangKeluar->kode_transaksi . '-VOID',
                    'transaction_type'   => 'VOID_OUT',
                    'barang_id'          => $barang->id,
                    'quantity'           => $barangKeluar->jumlah_keluar,
                    'quantity_before'    => $qtyBefore,
                    'quantity_after'     => $qtyAfter,
                    'reference_type'     => get_class($barangKeluar),
                    'reference_id'       => $barangKeluar->id,
                    'user_id'            => $userId,
                    'notes'              => "Pembatalan (Void) Barang Keluar #{$barangKeluar->kode_transaksi}",
                    'status'             => 'VOID',
                ]);

                $barang->stok = $qtyAfter;
                $barang->save();
            }

            $barangKeluar->status = 'VOID';
            $barangKeluar->save();

            return true;
        });
    }

    /**
     * Kalkulasi Stok berdasarkan Formula Konseptual Ledger:
     * Opening Balance + Inbound - Outbound + Transfer In - Transfer Out + Adjustment
     */
    public function calculateStockFromLedger(int $barangId): float
    {
        $opening    = StockLedger::where('barang_id', $barangId)->where('status', 'POSTED')->where('transaction_type', 'OPENING')->sum('quantity');
        $inbound    = StockLedger::where('barang_id', $barangId)->where('status', 'POSTED')->where('transaction_type', 'IN')->sum('quantity');
        $outbound   = StockLedger::where('barang_id', $barangId)->where('status', 'POSTED')->where('transaction_type', 'OUT')->sum('quantity');
        $transferIn = StockLedger::where('barang_id', $barangId)->where('status', 'POSTED')->where('transaction_type', 'TRANSFER_IN')->sum('quantity');
        $transferOut= StockLedger::where('barang_id', $barangId)->where('status', 'POSTED')->where('transaction_type', 'TRANSFER_OUT')->sum('quantity');
        $adjustment = StockLedger::where('barang_id', $barangId)->where('status', 'POSTED')->where('transaction_type', 'ADJUSTMENT')->sum('quantity');
        $voidIn     = StockLedger::where('barang_id', $barangId)->where('status', 'VOID')->where('transaction_type', 'VOID_IN')->sum('quantity');
        $voidOut    = StockLedger::where('barang_id', $barangId)->where('status', 'VOID')->where('transaction_type', 'VOID_OUT')->sum('quantity');

        return ($opening + $inbound - $outbound + $transferIn - $transferOut + $adjustment - $voidIn + $voidOut);
    }
}
