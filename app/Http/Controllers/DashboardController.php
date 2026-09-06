<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\PjuAsset;
use App\Models\MaintenancePju;
use App\Models\GaransiPju;
use App\Models\ReturVendor;
use App\Models\StockOpname;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display role-based dashboard based on user permissions/role.
     */
    public function index()
    {
        $user = auth()->user();
        
        // PENTING: Jangan gunakan $user->role->role karena relasi sudah diganti ke many-to-many (roles)
        $userRole = strtolower($user->getRoleName());

        // Common Data
        $barangCount       = Barang::count();
        $totalStockUnits   = Barang::sum('stok');
        $barangMasukCount  = BarangMasuk::count();
        $barangKeluarCount = BarangKeluar::count();
        $userCount         = User::count();

        // 1. DASHBOARD PIMPINAN (Superadmin / Kepala Gudang)
        $pimpinanData = [];
        if (in_array($userRole, ['superadmin', 'kepala gudang'])) {
            $pimpinanData = [
                'total_stock_units' => $totalStockUnits,
                'maintenance_count' => MaintenancePju::whereIn('status', ['SCHEDULED', 'IN_PROGRESS'])->count(),
                'warranty_count'    => GaransiPju::where('status', 'BERLAKU')->count(),
                'return_count'      => ReturVendor::where('status', 'PROSES_VENDOR')->count(),
                'wilayah_summary'   => Kecamatan::withCount('lokasis')->get(),
                'kpi_active_pju'    => PjuAsset::count() > 0 ? round((PjuAsset::where('status_aset', 'TERPASANG')->count() / PjuAsset::count()) * 100, 1) : 100,
            ];
        }

        // 2. DASHBOARD WAREHOUSE (Admin Gudang)
        $warehouseData = [];
        if (in_array($userRole, ['superadmin', 'admin gudang', 'kepala gudang'])) {
            $warehouseData = [
                'barang_minimum'    => Barang::whereColumn('stok', '<=', 'stok_minimum')->get(),
                'opname_discrepancy'=> StockOpname::where('selisih', '!=', 0)->count(),
            ];
        }

        // 3. DASHBOARD TECHNICIAN (Teknisi)
        $technicianData = [];
        if (in_array($userRole, ['superadmin', 'teknisi'])) {
            $technicianData = [
                'assigned_wo'  => MaintenancePju::where('teknisi_id', $user->id)->where('status', 'SCHEDULED')->count(),
                'active_wo'    => MaintenancePju::where('teknisi_id', $user->id)->where('status', 'IN_PROGRESS')->count(),
                'completed_wo' => MaintenancePju::where('teknisi_id', $user->id)->where('status', 'COMPLETED')->count(),
            ];
        }

        // Monthly Trend
        $barangMasukPerBulan = BarangMasuk::selectRaw('DATE_FORMAT(tanggal_masuk, "%Y-%m") as date, SUM(jumlah_masuk) as total')
            ->groupBy('date')->orderBy('date')->get()
            ->map(fn($d) => (object) ['date' => $d->date, 'total' => (int) $d->total]);

        $barangKeluarPerBulan = BarangKeluar::selectRaw('DATE_FORMAT(tanggal_keluar, "%Y-%m") as date, SUM(jumlah_keluar) as total')
            ->groupBy('date')->orderBy('date')->get()
            ->map(fn($d) => (object) ['date' => $d->date, 'total' => (int) $d->total]);

        $barangMinimum = Barang::whereColumn('stok', '<=', 'stok_minimum')->get();

        return view('dashboard', [
            'role'               => $userRole,
            'barang'             => $barangCount,
            'barangMasuk'        => $barangMasukCount,
            'barangKeluar'       => $barangKeluarCount,
            'user'               => $userCount,
            'barangMasukData'    => $barangMasukPerBulan,
            'barangKeluarData'   => $barangKeluarPerBulan,
            'barangMinimum'      => $barangMinimum,
            'pimpinanData'       => $pimpinanData,
            'warehouseData'      => $warehouseData,
            'technicianData'     => $technicianData,
        ]);
    }
}
