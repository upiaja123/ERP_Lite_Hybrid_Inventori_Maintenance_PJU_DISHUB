<?php

namespace App\Http\Controllers;

use App\Models\PemasanganPju;
use App\Models\PjuAsset;
use App\Models\LokasiPju;
use App\Models\User;
use App\Services\PjuLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PemasanganPjuController extends Controller
{
    protected PjuLifecycleService $lifecycleService;

    public function __construct(PjuLifecycleService $lifecycleService)
    {
        $this->lifecycleService = $lifecycleService;
    }

    public function index()
    {
        return view('pemasangan-pju.index', [
            'pemasangans' => PemasanganPju::with(['asset', 'lokasi', 'teknisi'])->latest()->get(),
            'assets'      => PjuAsset::whereIn('status_aset', ['GUDANG', 'MAINTENANCE'])->get(),
            'lokasis'     => LokasiPju::all(),
            'teknisis'    => User::all(),
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => PemasanganPju::with(['asset', 'lokasi', 'teknisi'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['admin gudang', 'teknisi']) && !auth()->user()->hasPermissionTo('pju.install')) {
            abort(403, 'Unauthorized action. Hanya Teknisi, Admin Gudang, atau Super Admin yang dapat mencatat pemasangan PJU.');
        }

        $validator = Validator::make($request->all(), [
            'pju_asset_id'   => 'required|exists:pju_assets,id',
            'lokasi_id'      => 'required|exists:lokasi_pjus,id',
            'tanggal_pasang' => 'required|date',
            'teknisi_id'     => 'nullable|exists:users,id',
            'kondisi_pasang' => 'nullable|string',
            'catatan'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $pemasangan = $this->lifecycleService->installAsset(
                $request->all(),
                auth()->id() ?? 1
            );

            return response()->json([
                'success' => true,
                'message' => 'PJU Berhasil Dipasang & Lifecycle Aset Diperbarui ke TERPASANG!',
                'data'    => $pemasangan->load(['asset', 'lokasi', 'teknisi'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
