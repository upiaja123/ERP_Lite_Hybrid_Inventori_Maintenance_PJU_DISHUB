# BUSINESS_RULES.md
## ERP Lite Hybrid PJU — DISHUB
## Business Rules Document

**Version**: 1.3 — CLASS 05
**Last Updated**: 2026-08-17

---

## BR-001: INVENTORY LEDGER & STOCK FORMULA

### BR-001-01: Stock Calculation Formula
Saldo persediaan tidak pernah diubah secara langsung (`UPDATE barangs SET stok = ...`). Saldo persediaan real-time dihitung dan divalidasi berdasarkan formula konseptual ledger:

$$\text{Current Stock} = \text{Opening} + \text{Inbound} - \text{Outbound} + \text{Transfer In} - \text{Transfer Out} + \text{Adjustment}$$

---

## BR-020: FIELD OPERATIONS & PJU LIFECYCLE RULES

### BR-020-01: Pemasangan PJU (Pasang)
1. **Prasyarat Status Aset**: Aset PJU hanya dapat dipasang jika berstatus `GUDANG` atau `MAINTENANCE`. Aset yang sedang `TERPASANG`, `RUSAK`, atau `RETUR` ditolak oleh sistem.
2. **Validasi Subjek**: Memvalidasi keabsahan item aset (`pju_asset_id`), lokasi terdaftar (`lokasi_id`), dan penugasan teknisi (`teknisi_id`).
3. **Transisi Status**: Setelah pemasangan disetujui, status aset berubah menjadi `TERPASANG` dan `lokasi_id` diperbarui.

### BR-020-02: Pencopotan PJU (Copot)
1. **Prasyarat Status Aset**: Pencopotan hanya dapat dilakukan pada aset PJU yang berstatus `TERPASANG`.
2. **Field Wajib**: Mencatat tanggal pencopotan, alasan pencopotan (`RUSAK_BERAT`, `MAINTENANCE`, `RETUR_VENDOR`, `PEREMAJAAN`), kondisi barang, barcode/serial number, dan teknisi pelaksana.
3. **Transisi Status**:
   - Alasan `MAINTENANCE` $\rightarrow$ Status `MAINTENANCE`
   - Alasan `RETUR_VENDOR` $\rightarrow$ Status `RETUR`
   - Alasan `PEREMAJAAN` $\rightarrow$ Status `GUDANG` (`lokasi_id` di-set NULL)
   - Alasan `RUSAK_BERAT` $\rightarrow$ Status `RUSAK`

### BR-020-03: Work Order Maintenance
1. **Workflow**: `Reported → Assigned → In Progress → Completed → Verified → Closed`.
2. **Pencatatan**: Mencatat Work Order Number, kerusakan acuan, lokasi, teknisi/tim, tindakan perbaikan, spare part yang digunakan (JSON array), foto dokumentasi, dan hasil (`BERHASIL`, `BUTUH_RETUR`, `GAGAL`).

### BR-020-04: Tracking Garansi Vendor (Warranty)
Status garansi dipantau secara otomatis berdasarkan masa berlaku:
- `BERLAKU` / `ACTIVE`: Tanggal saat ini $\le$ tanggal berakhir garansi.
- `EXPIRING`: Sisa garansi $\le 30$ hari.
- `EXPIRED`: Tanggal saat ini $>$ tanggal berakhir garansi.
- `CLAIMED` / `COMPLETED`: Dalam/selesai proses klaim garansi.

### BR-020-05: Retur Vendor (Vendor Return Workflow)
1. **Workflow**: `Draft → Submitted → Approved → Sent → Vendor Received → Inspection → Repair/Replace/Reject → Completed`.
2. **Dipulihkan ke Gudang**: Saat barang retur selesai diperbaiki/diganti oleh vendor (`SELESAI_GANTI`), status aset dipulihkan ke `GUDANG` dengan `lokasi_id = NULL` agar siap dipasang kembali.

### BR-020-06: End-to-End Lifecycle Traceability
Seluruh riwayat perjalanan aset PJU:
$$\text{Barang Masuk} \rightarrow \text{Keluar} \rightarrow \text{Pasang} \rightarrow \text{Rusak} \rightarrow \text{Copot} \rightarrow \text{Warranty/Return} \rightarrow \text{Kembali ke Gudang} \rightarrow \text{Pasang Kembali}$$
dapat ditelusuri secara kronologis dan presisi via `PjuLifecycleService::getAssetTraceabilityHistory()`.
