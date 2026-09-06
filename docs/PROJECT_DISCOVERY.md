# PROJECT_DISCOVERY.md
## ERP Lite Hybrid PJU — DISHUB
## Project Discovery & Inspection Report

**Version**: 1.0 — CLASS 01
**Date**: 2026-08-09
**Status**: PRE-INTERVIEW DISCOVERY COMPLETED

---

## 1. Project Overview & Structure

Workspace dasar berawal dari proyek **Inventory Gudang** yang dikembangkan oleh Ferdy Salsabilla (`ferdy-s/Inventory-Gudang`). Proyek ini ditransformasikan menjadi **ERP Lite Hybrid PJU — DISHUB** untuk Dinas Perhubungan.

### Directory Structure Overview

```
Inventory-Gudang/
├── app/
│   ├── Exceptions/           ← InsufficientStock, Approval, ItemCode Exceptions
│   ├── Http/
│   │   ├── Controllers/      ← Barang, BarangMasuk, BarangKeluar, HakAkses, User, Laporan
│   │   ├── Middleware/       ← CheckRole
│   │   └── Requests/         ← StoreBarangMasukRequest, StoreBarangKeluarRequest
│   ├── Models/               ← Barang, BarangMasuk, BarangKeluar, StockLedger, LokasiPju, PjuAsset, Kecamatan, Kelurahan, User, Role, Supplier, Customer
│   └── Services/             ← InventoryService, ApprovalWorkflowService, ItemCodeGeneratorService, LocationService
├── config/                   ← erp.php, integration.php, app.php, database.php, auth.php
├── database/
│   ├── factories/
│   ├── migrations/           ← Core inventory migrations + stock_ledgers + pju_master_tables
│   └── seeders/              ← DatabaseSeeder
├── docs/                     ← PROJECT_DISCOVERY.md, IMPLEMENTATION_PLAN.md, DECISIONS.md, REQUIREMENTS.md, BUSINESS_RULES.md, ARCHITECTURE.md, PROGRESS.md, CHANGELOG.md
├── public/                   ← Stisla CSS/JS assets, uploads
├── resources/
│   ├── css/ & js/
│   └── views/                ← Blade templates (layouts, barang, barang-masuk, barang-keluar, dashboard, laporan)
├── routes/                   ← web.php, api.php, auth.php
├── storage/                  ← logs, app/public (gambar-barang)
├── tests/                    ← Feature tests, Smoke tests
└── vendor/                   ← Composer dependencies
```

---

## 2. Existing Technology Stack

| Layer | Component / Tool | Version / Spec |
|---|---|---|
| **Backend Framework** | Laravel Framework | v10.8 (target upgrade PHP 8.3 / Laravel 11/12) |
| **PHP Runtime** | PHP | 8.1+ / 8.3 |
| **Database** | MySQL | MySQL 8.x / MariaDB |
| **Frontend Rendering** | Laravel Blade Engine | Stisla Bootstrap 4 Template |
| **UI Framework & Style** | Bootstrap 4 + Custom CSS | Poppins Font, Blue-Purple Gradient Palette |
| **JS Libraries** | jQuery, Select2, DataTables, Chart.js, SweetAlert2, Day.js | Client-side interactivity |
| **Reporting & Export** | DomPDF (`barryvdh/laravel-dompdf`) | PDF stream & print support |
| **Audit Log** | Spatie Activity Log (`spatie/laravel-activitylog`) | Activity logging for models |
| **Authentication** | Laravel Breeze + Sanctum | Session auth & API tokens |

---

## 3. Requirements & Core Business Intent

1. **System of Record**: MySQL adalah satu-satunya sumber kebenaran data persediaan, aset, dan transaksi.
2. **End-to-End PJU Lifecycle**:
   `Pengadaan → Barang Masuk → Gudang → Barang Keluar → Distribusi → Pemasangan → Kerusakan → Pencopotan → Maintenance → Garansi/Retur → Barang Kembali → Pemasangan Kembali`
