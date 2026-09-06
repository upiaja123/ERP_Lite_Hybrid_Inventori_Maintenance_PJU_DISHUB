<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\LokasiPju;
use App\Models\StockMutasi;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockMutasiController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        return view('stock-mutasi.index', [
            'mutasis' => StockMutasi::with(['barang', 'tujuanLokasi', 'creator'])->latest()->get(),
            'barangs' => Barang::all(),
            'lokasis' => LokasiPju::all(),
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => StockMutasi::with(['barang', 'tujuanLokasi', 'creator'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'barang_id'        => 'required|exists:barangs,id',
            'jumlah'           => 'required|numeric|min:1',
            'tipe_mutasi'      => 'required|in:WAREHOUSE_TO_WAREHOUSE,WAREHOUSE_TO_FIELD,LOCATION_TO_LOCATION',
            'asal_lokasi'      => 'required|string',
            'tujuan_lokasi_id' => 'nullable|exists:lokasi_pjus,id',
            'catatan'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $mutasi = $this->inventoryService->recordMutation(
                $request->all(),
                auth()->id() ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'Data Mutasi Stok Berhasil Disimpan & Ledger Diperbarui!',
                'data'    => $mutasi->load(['barang', 'tujuanLokasi'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
