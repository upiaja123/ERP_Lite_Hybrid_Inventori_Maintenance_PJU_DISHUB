# REQUIREMENTS.md
## ERP Lite Hybrid PJU — DISHUB
## System Requirements Document

**Version**: 1.0 — CLASS 01
**Status**: DRAFT — Partial Client Confirmation
**Last Updated**: 2026-08-09

---

## 1. Tujuan Sistem

Membangun sistem digital untuk menggantikan proses manual berbasis Excel di Dinas Perhubungan (Dishub) untuk pengelolaan:

- Inventori PJU (Penerangan Jalan Umum)
- Maintenance & Work Order
- Garansi & Retur Vendor
- Pelaporan & Transparansi

---

## 2. Scope Sistem

### 2.1 Lifecycle PJU yang harus tercakup

```
Pengadaan
    → Barang Masuk (ke Gudang)
    → Gudang (stok tersimpan)
    → Barang Keluar (distribusi)
    → Distribusi (pengiriman ke lokasi)
    → Pemasangan (instalasi di tiang)
    → Kerusakan (laporan kerusakan)
    → Pencopotan (dilepas dari tiang)
    → Maintenance (perbaikan/penggantian)
    → Garansi / Retur Vendor (jika dalam garansi)
    → Barang Kembali (kembali ke gudang)
    → Pemasangan kembali (re-install)
```

### 2.2 Yang BUKAN scope sistem

- Google Sheets sebagai database utama
- GIS/peta (FUTURE — tidak MVP)
- Barcode scanner integration (NEEDS_CLIENT_CONFIRMATION)

---

## 3. Functional Requirements

### FR-001: Manajemen Barang/Item

| ID | Requirement | Status |
|---|---|---|
| FR-001-01 | Sistem dapat menyimpan data barang/item PJU | CONFIRMED |
| FR-001-02 | Setiap barang memiliki kode unik yang configurable | CONFIRMED |
| FR-001-03 | Barang dapat dikategorikan berdasarkan jenis | CONFIRMED |
| FR-001-04 | Barang memiliki satuan (unit, set, buah, dll.) | NEEDS_CLIENT_CONFIRMATION (definisi satuan) |
| FR-001-05 | Barang dapat memiliki gambar/foto | CONFIRMED |
| FR-001-06 | Barang memiliki stok minimum alert | CONFIRMED |

### FR-002: Inventori Ledger

| ID | Requirement | Status |
|---|---|---|
| FR-002-01 | Setiap perubahan stok harus tercatat dalam ledger | CONFIRMED |
| FR-002-02 | Stok tidak boleh diubah langsung tanpa transaction source | CONFIRMED |
| FR-002-03 | Ledger mencatat: quantity before, quantity after, user, timestamp | CONFIRMED |
| FR-002-04 | Stok tidak boleh negatif | CONFIRMED |
| FR-002-05 | Saldo awal stok | NEEDS_CLIENT_CONFIRMATION |

### FR-003: Barang Masuk (Inbound)

| ID | Requirement | Status |
|---|---|---|
| FR-003-01 | Pencatatan barang masuk dari supplier/vendor | CONFIRMED |
| FR-003-02 | Referensi nomor dokumen pengadaan | CONFIRMED |
| FR-003-03 | Approval workflow (configurable levels) | NEEDS_CLIENT_CONFIRMATION (level & approver) |
| FR-003-04 | Status transaksi: DRAFT → SUBMITTED → APPROVED → POSTED | CONFIRMED |
| FR-003-05 | Void/pembatalan transaksi dengan alasan | CONFIRMED |
| FR-003-06 | Audit trail lengkap | CONFIRMED |

### FR-004: Barang Keluar (Outbound/Distribusi)

| ID | Requirement | Status |
|---|---|---|
| FR-004-01 | Pencatatan barang keluar untuk distribusi/pemasangan | CONFIRMED |
| FR-004-02 | Validasi stok tersedia sebelum keluar | CONFIRMED |
| FR-004-03 | Approval workflow (configurable) | NEEDS_CLIENT_CONFIRMATION |
| FR-004-04 | Referensi ke distribusi/teknisi | CONFIRMED |

### FR-005: Aset PJU

| ID | Requirement | Status |
|---|---|---|
| FR-005-01 | Setiap unit PJU dapat ditrack sebagai individual asset | CONFIRMED |
| FR-005-02 | Status aset: gudang/terpasang/rusak/maintenance/retur/nonaktif | CONFIRMED |
| FR-005-03 | Relasi ke lokasi pemasangan | CONFIRMED |
| FR-005-04 | Informasi garansi vendor | CONFIRMED |
| FR-005-05 | Nomor seri/identifikasi unik | CONFIRMED |

### FR-006: Lokasi PJU

| ID | Requirement | Status |
|---|---|---|
| FR-006-01 | Lokasi PJU mencakup: kecamatan, kelurahan, jalan, tiang | CONFIRMED |
| FR-006-02 | Normalisasi: kecamatan → kelurahan → lokasi → tiang | CONFIRMED |
| FR-006-03 | GPS koordinat (latitude/longitude) | NEEDS_CLIENT_CONFIRMATION — nullable/future-ready |
| FR-006-04 | Nomor tiang (pole_number) | CONFIRMED — nullable, uniqueness NEEDS_CLIENT_CONFIRMATION |
| FR-006-05 | Data master kecamatan/kelurahan resmi | NEEDS_CLIENT_CONFIRMATION |