3. **Transaction Ledger Engine**: Menggantikan pengeditan stok secara langsung (`UPDATE stock SET ...`) dengan `StockLedger` (mencatat `quantity_before`, `quantity_after`, `user_id`, `reference`, `status`).
4. **Dynamic Location Architecture**: Lokasi PJU **TIDAK WAJIB** di-seed lengkap sebelum sistem dipakai, melainkan bertumbuh dinamis dari transaksi/laporan operasional (*on-the-fly*). GPS koordinat bersifat `nullable`/optional.
5. **Configurable Workflows**:
   - Kode barang/PJU dikelola via `ItemCodeGeneratorService` + `config/erp.php` (Q1).
   - Approval multi-level dikelola via `ApprovalWorkflowService` + `config/erp.php` (Q2).
   - Sync Google Sheets dikelola via background scheduler (`SYNC_INTERVAL_MINUTES=60` default) tanpa mengganggu transaksi ERP utama (Q3).

---

## 4. Existing UI / UX Aesthetics Analysis

- **Theme**: Stisla Admin Template (Bootstrap 4).
- **Color Palette**: 
  - Primary Accent: Linear gradient (`#7686ff` → `#9ba8ff` & `#6e73ff` → `#8c9aff`).
  - Cards: Pure white `#ffffff`, `border-radius: 16px`, `box-shadow: 0 4px 14px rgba(0,0,0,0.05)`.
  - Body Background: Light soft blue `#f7f9ff`.
  - Typography: `Poppins`, sans-serif.
- **Konsistensi UI Rule**: Semua modul baru (Aset PJU, Lokasi, Distribusi, Maintenance, Garansi) **WAJIB** memakai layout dan komponen Stisla ini tanpa mengubah tema dasar.

---

## 5. Data Sources & Excel Integration Staging

- **Excel Operasional Dishub**:
  - Berisi kolom: Alamat, Kelurahan, Jenis Lampu, Tiang, Jumlah, Tim, Tanggal, Tanggal Pemasangan, Pelapor.
  - Alur Staging: `Excel → Staging Table → Normalization (Trim/Case) → Validation → Transaction / Master`.
  - Excel asli **TIDAK BOLEH** diubah atau dihapus.

---

## 6. Risk, Ambiguity & Tech Debt Analysis

| ID | Risk / Ambiguity | Severity | Mitigation Strategy |
|---|---|---|---|
| **RA-001** | Transaksi lama (`BarangMasukController`/`BarangKeluarController`) langsung increment/decrement stok tanpa ledger | HIGH | Refactored di CLASS 02 dengan `InventoryService` |
| **RA-002** | Role check existing masih hardcoded string (`role->role === 'superadmin'`) | HIGH | Migrasi ke Spatie Permission dengan granular permission |
| **RA-003** | Format Kode PJU resmi Dishub belum ditentukan (Q1) | MEDIUM | `ItemCodeGeneratorService` membaca konfigurasi (PRPTY-XXXXX dummy placeholder) |
| **RA-004** | Jumlah level & approver final belum ditentukan (Q2) | MEDIUM | Engine approval dibuat configurable multi-level |
| **RA-005** | Potensi duplikasi alamat lokasi PJU karena beda spasi/huruf kapital | MEDIUM | Normalisasi string (`LOWER(TRIM(alamat_jalan))`) di `LocationService` |
| **RA-006** | Google API downtime / internet disruption | LOW | Scheduled job async queue; kegagalan sync tidak membatalkan transaksi ERP |

---

## 7. Recommendations for Next Classes

1. **Persiapkan Wawancara Besok**: Gunakan 12 checklist pertanyaan di `docs/DECISIONS.md`.
2. **Lanjutkan ke CLASS 04 (Distribusi & Pemasangan)**: Mengimplementasikan workflow pengiriman dari Gudang ke Lokasi PJU dan penugasan teknisi.
3. **Persiapkan Seeder Master Wilayah**: Setelah list resmi Kecamatan/Kelurahan Dishub diperoleh pasca-wawancara, jalankan seeder.
