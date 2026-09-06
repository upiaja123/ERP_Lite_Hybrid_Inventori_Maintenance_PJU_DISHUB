<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\StockOpname;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockOpnameController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        return view('stock-opname.index', [
            'opnames' => StockOpname::with(['barang', 'petugas', 'approver'])->latest()->get(),
            'barangs' => Barang::all(),
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => StockOpname::with(['barang', 'petugas', 'approver'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['admin gudang']) && !auth()->user()->hasPermissionTo('inventory.create')) {
            abort(403, 'Unauthorized action. Hanya Admin Gudang atau Super Admin yang dapat mencatat stock opname.');
        }

        $validator = Validator::make($request->all(), [
            'barang_id'      => 'required|exists:barangs,id',
            'stok_fisik'     => 'required|numeric|min:0',
            'tanggal_opname' => 'required|date',
            'alasan_selisih' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $opname = $this->inventoryService->recordStockOpname(
            $request->all(),
            auth()->id() ?? 1
        );

        return response()->json([
            'success' => true,
            'message' => 'Data Stock Opname Berhasil Disimpan!',
            'data'    => $opname->load(['barang', 'petugas'])
        ]);
    }

    public function approve(StockOpname $stockOpname)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['kepala gudang']) && !auth()->user()->hasPermissionTo('inventory.approve')) {
            abort(403, 'Unauthorized action. Hanya Kepala Gudang atau Super Admin yang berwenang menyetujui stock opname.');
        }

        try {
            $approvedOpname = $this->inventoryService->approveStockOpname(
                $stockOpname,
                auth()->id() ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock Opname Berhasil Disetujui & Stok Di-adjustment Via Ledger!',
                'data'    => $approvedOpname
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(StockOpname $stockOpname)
    {
        if ($stockOpname->status_opname === 'ADJUSTED') {
            return response()->json([
                'success' => false,
                'message' => 'Stock Opname yang sudah di-adjustment tidak dapat dihapus!'
            ], 422);
        }

        $stockOpname->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Stock Opname Berhasil Dihapus!'
        ]);
    }
}
