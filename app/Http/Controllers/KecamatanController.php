<?php

namespace App\Http\Controllers;

use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KecamatanController extends Controller
{
    public function index()
    {
        return view('kecamatan.index', [
            'kecamatans' => Kecamatan::with('kelurahans')->get()
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => Kecamatan::with('kelurahans')->get()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kecamatan' => 'required|string|unique:kecamatans,nama_kecamatan',
            'kode_kecamatan' => 'nullable|string|unique:kecamatans,kode_kecamatan',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kecamatan = Kecamatan::create([
            'nama_kecamatan' => $request->nama_kecamatan,
            'kode_kecamatan' => $request->kode_kecamatan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Kecamatan Berhasil Disimpan!',
            'data'    => $kecamatan
        ]);
    }

    public function edit(Kecamatan $kecamatan)
    {
        return response()->json([
            'success' => true,
            'data'    => $kecamatan
        ]);
    }

    public function update(Request $request, Kecamatan $kecamatan)
    {
        $validator = Validator::make($request->all(), [
            'nama_kecamatan' => 'required|string|unique:kecamatans,nama_kecamatan,' . $kecamatan->id,
            'kode_kecamatan' => 'nullable|string|unique:kecamatans,kode_kecamatan,' . $kecamatan->id,
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kecamatan->update([
            'nama_kecamatan' => $request->nama_kecamatan,
            'kode_kecamatan' => $request->kode_kecamatan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Kecamatan Berhasil Diupdate!',
            'data'    => $kecamatan
        ]);
    }

    public function destroy(Kecamatan $kecamatan)
    {
        $kecamatan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Kecamatan Berhasil Dihapus!'
        ]);
    }
}
