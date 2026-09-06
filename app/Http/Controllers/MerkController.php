<?php

namespace App\Http\Controllers;

use App\Models\Merk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MerkController extends Controller
{
    public function index()
    {
        return view('merk.index', [
            'merks' => Merk::all()
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => Merk::all()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_merk' => 'required|unique:merks,nama_merk',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $merk = Merk::create([
            'nama_merk' => $request->nama_merk,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Merk Berhasil Disimpan!',
            'data'    => $merk
        ]);
    }

    public function edit(Merk $merk)
    {
        return response()->json([
            'success' => true,
            'data'    => $merk
        ]);
    }

    public function update(Request $request, Merk $merk)
    {
        $validator = Validator::make($request->all(), [
            'nama_merk' => 'required|unique:merks,nama_merk,' . $merk->id,
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $merk->update([
            'nama_merk' => $request->nama_merk,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Merk Berhasil Diupdate!',
            'data'    => $merk
        ]);
    }

    public function destroy(Merk $merk)
    {
        $merk->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Merk Berhasil Dihapus!'
        ]);
    }
}
