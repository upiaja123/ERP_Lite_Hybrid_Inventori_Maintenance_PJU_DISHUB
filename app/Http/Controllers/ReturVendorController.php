<?php

namespace App\Http\Controllers;

use App\Models\ReturVendor;
use App\Models\PjuAsset;
use App\Models\Supplier;
use App\Services\PjuLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReturVendorController extends Controller
{
    protected PjuLifecycleService $lifecycleService;

    public function __construct(PjuLifecycleService $lifecycleService)
    {
        $this->lifecycleService = $lifecycleService;
    }

    public function index()
    {
        return view('retur-vendor.index', [
            'returs'    => ReturVendor::with(['asset', 'supplier'])->latest()->get(),
            'assets'    => PjuAsset::whereIn('status_aset', ['RUSAK', 'RETUR'])->get(),
            'suppliers' => Supplier::all(),
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => ReturVendor::with(['asset', 'supplier'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['admin gudang']) && !auth()->user()->hasPermissionTo('vendor.retur.create')) {
            abort(403, 'Unauthorized action. Hanya Admin Gudang atau Super Admin yang dapat mendaftarkan retur vendor.');
        }

        $validator = Validator::make($request->all(), [
            'pju_asset_id'  => 'required|exists:pju_assets,id',
            'alasan_retur'  => 'required|string',
            'tanggal_retur' => 'required|date',
            'supplier_id'   => 'nullable|exists:suppliers,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $retur = $this->lifecycleService->processVendorReturn(
            $request->all(),
            auth()->id() ?? 1
        );

        return response()->json([
            'success' => true,
            'message' => 'Proses Retur Vendor Berhasil Didaftarkan & Aset Di-set RETUR!',
            'data'    => $retur->load(['asset', 'supplier'])
        ]);
    }

    public function complete(Request $request, ReturVendor $returVendor)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['kepala gudang']) && !auth()->user()->hasPermissionTo('vendor.retur.approve')) {
            abort(403, 'Unauthorized action. Hanya Kepala Gudang atau Super Admin yang berwenang menyelesaikan retur vendor.');
        }

        $validator = Validator::make($request->all(), [
            'tanggal_kembali' => 'required|date',
            'kondisi_kembali' => 'required|string',
            'status'          => 'required|in:SELESAI_GANTI,DITOLAK_VENDOR',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $completed = $this->lifecycleService->completeVendorReturn(
            $returVendor,
            $request->all(),
            auth()->id() ?? 1
        );

        return response()->json([
            'success' => true,
            'message' => 'Retur Vendor Selesai & Aset PJU Dipulihkan ke GUDANG!',
            'data'    => $completed
        ]);
    }
}
