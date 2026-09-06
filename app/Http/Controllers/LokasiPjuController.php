<?php

namespace App\Http\Controllers;

use App\Models\LokasiPju;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Services\LocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LokasiPjuController extends Controller
{
    protected LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function index()
    {
        return view('lokasi-pju.index', [
            'lokasis'    => LokasiPju::with(['kecamatan', 'kelurahan'])->latest()->get(),
            'kecamatans' => Kecamatan::all(),
            'kelurahans' => Kelurahan::all(),
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => LokasiPju::with(['kecamatan', 'kelurahan'])->latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alamat_jalan'    => 'required|string',
            'nama_lokasi'     => 'nullable|string',
            'kecamatan_id'    => 'nullable|exists:kecamatans,id',
            'kelurahan_id'    => 'nullable|exists:kelurahans,id',
            'pole_number'     => 'nullable|string',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'tim_operasional' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $lokasi = $this->locationService->findOrCreateLocation(
            $request->all(),
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Data Lokasi PJU Berhasil Disimpan!',
            'data'    => $lokasi->load(['kecamatan', 'kelurahan'])
        ]);
    }

    public function show(LokasiPju $lokasiPju)
    {
        $lokasiPju->load(['kecamatan', 'kelurahan', 'assets']);

        return response()->json([
            'success' => true,
            'data'    => $lokasiPju
        ]);
    }

    public function edit(LokasiPju $lokasiPju)
    {
        return response()->json([
            'success' => true,
            'data'    => $lokasiPju
        ]);
    }

    public function update(Request $request, LokasiPju $lokasiPju)
    {
        $validator = Validator::make($request->all(), [
            'alamat_jalan'    => 'required|string',
            'nama_lokasi'     => 'nullable|string',
            'kecamatan_id'    => 'nullable|exists:kecamatans,id',
            'kelurahan_id'    => 'nullable|exists:kelurahans,id',
            'pole_number'     => 'nullable|string',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'tim_operasional' => 'nullable|string',
            'status'          => 'required|in:AKTIF,NONAKTIF,PERBAIKAN',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $lat = $request->filled('latitude') ? (float) $request->latitude : null;
        $lng = $request->filled('longitude') ? (float) $request->longitude : null;

        $mapsUrl = null;
        if ($lat && $lng) {
            $mapsUrl = "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
        }

        $lokasiPju->update([
            'nama_lokasi'     => $request->nama_lokasi,
            'alamat_jalan'    => trim($request->alamat_jalan),
            'kecamatan_id'    => $request->kecamatan_id,
            'kelurahan_id'    => $request->kelurahan_id,
            'pole_number'     => $request->pole_number,
            'latitude'        => $lat,
            'longitude'       => $lng,
            'google_maps_url' => $mapsUrl,
            'tim_operasional' => $request->tim_operasional,
            'status'          => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Lokasi PJU Berhasil Diupdate!',
            'data'    => $lokasiPju->load(['kecamatan', 'kelurahan'])
        ]);
    }

    public function destroy(LokasiPju $lokasiPju)
    {
        $lokasiPju->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Lokasi PJU Berhasil Dihapus!'
        ]);
    }
}
