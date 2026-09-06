<?php

namespace App\Http\Controllers;

use App\Models\Kelurahan;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KelurahanController extends Controller
{
    public function index()
    {
        return view('kelurahan.index', [
            'kelurahans' => Kelurahan::with('kecamatan')->get(),
            'kecamatans' => Kecamatan::all(),
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => Kelurahan::with('kecamatan')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kelurahan' => 'required|string',
            'kecamatan_id'   => 'required|exists:kecamatans,id',
            'kode_kelurahan' => 'nullable|string|unique:kelurahans,kode_kelurahan',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kelurahan = Kelurahan::create([
            'nama_kelurahan' => $request->nama_kelurahan,
            'kecamatan_id'   => $request->kecamatan_id,
            'kode_kelurahan' => $request->kode_kelurahan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Kelurahan Berhasil Disimpan!',
            'data'    => $kelurahan->load('kecamatan')
        ]);
    }

    public function edit(Kelurahan $kelurahan)
    {
        return response()->json([
            'success' => true,
            'data'    => $kelurahan
        ]);
    }

    public function update(Request $request, Kelurahan $kelurahan)
    {
        $validator = Validator::make($request->all(), [
            'nama_kelurahan' => 'required|string',
            'kecamatan_id'   => 'required|exists:kecamatans,id',
            'kode_kelurahan' => 'nullable|string|unique:kelurahans,kode_kelurahan,' . $kelurahan->id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kelurahan->update([
            'nama_kelurahan' => $request->nama_kelurahan,
            'kecamatan_id'   => $request->kecamatan_id,
            'kode_kelurahan' => $request->kode_kelurahan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Kelurahan Berhasil Diupdate!',
            'data'    => $kelurahan->load('kecamatan')
        ]);
    }

    public function destroy(Kelurahan $kelurahan)
    {
        $kelurahan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Kelurahan Berhasil Dihapus!'
        ]);
    }
}
