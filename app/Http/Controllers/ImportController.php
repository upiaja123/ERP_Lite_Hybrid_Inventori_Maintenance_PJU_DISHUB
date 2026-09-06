<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Jenis;
use App\Models\Satuan;
use App\Models\Supplier;
use App\Services\InventoryService;
use App\Services\ItemCodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    protected InventoryService $inventoryService;
    protected ItemCodeGeneratorService $codeGenerator;

    public function __construct(InventoryService $inventoryService, ItemCodeGeneratorService $codeGenerator)
    {
        $this->inventoryService = $inventoryService;
        $this->codeGenerator = $codeGenerator;
    }

    public function index()
    {
        return view('import.index');
    }

    /**
     * STAGE 1 & 2 & 3: Upload, Preview, and Validate CSV/Excel Staging Data
     */
    public function previewAndValidate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file_import' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $path = $request->file('file_import')->getRealPath();
        $rows = array_map('str_getcsv', file($path));

        if (count($rows) < 2) {
            return response()->json(['message' => 'File CSV kosong atau tidak memiliki header!'], 422);
        }

        $header = array_shift($rows);
        $previewData = [];
        $errorReport = [];
        $validRows = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Line in file (header = line 1)
            $namaBarang   = trim($row[0] ?? '');
            $deskripsi    = trim($row[1] ?? 'Data Impor Excel');
            $namaJenis    = trim($row[2] ?? 'Lampu LED PJU');
            $namaSatuan   = trim($row[3] ?? 'Unit');
            $stokMinimum  = (float) ($row[4] ?? 10);
            $openingStock = (float) ($row[5] ?? 0);
            $barcodeValue = trim($row[6] ?? '');

            $rowErrors = [];

            if (empty($namaBarang)) {
                $rowErrors[] = "Baris {$rowNum}: Nama barang tidak boleh kosong.";
            }

            // Check duplicate barcode if present
            if (!empty($barcodeValue)) {
                if (Barang::where('barcode_value', $barcodeValue)->exists()) {
                    $rowErrors[] = "Baris {$rowNum}: Barcode '{$barcodeValue}' sudah ada di database!";
                }
            }

            if (!empty($rowErrors)) {
                $errorReport[] = [
                    'line'   => $rowNum,
                    'data'   => $row,
                    'errors' => $rowErrors,
                ];
            } else {
                $validRows[] = [
                    'line'          => $rowNum,
                    'nama_barang'   => $namaBarang,
                    'deskripsi'     => $deskripsi,
                    'nama_jenis'    => $namaJenis,
                    'nama_satuan'   => $namaSatuan,
                    'stok_minimum'  => $stokMinimum,
                    'opening_stock' => $openingStock,
                    'barcode_value' => $barcodeValue,
                ];
            }

            if (count($previewData) < 10) {
                $previewData[] = [
                    'line'    => $rowNum,
                    'barang'  => $namaBarang,
                    'jenis'   => $namaJenis,
                    'opening' => $openingStock,
                    'status'  => empty($rowErrors) ? 'VALID' : 'INVALID',
                ];
            }
        }

        // Cache valid rows temporarily for Staging Approval step
        $stagingToken = 'staging_' . uniqid();
        Storage::put("staging/{$stagingToken}.json", json_encode($validRows));

        return response()->json([
            'success'       => true,
            'staging_token' => $stagingToken,
            'total_rows'    => count($rows),
            'valid_count'   => count($validRows),
            'invalid_count' => count($errorReport),
            'preview'       => $previewData,
            'error_report'  => $errorReport,
        ]);
    }

    /**
     * STAGE 4 & 5 & 6: Approve and Import Validated Staging Data to Production DB
     */
    public function approveAndImport(Request $request)
    {
        $stagingToken = $request->input('staging_token');

        if (!$stagingToken || !Storage::exists("staging/{$stagingToken}.json")) {
            return response()->json(['message' => 'Token staging impor tidak ditemukan atau sudah kadaluarsa!'], 422);
        }

        $validRows = json_decode(Storage::get("staging/{$stagingToken}.json"), true);
        $importedCount = 0;

        foreach ($validRows as $row) {
            $jenis  = Jenis::firstOrCreate(['nama_jenis' => $row['nama_jenis']]);
            $satuan = Satuan::firstOrCreate(['satuan' => $row['nama_satuan']]);

            $kodeBarang = $this->codeGenerator->generate('barang', 'barangs', 'kode_barang');

            $barang = Barang::create([
                'kode_barang'   => $kodeBarang,
                'nama_barang'   => $row['nama_barang'],
                'deskripsi'     => $row['deskripsi'],
                'jenis_id'      => $jenis->id,
                'satuan_id'     => $satuan->id,
                'stok_minimum'  => $row['stok_minimum'],
                'barcode_type'  => !empty($row['barcode_value']) ? 'INDIVIDUAL' : 'BATCH',
                'barcode_value' => !empty($row['barcode_value']) ? $row['barcode_value'] : null,
                'user_id'       => auth()->id() ?? 1,
                'stok'          => 0,
            ]);

            // If Opening Stock > 0, post Opening Inbound Ledger
            if ($row['opening_stock'] > 0) {
                $supplier = Supplier::firstOrCreate(['nama_supplier' => 'Saldo Awal Migrasi Excel']);
                $this->inventoryService->recordInbound([
                    'tanggal_masuk'  => date('Y-m-d'),
                    'barang_id'      => $barang->id,
                    'jumlah_masuk'   => $row['opening_stock'],
                    'supplier_id'    => $supplier->id,
                    'kode_transaksi' => 'OPENING-' . $kodeBarang,
                    'no_dokumen'     => 'MIGRASI-EXCEL-' . date('Ymd'),
                    'status'         => 'POSTED',
                ], auth()->id() ?? 1);
            }

            $importedCount++;
        }

        // Cleanup staging cache
        Storage::delete("staging/{$stagingToken}.json");

        // Log Audit Migration Action
        activity()
            ->causedBy(auth()->user())
            ->withProperties(['imported_count' => $importedCount, 'staging_token' => $stagingToken])
            ->log("MIGRASI DATA EXCEL: " . auth()->user()->name . " mengimpor {$importedCount} barang ke Production DB.");

        return response()->json([
            'success'        => true,
            'message'        => "Berhasil Mengimpor {$importedCount} Data Barang & Saldo Awal ke System of Record!",
            'imported_count' => $importedCount,
        ]);
    }
}
