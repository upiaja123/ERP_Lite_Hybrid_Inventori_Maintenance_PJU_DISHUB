<?php

namespace App\Http\Controllers;

use App\Models\PencopotanPju;
use App\Models\PjuAsset;
use App\Models\LokasiPju;
use App\Models\User;
use App\Services\PjuLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PencopotanPjuController extends Controller
{
    protected PjuLifecycleService $lifecycleService;

    public function __construct(PjuLifecycleService $lifecycleService)
    {
        $this->lifecycleService = $lifecycleService;
    }

    public function index()
    {
        return view('pencopotan-pju.index', [
            'pencopotans' => PencopotanPju::with(['asset', 'lokasi', 'teknisi'])->latest()->get(),
            'assets'      => PjuAsset::where('status_aset', 'TERPASANG')->get(),
            'lokasis'     => LokasiPju::all(),
            'teknisis'    => User::all(),
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => PencopotanPju::with(['asset', 'lokasi', 'teknisi'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['admin gudang', 'teknisi']) && !auth()->user()->hasPermissionTo('pju.install')) {
            abort(403, 'Unauthorized action. Hanya Teknisi, Admin Gudang, atau Super Admin yang dapat mencatat pencopotan PJU.');
        }

        $validator = Validator::make($request->all(), [
            'pju_asset_id'   => 'required|exists:pju_assets,id',
            'tanggal_copot'  => 'required|date',
            'alasan'         => 'required|in:RUSAK_BERAT,MAINTENANCE,RETUR_VENDOR,PEREMAJAAN',
            'kondisi_barang' => 'required|string',
            'teknisi_id'     => 'nullable|exists:users,id',
            'catatan'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $pencopotan = $this->lifecycleService->uninstallAsset(
                $request->all(),
                auth()->id() ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'PJU Berhasil Dicopot & Lifecycle Aset Diperbarui!',
                'data'    => $pencopotan->load(['asset', 'lokasi', 'teknisi'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
