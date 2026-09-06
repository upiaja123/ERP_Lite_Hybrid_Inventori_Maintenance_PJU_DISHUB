<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Http\Request;

class BarangApiController extends Controller
{
    public function index(Request $request)
    {
        $limit = min((int) $request->input('limit', 15), 100);

        $barangs = Barang::with(['jenis:id,nama_jenis', 'satuan:id,satuan'])
            ->select('id', 'kode_barang', 'nama_barang', 'stok', 'stok_minimum', 'jenis_id', 'satuan_id', 'status')
            ->paginate($limit);

        return response()->json([
            'status' => 'success',
            'data'   => $barangs,
        ]);
    }

    public function show($id)
    {
        $barang = Barang::with(['jenis', 'satuan', 'merk', 'watt', 'supplier'])->find($id);

        if (!$barang) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Barang tidak ditemukan!',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $barang,
        ]);
    }
}
