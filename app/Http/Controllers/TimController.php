<?php

namespace App\Http\Controllers;

use App\Models\Tim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TimController extends Controller
{
    public function index()
    {
        return view('tim.index', [
            'tims' => Tim::all()
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => Tim::all()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_tim'         => 'required|unique:tims,kode_tim',
            'nama_tim'         => 'required|string',
            'penanggung_jawab' => 'nullable|string',
            'kontak'           => 'nullable|string',
            'wilayah_tugas'    => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tim = Tim::create([
            'kode_tim'         => $request->kode_tim,
            'nama_tim'         => $request->nama_tim,
            'penanggung_jawab' => $request->penanggung_jawab,
            'kontak'           => $request->kontak,
            'wilayah_tugas'    => $request->wilayah_tugas,
            'status'           => 'AKTIF',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Tim Operasional Berhasil Disimpan!',
            'data'    => $tim
        ]);
    }

    public function edit(Tim $tim)
    {
        return response()->json([
            'success' => true,
            'data'    => $tim
        ]);
    }

    public function update(Request $request, Tim $tim)
    {
        $validator = Validator::make($request->all(), [
            'kode_tim'         => 'required|unique:tims,kode_tim,' . $tim->id,
            'nama_tim'         => 'required|string',
            'penanggung_jawab' => 'nullable|string',
            'kontak'           => 'nullable|string',
            'wilayah_tugas'    => 'nullable|string',
            'status'           => 'required|in:AKTIF,NONAKTIF',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tim->update([
            'kode_tim'         => $request->kode_tim,
            'nama_tim'         => $request->nama_tim,
            'penanggung_jawab' => $request->penanggung_jawab,
            'kontak'           => $request->kontak,
            'wilayah_tugas'    => $request->wilayah_tugas,
            'status'           => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Tim Operasional Berhasil Diupdate!',
            'data'    => $tim
        ]);
    }

    public function destroy(Tim $tim)
    {
        $tim->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Tim Berhasil Dihapus!'
        ]);
    }
}
