# REPORTING.md
## ERP Lite Hybrid PJU — DISHUB
## Reporting & Role-Based Dashboard Specification

**Version**: 1.0 — CLASS 06
**Last Updated**: 2026-08-17

---

## 1. Role-Based Dashboard Metrics

Dashboard disesuaikan berdasarkan peran pengguna (*Role-Based Metrics*):

| Role / Peran | Metrik & Visualisasi Utama |
|---|---|
| **Pimpinan** (Superadmin / Kepala Gudang) | - Total Stok Units<br>- Jumlah Active Maintenance Work Orders<br>- Status Garansi Berlaku<br>- Retur Vendor Dalam Proses<br>- Grafik Trend Inbound/Outbound Bulanan<br>- Ringkasan Aset per Wilayah (Kecamatan)<br>- KPI Persentase Aset PJU Terpasang |
| **Warehouse** (Admin Gudang) | - Total Barang Master<br>- Barang Mencapai Stok Minimum<br>- Ringkasan Barang Masuk & Keluar<br>- Selisih Stock Opname |
| **Technician** (Teknisi) | - Work Order Ditugaskan (Assigned)<br>- Work Order Sedang Berjalan (Active)<br>- Work Order Selesai (Completed) |

---

## 2. 13 Supported Reports & Formats

Seluruh 13 jenis laporan mendukung format **PDF Stream**, **Excel Export**, dan **Print**:

1. **Stock Report**: Saldo persediaan real-time per barang, kategori, merk, watt, dan satuan.
2. **Inbound Report**: Transaksi barang masuk per periode tanggal dan supplier.
3. **Outbound Report**: Transaksi barang keluar per periode tanggal dan penerima.
4. **Mutation Report**: Mutasi transfer persediaan antar gudang / ke lapangan.
5. **Stock Opname Report**: Histori hitung fisik persediaan dan selisih adjustment.
6. **Maintenance Report**: Laporan work order perbaikan, teknisi, spare part, dan hasil.
7. **Pasang Report**: Rekapitulasi pemasangan aset PJU di titik lokasi.
8. **Copot Report**: Rekapitulasi pencopotan lampu PJU dan alasan copot.
9. **Warranty Report**: Status garansi aset PJU per supplier (`BERLAKU`, `EXPIRING`, `EXPIRED`).
10. **Return Report**: Status retur barang rusak ke vendor (`PROSES_VENDOR`, `SELESAI_GANTI`).
11. **Item History Report**: Penelusuran kronologis ledger dan histori 1 barang presisi.
12. **Region Report**: Rekapitulasi aset PJU dan lokasi per Kecamatan / Kelurahan.
13. **Team Report**: Rekapitulasi kinerja tim operasional Dishub / Subkon.

---

## 3. Granular Filter Options

Laporan dapat difilter secara fleksibel berdasarkan gabungan parameter:
- **Periode**: `start_date`, `end_date`.
- **Wilayah**: `kecamatan_id`, `kelurahan_id`, `lokasi_id`.
- **Atribut Barang**: `jenis_id` (Kategori), `watt_id` (Daya), `merk_id` (Brand), `supplier_id` (Vendor).
- **Status**: Status persediaan (`AKTIF`/`NONAKTIF`), status WO, status garansi, status retur.
