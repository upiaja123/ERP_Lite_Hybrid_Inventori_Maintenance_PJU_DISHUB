<?php

namespace App\Http\Controllers;

use App\Models\Jenis;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Merk;
use App\Models\Watt;
use App\Models\Supplier;
use App\Services\ItemCodeGeneratorService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class BarangController extends Controller
{
    protected ItemCodeGeneratorService $codeGenerator;

    public function __construct(ItemCodeGeneratorService $codeGenerator)
    {
        $this->codeGenerator = $codeGenerator;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('barang.index', [
            'barangs'         => Barang::with(['jenis', 'satuan', 'merk', 'watt', 'supplier'])->latest()->get(),
            'jenis_barangs'   => Jenis::all(),
            'satuans'         => Satuan::all(),
            'merks'           => Merk::all(),
            'watts'           => Watt::all(),
            'suppliers'       => Supplier::all(),
        ]);
    }

    public function getDataBarang()
    {
        try {
            $barangs = Barang::with(['jenis', 'satuan', 'merk', 'watt', 'supplier'])->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $barangs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('barang.create', [
            'jenis_barangs' => Jenis::all(),
            'satuans'       => Satuan::all(),
            'merks'         => Merk::all(),
            'watts'         => Watt::all(),
            'suppliers'     => Supplier::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['admin gudang', 'kepala gudang']) && !auth()->user()->hasPermissionTo('inventory.create')) {
            abort(403, 'Unauthorized action. Hanya Admin Gudang atau Super Admin yang dapat menambahkan barang.');
        }

        $rules = [
            'nama_barang'        => 'required|string',
            'deskripsi'          => 'required|string',
            'gambar'             => 'required|array|max:20',
            'gambar.*'           => 'image|mimes:jpeg,png,jpg|max:2048',
            'stok_minimum'       => 'required|numeric|min:0',
            'jenis_id'           => 'required|exists:jenis,id',
            'satuan_id'          => 'required|exists:satuans,id',
            'merk_id'            => 'nullable|exists:merks,id',
            'watt_id'            => 'nullable|exists:watts,id',
            'supplier_id'        => 'nullable|exists:suppliers,id',
            'barcode_type'       => 'required|in:INDIVIDUAL,BATCH',
            'barcode_value'      => 'nullable|string',
            'tanggal_pengadaan'  => 'nullable|date',
            'masa_garansi_bulan' => 'nullable|numeric|min:0',
            'status'             => 'nullable|in:AKTIF,NONAKTIF',
        ];

        // Validasi keunikan barcode jika tipe INDIVIDUAL
        if ($request->barcode_type === 'INDIVIDUAL' && $request->filled('barcode_value')) {
            $rules['barcode_value'] = 'required|string|unique:barangs,barcode_value';
        }

        $validator = Validator::make($request->all(), $rules, [
            'nama_barang.required'   => 'Nama barang wajib diisi!',
            'deskripsi.required'     => 'Deskripsi wajib diisi!',
            'gambar.required'        => 'Upload minimal 1 gambar!',
            'jenis_id.required'      => 'Pilih jenis/kategori barang!',
            'satuan_id.required'     => 'Pilih satuan barang!',
            'barcode_value.unique'   => 'Barcode individual sudah digunakan!',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $images = [];

        if ($request->hasFile('gambar')) {
            foreach ($request->file('gambar') as $file) {
                $path = $file->store('gambar-barang', 'public');
                $images[] = $path;
            }
        }

        // Configurable Item Code Generator
        $kode_barang = $this->codeGenerator->generate('barang', 'barangs', 'kode_barang');

        $barang = Barang::create([
            'nama_barang'        => $request->nama_barang,
            'deskripsi'          => $request->deskripsi,
            'user_id'            => auth()->id() ?? 1,
            'kode_barang'        => $kode_barang,
            'gambar'             => json_encode($images),
            'stok_minimum'       => $request->stok_minimum,
            'jenis_id'           => $request->jenis_id,
            'satuan_id'          => $request->satuan_id,
            'merk_id'            => $request->merk_id,
            'watt_id'            => $request->watt_id,
            'supplier_id'        => $request->supplier_id,
            'barcode_type'       => $request->barcode_type ?? 'BATCH',
            'barcode_value'      => $request->barcode_value,
            'tanggal_pengadaan'  => $request->tanggal_pengadaan,
            'masa_garansi_bulan' => $request->masa_garansi_bulan ?? 12,
            'status'             => $request->status ?? 'AKTIF',
            'stok'               => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Barang Berhasil Disimpan!',
            'data'    => $barang->load(['jenis', 'satuan', 'merk', 'watt', 'supplier'])
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Barang $barang)
    {
        $barang->load(['jenis', 'satuan', 'merk', 'watt', 'supplier', 'pjuAssets']);

        return response()->json([
            'success' => true,
            'data'    => $barang
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $barang)
    {
        $barang->load(['jenis', 'satuan', 'merk', 'watt', 'supplier']);

        return response()->json([
            'success' => true,
            'message' => 'Edit Data Barang',
            'data'    => $barang
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $barang)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['admin gudang', 'kepala gudang']) && !auth()->user()->hasPermissionTo('inventory.update')) {
            abort(403, 'Unauthorized action. Hanya Admin Gudang atau Super Admin yang dapat mengubah data barang.');
        }

        $rules = [
            'nama_barang'        => 'required|string',
            'deskripsi'          => 'required|string',
            'gambar'             => 'nullable|array|max:20',
            'gambar.*'           => 'image|mimes:jpeg,png,jpg|max:2048',
            'stok_minimum'       => 'required|numeric|min:0',
            'jenis_id'           => 'required|exists:jenis,id',
            'satuan_id'          => 'required|exists:satuans,id',
            'merk_id'            => 'nullable|exists:merks,id',
            'watt_id'            => 'nullable|exists:watts,id',
            'supplier_id'        => 'nullable|exists:suppliers,id',
            'barcode_type'       => 'required|in:INDIVIDUAL,BATCH',
            'barcode_value'      => 'nullable|string',
            'tanggal_pengadaan'  => 'nullable|date',
            'masa_garansi_bulan' => 'nullable|numeric|min:0',
            'status'             => 'required|in:AKTIF,NONAKTIF',
        ];

        // Validasi keunikan barcode jika tipe INDIVIDUAL
        if ($request->barcode_type === 'INDIVIDUAL' && $request->filled('barcode_value')) {
            $rules['barcode_value'] = 'required|string|unique:barangs,barcode_value,' . $barang->id;
        }

        $validator = Validator::make($request->all(), $rules, [
            'nama_barang.required' => 'Form Nama Barang Wajib Di Isi !',
            'deskripsi.required'   => 'Form Deskripsi Wajib Di Isi !',
            'jenis_id.required'    => 'Pilih Jenis Barang!',
            'satuan_id.required'   => 'Pilih Satuan Barang!',
            'barcode_value.unique' => 'Barcode individual sudah digunakan!',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $oldImages = [];

        if ($barang->gambar) {
            $decoded = json_decode($barang->gambar, true);
            if (is_array($decoded)) {
                $oldImages = $decoded;
            } else {
                $oldImages = [$barang->gambar];
            }
        }
        $images = $oldImages;

        if ($request->hasFile('gambar')) {
            if (!empty($oldImages)) {
                foreach ($oldImages as $img) {
                    if (Storage::exists('public/' . $img)) {
                        Storage::delete('public/' . $img);
                    }
                }
            }

            $images = [];

            foreach ($request->file('gambar') as $file) {
                $path = $file->store('gambar-barang', 'public');
                $images[] = $path;
            }
        }

        $barang->update([
            'nama_barang'        => $request->nama_barang,
            'stok_minimum'       => $request->stok_minimum,
            'deskripsi'          => $request->deskripsi,
            'user_id'            => auth()->id() ?? 1,
            'gambar'             => json_encode($images),
            'jenis_id'           => $request->jenis_id,
            'satuan_id'          => $request->satuan_id,
            'merk_id'            => $request->merk_id,
            'watt_id'            => $request->watt_id,
            'supplier_id'        => $request->supplier_id,
            'barcode_type'       => $request->barcode_type,
            'barcode_value'      => $request->barcode_value,
            'tanggal_pengadaan'  => $request->tanggal_pengadaan,
            'masa_garansi_bulan' => $request->masa_garansi_bulan ?? 12,
            'status'             => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data Barang Berhasil Diupdate!',
            'data'    => $barang->load(['jenis', 'satuan', 'merk', 'watt', 'supplier'])
        ]);
    }

    /**
     * Remove or Deactivate the specified resource.
     */
    public function destroy($id)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasRole(['admin gudang', 'kepala gudang']) && !auth()->user()->hasPermissionTo('inventory.delete')) {
            abort(403, 'Unauthorized action. Hanya Admin Gudang atau Super Admin yang dapat menghapus barang.');
        }

        try {
            $barang = Barang::find($id);

            if (!$barang) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }

            $barang->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data Barang Berhasil Dihapus!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function cetakPdf($id)
    {
        $item = Barang::with(['jenis', 'satuan', 'merk', 'watt', 'supplier', 'lastBarangMasuk.supplier'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.barang', compact('item'));

        return $pdf->stream('detail-barang.pdf');
    }
}