### FR-007: Distribusi & Pemasangan

| ID | Requirement | Status |
|---|---|---|
| FR-007-01 | Pencatatan distribusi dari gudang ke lokasi | CONFIRMED |
| FR-007-02 | Penugasan teknisi | CONFIRMED |
| FR-007-03 | Pencatatan pemasangan (instalasi) | CONFIRMED |
| FR-007-04 | Foto dokumentasi pemasangan | CONFIRMED |
| FR-007-05 | Status distribusi: pending/dikirim/terpasang/batal | CONFIRMED |

### FR-008: Kerusakan & Maintenance

| ID | Requirement | Status |
|---|---|---|
| FR-008-01 | Laporan kerusakan PJU | CONFIRMED |
| FR-008-02 | Work Order maintenance | CONFIRMED |
| FR-008-03 | Pencatatan pencopotan PJU | CONFIRMED |
| FR-008-04 | Tracking spare part yang digunakan | CONFIRMED |
| FR-008-05 | Teknisi assignment | CONFIRMED |

### FR-009: Garansi & Retur Vendor

| ID | Requirement | Status |
|---|---|---|
| FR-009-01 | Tracking masa garansi per aset | CONFIRMED |
| FR-009-02 | Alert garansi mendekati kadaluarsa | CONFIRMED |
| FR-009-03 | Proses retur ke vendor | CONFIRMED |
| FR-009-04 | Tracking barang kembali dari vendor | CONFIRMED |
| FR-009-05 | Masa garansi default per vendor/produk | NEEDS_CLIENT_CONFIRMATION |

### FR-010: Pelaporan

| ID | Requirement | Status |
|---|---|---|
| FR-010-01 | Laporan stok PDF & Excel | CONFIRMED |
| FR-010-02 | Laporan barang masuk/keluar | CONFIRMED |
| FR-010-03 | Laporan lifecycle PJU | CONFIRMED |
| FR-010-04 | Laporan maintenance | CONFIRMED |
| FR-010-05 | Sinkronisasi ke Google Sheets | CONFIRMED (scheduled) |
| FR-010-06 | Dataset yang boleh disync ke Google Sheets | NEEDS_CLIENT_CONFIRMATION |

### FR-011: RBAC & Keamanan

| ID | Requirement | Status |
|---|---|---|
| FR-011-01 | Role-Based Access Control granular | CONFIRMED |
| FR-011-02 | Permission per fitur (view/create/update/approve/void/export) | CONFIRMED |
| FR-011-03 | Audit log semua aksi penting | CONFIRMED |
| FR-011-04 | Rate limiting API | CONFIRMED |
| FR-011-05 | HTTPS production | CONFIRMED |

---

## 4. Non-Functional Requirements

| ID | Requirement | Target |
|---|---|---|
| NFR-001 | Performance: halaman load < 3 detik | Normal load |
| NFR-002 | Ketersediaan: uptime > 99% | Production |
| NFR-003 | Keamanan: OWASP Top 10 compliance | Required |
| NFR-004 | Audit: setiap transaksi penting tercatat | Required |
| NFR-005 | Google Sheets failure tidak mempengaruhi ERP | Required |
| NFR-006 | Concurrent stok update safe | Required |

---

## 5. RBAC Role Matrix (Minimum)

| Fitur | Super Admin | System Admin | Admin Gudang | Warehouse Manager | Teknisi | Dept Manager | Viewer |
|---|---|---|---|---|---|---|---|
| Manajemen User | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Data Master | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Barang Masuk | ✅ | ✅ | ✅ | View | ❌ | View | View |
| Approval | ✅ | ✅ | ❌ | ✅ | ❌ | ✅ | ❌ |
| Barang Keluar | ✅ | ✅ | ✅ | View | ❌ | View | View |
| PJU Asset | ✅ | ✅ | ✅ | ✅ | View | View | View |
| Distribusi | ✅ | ✅ | ✅ | ✅ | ✅ | View | View |
| Pemasangan | ✅ | ✅ | ❌ | View | ✅ | View | View |
| Maintenance | ✅ | ✅ | ❌ | View | ✅ | View | View |
| Garansi/Retur | ✅ | ✅ | ✅ | ✅ | ❌ | View | View |
| Laporan | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ |
| Export | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |

*Role final mengikuti konfirmasi client.*

---

## 6. Technology Stack (Confirmed)

```
Backend:        Laravel 10 (existing) → upgrade path ke 11/12 jika diperlukan
PHP:            8.1+ (existing) → target 8.3
Database:       MySQL
Frontend:       Laravel Blade + Stisla Bootstrap Template
Auth:           Laravel Breeze (existing) + Spatie Permission
Reporting:      barryvdh/laravel-dompdf + maatwebsite/excel
Activity Log:   spatie/laravel-activitylog (existing)
Queue:          Laravel Queue (database driver, Redis jika diperlukan)
API:            REST API /api/v1
```

---

*Dokumen ini diperbarui setiap ada requirement baru atau konfirmasi client.*
