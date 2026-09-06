<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\StockLedger;
use App\Models\StockMutasi;
use App\Models\StockOpname;
use App\Models\PjuAsset;
use App\Models\LokasiPju;
use App\Models\PemasanganPju;
use App\Models\PencopotanPju;
use App\Models\MaintenancePju;
use App\Models\GaransiPju;
use App\Models\ReturVendor;
use App\Models\Kecamatan;
use App\Models\Tim;
use Illuminate\Database\Eloquent\Builder;

class ReportingService
{
    /**
     * Build Stock Report with filters
     */
    public function getStockReport(array $filters = [])
    {
        $query = Barang::with(['jenis', 'satuan', 'merk', 'watt', 'supplier']);

        if (!empty($filters['jenis_id'])) {
            $query->where('jenis_id', $filters['jenis_id']);
        }
        if (!empty($filters['watt_id'])) {
            $query->where('watt_id', $filters['watt_id']);
        }
        if (!empty($filters['merk_id'])) {
            $query->where('merk_id', $filters['merk_id']);
        }
        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    /**
     * Build Inbound Report with filters
     */
    public function getInboundReport(array $filters = [])
    {
        $query = BarangMasuk::with(['barang.satuan', 'supplier']);

        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggal_masuk', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggal_masuk', '<=', $filters['end_date']);
        }
        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        return $query->latest('tanggal_masuk')->get();
    }

    /**
     * Build Outbound Report with filters
     */
    public function getOutboundReport(array $filters = [])
    {
        $query = BarangKeluar::with(['barang.satuan', 'customer']);

        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggal_keluar', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggal_keluar', '<=', $filters['end_date']);
        }
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        return $query->latest('tanggal_keluar')->get();
    }

    /**
     * Build Mutation Report with filters
     */
    public function getMutationReport(array $filters = [])
    {
        $query = StockMutasi::with(['barang.satuan', 'tujuanLokasi']);

        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggal_mutasi', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggal_mutasi', '<=', $filters['end_date']);
        }

        return $query->latest('tanggal_mutasi')->get();
    }

    /**
     * Build Stock Opname Report with filters
     */
    public function getStockOpnameReport(array $filters = [])
    {
        $query = StockOpname::with(['barang', 'petugas', 'approver']);

        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggal_opname', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggal_opname', '<=', $filters['end_date']);
        }

        return $query->latest('tanggal_opname')->get();
    }

    /**
     * Build Maintenance Report with filters
     */
    public function getMaintenanceReport(array $filters = [])
    {
        $query = MaintenancePju::with(['asset', 'lokasi', 'teknisi']);

        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggal_mulai', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggal_mulai', '<=', $filters['end_date']);
        }
        if (!empty($filters['teknisi_id'])) {
            $query->where('teknisi_id', $filters['teknisi_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest('tanggal_mulai')->get();
    }

    /**
     * Build PJU Installation (Pasang) Report
     */
    public function getPasangReport(array $filters = [])
    {
        $query = PemasanganPju::with(['asset', 'lokasi', 'teknisi']);

        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggal_pasang', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggal_pasang', '<=', $filters['end_date']);
        }

        return $query->latest('tanggal_pasang')->get();
    }

    /**
     * Build PJU Uninstallation (Copot) Report
     */
    public function getCopotReport(array $filters = [])
    {
        $query = PencopotanPju::with(['asset', 'lokasi', 'teknisi']);

        if (!empty($filters['start_date'])) {
            $query->whereDate('tanggal_copot', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->whereDate('tanggal_copot', '<=', $filters['end_date']);
        }

        return $query->latest('tanggal_copot')->get();
    }

    /**
     * Build Warranty Report
     */
    public function getWarrantyReport(array $filters = [])
    {
        $query = GaransiPju::with(['asset', 'supplier']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->get();
    }

    /**
     * Build Vendor Return Report
     */
    public function getReturnReport(array $filters = [])
    {
        $query = ReturVendor::with(['asset', 'supplier']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest('tanggal_retur')->get();
    }

    /**
     * Build Single Item History Report (Traceability)
     */
    public function getItemHistoryReport(int $barangId)
    {
        return StockLedger::where('barang_id', $barangId)
            ->with(['user', 'barang'])
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Build Region Summary Report
     */
    public function getRegionReport()
    {
        return Kecamatan::withCount(['lokasis', 'kelurahans'])->get();
    }

    /**
     * Build Team Performance Summary Report
     */
    public function getTeamReport()
    {
        return Tim::all();
    }
}
