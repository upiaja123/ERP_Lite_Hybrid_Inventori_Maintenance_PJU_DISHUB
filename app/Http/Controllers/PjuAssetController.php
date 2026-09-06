<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\LokasiPju;
use App\Models\PjuAsset;
use App\Models\Supplier;
use App\Services\PjuLifecycleService;
use App\Services\ItemCodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PjuAssetController extends Controller
{
    protected PjuLifecycleService $lifecycleService;
    protected ItemCodeGeneratorService $codeGenerator;

    public function __construct(PjuLifecycleService $lifecycleService, ItemCodeGeneratorService $codeGenerator)
    {
        $this->lifecycleService = $lifecycleService;
        $this->codeGenerator = $codeGenerator;
    }

    public function index()
    {
        return view('pju-asset.index', [
            'assets'    => PjuAsset::with(['barang', 'lokasi', 'supplier'])->latest()->get(),
            'barangs'   => Barang::all(),
            'lokasis'   => LokasiPju::all(),
            'suppliers' => Supplier::all(),
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => PjuAsset::with(['barang', 'lokasi', 'supplier'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_pju'        => 'required|string',
            'barang_id'       => 'required|exists:barangs,id',
            'lokasi_id'       => 'nullable|exists:lokasi_pjus,id',
            'no_seri'         => 'nullable|string|unique:pju_assets,no_seri',
            'jenis_lampu'     => 'nullable|string',
            'daya_watt'       => 'nullable|numeric',
            'merk'            => 'nullable|string',
            'supplier_id'     => 'nullable|exists:suppliers,id',
            'tahun_pengadaan' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kodePju = $this->codeGenerator->generate('pju_asset', 'pju_assets', 'kode_pju');

        $asset = PjuAsset::create([
            'kode_pju'        => $kodePju,
            'nama_pju'        => $request->nama_pju,
            'barang_id'       => $request->barang_id,
            'lokasi_id'       => $request->lokasi_id,
            'no_seri'         => $request->no_seri,
            'jenis_lampu'     => $request->jenis_lampu,
            'daya_watt'       => $request->daya_watt,
            'merk'            => $request->merk,
            'supplier_id'     => $request->supplier_id,
            'tahun_pengadaan' => $request->tahun_pengadaan,
            'status_aset'     => $request->lokasi_id ? 'TERPASANG' : 'GUDANG',
            'created_by'      => auth()->id() ?? 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Aset PJU Berhasil Didaftarkan!',
            'data'    => $asset->load(['barang', 'lokasi', 'supplier'])
        ]);
    }

    public function show(PjuAsset $pjuAsset)
    {
        $traceability = $this->lifecycleService->getAssetTraceabilityHistory($pjuAsset->id);

        return response()->json([
            'success'      => true,
            'data'         => $pjuAsset->load(['barang', 'lokasi', 'supplier']),
            'traceability' => $traceability,
        ]);
    }

    public function destroy(PjuAsset $pjuAsset)
    {
        $pjuAsset->delete();

        return response()->json([
            'success' => true,
            'message' => 'Aset PJU Berhasil Dihapus!'
        ]);
    }
}
