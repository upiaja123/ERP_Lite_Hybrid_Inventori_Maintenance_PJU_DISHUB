<?php

namespace App\Http\Controllers;

use App\Models\GaransiPju;
use App\Models\PjuAsset;
use App\Models\Supplier;
use App\Services\PjuLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GaransiPjuController extends Controller
{
    protected PjuLifecycleService $lifecycleService;

    public function __construct(PjuLifecycleService $lifecycleService)
    {
        $this->lifecycleService = $lifecycleService;
    }

    public function index()
    {
        return view('garansi-pju.index', [
            'garansis'  => GaransiPju::with(['asset', 'supplier'])->latest()->get(),
            'assets'    => PjuAsset::all(),
            'suppliers' => Supplier::all(),
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => GaransiPju::with(['asset', 'supplier'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pju_asset_id'     => 'required|exists:pju_assets,id',
            'supplier_id'      => 'required|exists:suppliers,id',
            'tanggal_mulai'    => 'required|date',
            'tanggal_berakhir' => 'required|date|after_or_equal:tanggal_mulai',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $garansi = $this->lifecycleService->registerWarranty($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data Garansi PJU Berhasil Didaftarkan!',
            'data'    => $garansi->load(['asset', 'supplier'])
        ]);
    }
}
