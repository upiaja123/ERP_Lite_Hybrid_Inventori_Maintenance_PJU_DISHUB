<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Http\Requests\StoreBarangKeluarRequest;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Customer;
use App\Models\BarangKeluar;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Exception;

class BarangKeluarController extends Controller
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        return view('barang-keluar.index', [
            'barangs'           => Barang::all(),
            'barangKeluar'      => BarangKeluar::all(),
            'customers'         => Customer::all()
        ]);
    }

    public function getDataBarangKeluar()
    {
        return response()->json([
            'success'   => true,
            'data'      => BarangKeluar::with(['barang', 'customer'])->get(),
            'customer'  => Customer::all()
        ]);
    }

    public function create()
    {
        return view('barang-keluar.create', [
            'barangs' => Barang::all()
        ]);
    }

    // ================= STORE (VIA INVENTORY SERVICE & LEDGER) =================
    public function store(StoreBarangKeluarRequest $request)
    {
        try {
            $userId = auth()->id() ?? 1;

            $barangKeluar = $this->inventoryService->recordOutbound(
                $request->validated(),
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => 'Data Barang Keluar Berhasil Disimpan!',
                'data'    => $barangKeluar
            ]);
        } catch (InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function edit(BarangKeluar $barangKeluar)
    {
        return response()->json([
            'success' => true,
            'message' => 'Edit Data Barang',
            'data'    => $barangKeluar
        ]);
    }

    // ================= DELETE / VOID (VIA INVENTORY SERVICE & COUNTER LEDGER) =================
    public function destroy(BarangKeluar $barangKeluar)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['admin gudang', 'kepala gudang']) && !auth()->user()->hasPermissionTo('inventory.void')) {
            abort(403, 'Unauthorized action. Hanya Admin Gudang, Kepala Gudang, atau Super Admin yang dapat membatalkan (void) barang keluar.');
        }

        try {
            $userId = auth()->id() ?? 1;

            $this->inventoryService->voidBarangKeluar($barangKeluar, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Data Barang Keluar Berhasil Dihapus (Void) & Ledger Diperbarui!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAutoCompleteData(Request $request)
    {
        $barang = Barang::where('nama_barang', $request->nama_barang)->first();

        if ($barang) {
            return response()->json([
                'nama_barang'   => $barang->nama_barang,
                'stok'          => $barang->stok,
                'satuan_id'     => $barang->satuan_id,
            ]);
        }

        return response()->json(null, 444);
    }

    public function getStok(Request $request)
    {
        $namaBarang = $request->input('nama_barang');
        $barang = Barang::where('nama_barang', $namaBarang)->select('stok', 'satuan_id')->first();

        if (!$barang) {
            return response()->json(['stok' => 0, 'satuan_id' => null]);
        }

        return response()->json([
            'stok'       => $barang->stok,
            'satuan_id'  => $barang->satuan_id
        ]);
    }

    public function getSatuan()
    {
        return response()->json(Satuan::all());
    }

    public function getBarangs(Request $request)
    {
        if ($request->has('q')) {
            $barangs = Barang::where('nama_barang', 'like', '%' . $request->input('q') . '%')->get();
            return response()->json($barangs);
        }

        return response()->json([]);
    }
}
