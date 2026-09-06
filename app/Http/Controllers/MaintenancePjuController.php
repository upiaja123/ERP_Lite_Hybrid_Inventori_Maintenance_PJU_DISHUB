<?php

namespace App\Http\Controllers;

use App\Models\MaintenancePju;
use App\Models\KerusakanPju;
use App\Models\PjuAsset;
use App\Models\User;
use App\Services\PjuLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaintenancePjuController extends Controller
{
    protected PjuLifecycleService $lifecycleService;

    public function __construct(PjuLifecycleService $lifecycleService)
    {
        $this->lifecycleService = $lifecycleService;
    }

    public function index()
    {
        return view('maintenance-pju.index', [
            'maintenances' => MaintenancePju::with(['asset', 'lokasi', 'teknisi', 'kerusakan'])->latest()->get(),
            'kerusakans'   => KerusakanPju::whereIn('status', ['DILAPORKAN', 'DIPROSES'])->get(),
            'assets'       => PjuAsset::all(),
            'teknisis'     => User::all(),
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => MaintenancePju::with(['asset', 'lokasi', 'teknisi', 'kerusakan'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['kepala gudang', 'teknisi']) && !auth()->user()->hasPermissionTo('maintenance.create')) {
            abort(403, 'Unauthorized action. Hanya Teknisi, Kepala Gudang, atau Super Admin yang dapat membuat Work Order Maintenance.');
        }

        $validator = Validator::make($request->all(), [
            'pju_asset_id'      => 'required|exists:pju_assets,id',
            'jenis_maintenance' => 'required|in:PREVENTIF,KOREKTIF,DARURAT',
            'tanggal_mulai'     => 'required|date',
            'teknisi_id'        => 'nullable|exists:users,id',
            'kerusakan_id'      => 'nullable|exists:kerusakan_pjus,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $maintenance = $this->lifecycleService->createWorkOrder(
            $request->all(),
            auth()->id() ?? 1
        );

        return response()->json([
            'success' => true,
            'message' => 'Work Order Maintenance Berhasil Dibuat!',
            'data'    => $maintenance->load(['asset', 'lokasi', 'teknisi'])
        ]);
    }

    public function complete(Request $request, MaintenancePju $maintenancePju)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['kepala gudang', 'teknisi']) && !auth()->user()->hasPermissionTo('maintenance.complete')) {
            abort(403, 'Unauthorized action. Hanya Teknisi, Kepala Gudang, atau Super Admin yang dapat menyelesaikan Work Order Maintenance.');
        }

        $validator = Validator::make($request->all(), [
            'hasil'              => 'required|in:BERHASIL,BUTUH_RETUR,GAGAL',
            'tindakan_perbaikan' => 'required|string',
            'tanggal_selesai'    => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $completed = $this->lifecycleService->completeWorkOrder(
            $maintenancePju,
            $request->all(),
            auth()->id() ?? 1
        );

        return response()->json([
            'success' => true,
            'message' => 'Maintenance Work Order Berhasil Selesai & Lifecycle Aset Diperbarui!',
            'data'    => $completed
        ]);
    }
}
