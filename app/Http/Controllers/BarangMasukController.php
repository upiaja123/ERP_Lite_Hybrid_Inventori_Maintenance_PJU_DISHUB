<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBarangMasukRequest;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Supplier;
use App\Models\BarangMasuk;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Exception;

class BarangMasukController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        return view('barang-masuk.index', [
            'barangs'   => Barang::with('satuan')->get(),
            'suppliers' => Supplier::all()
        ]);
    }

    // ================= DATATABLE =================
    public function getDataBarangMasuk()
    {
        $data = BarangMasuk::with(['barang.satuan', 'supplier'])
            ->latest()
            ->get();

        return response()->json([
            'data' => $data
        ]);
    }

    // ================= STORE (VIA INVENTORY SERVICE & LEDGER) =================
    public function store(StoreBarangMasukRequest $request)
    {
        try {
            $userId = auth()->id() ?? 1; // Fallback jika durasi seeder/testing

            $barangMasuk = $this->inventoryService->recordInbound(
                $request->validated(),
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => 'Data Barang Masuk Berhasil Disimpan!',
                'data'    => $barangMasuk
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    // ================= DELETE / VOID (VIA INVENTORY SERVICE & COUNTER LEDGER) =================
    public function destroy(BarangMasuk $barangMasuk)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['admin gudang', 'kepala gudang']) && !auth()->user()->hasPermissionTo('inventory.void')) {
            abort(403, 'Unauthorized action. Hanya Admin Gudang, Kepala Gudang, atau Super Admin yang dapat membatalkan (void) barang masuk.');
        }

        try {
            $userId = auth()->id() ?? 1;

            $this->inventoryService->voidBarangMasuk($barangMasuk, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Data Barang Masuk Berhasil Dihapus (Void) & Ledger Diperbarui!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // ================= DETAIL (UNTUK SATUAN & STOK) =================
    public function getBarangDetail(Request $request)
    {
        $barang = Barang::with('satuan')->find($request->barang_id);

        if (!$barang) {
            return response()->json([
                'stok' => 0,
                'satuan' => '-'
            ]);
        }

        return response()->json([
            'stok'   => $barang->stok ?? 0,
            'satuan' => $barang->satuan->satuan ?? '-'
        ]);
    }

    // ================= GET SATUAN =================
    public function getSatuan()
    {
        return response()->json(Satuan::all());
    }
}
