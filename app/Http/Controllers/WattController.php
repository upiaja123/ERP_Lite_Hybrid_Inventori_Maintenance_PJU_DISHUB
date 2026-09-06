<?php

namespace App\Http\Controllers;

use App\Models\Watt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WattController extends Controller
{
    public function index()
    {
        return view('watt.index', [
            'watts' => Watt::all()
        ]);
    }

    public function getData()
    {
        return response()->json([
            'success' => true,
            'data'    => Watt::all()
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nilai_watt' => 'required|numeric|unique:watts,nilai_watt',
            'satuan_daya' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $watt = Watt::create([
            'nilai_watt'  => $request->nilai_watt,
            'satuan_daya' => $request->satuan_daya ?? 'Watt',
            'keterangan'  => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Watt Berhasil Disimpan!',
            'data'    => $watt
        ]);
    }

    public function edit(Watt $watt)
    {
        return response()->json([
            'success' => true,
            'data'    => $watt
        ]);
    }

    public function update(Request $request, Watt $watt)
    {
        $validator = Validator::make($request->all(), [
            'nilai_watt' => 'required|numeric|unique:watts,nilai_watt,' . $watt->id,
            'satuan_daya' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $watt->update([
            'nilai_watt'  => $request->nilai_watt,
            'satuan_daya' => $request->satuan_daya ?? 'Watt',
            'keterangan'  => $request->keterangan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Watt Berhasil Diupdate!',
            'data'    => $watt
        ]);
    }

    public function destroy(Watt $watt)
    {
        $watt->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Watt Berhasil Dihapus!'
        ]);
    }
}
