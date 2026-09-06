# ROLE PERMISSION MATRIX & AUTHORIZATION SPECIFICATION
## ERP Lite Hybrid Inventori & Maintenance PJU — DISHUB

**Version**: 2.0 (Post-Audit & Hardening)  
**Date**: 2026-09-06  
**Status**: APPROVED & TESTED  

---

## 1. Definisi Role Sistem

1. **Super Admin (`superadmin`)**:
   Pemegang kendali tertinggi seluruh sistem, manajemen otentikasi, konfigurasi RBAC, audit log, persetujuan impor/migrasi data, serta bypass Gate untuk penanganan darurat.
2. **Kepala Gudang / Warehouse Manager (`kepala gudang`)**:
   Penanggung jawab operasional gudang, verifikator persetujuan Stock Opname, pelaksana retur vendor selesai, pengawas Work Order maintenance, inspektur audit trail, dan monitoring laporan.
3. **Admin Gudang (`admin gudang`)**:
   Operator persediaan harian yang berwenang mengelola Master Data Barang, mencatat transaksi Barang Masuk, Barang Keluar, fisik Stock Opname, Mutasi Stok, dan inisiasi retur vendor.
4. **Teknisi (`teknisi`)**:
   Petugas lapangan yang bertugas mencatat instalasi pemasangan PJU, pencopotan PJU di tiang/lokasi, serta mencatat dan menyelesaikan Work Order perbaikan (maintenance).
5. **Viewer / Pengawas Eksternal (`viewer`)**:
   Pihak berwenang (Kepala Bidang / Auditor / Tim Transparansi) yang memiliki hak akses murni baca (read-only) untuk dashboard, monitoring aset, dan seluruh laporan rekonsiliasi.

---

## 2. Matriks Otorisasi Fitur per Role (CRUD Matrix)

| Modul / Fitur | Super Admin | Kepala Gudang | Admin Gudang | Teknisi | Viewer | Catatan Bisnis & Penegakan |
|---|:---:|:---:|:---:|:---:|:---:|---|
| **Manajemen Pengguna** (`/data-pengguna`) | ✅ CRUD | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | Isolasi penuh, hanya Superadmin |
| **Hak Akses & Role** (`/hak-akses`) | ✅ CRUD | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | Isolasi penuh, hanya Superadmin |
| **Audit Trail / Log** (`/aktivitas-user`) | ✅ VIEW | ✅ VIEW | ❌ 403 | ❌ 403 | ❌ 403 | Immutable log (DELETE ditolak 403) |
| **Import & Migrasi Excel** (`/import`) | ✅ FULL | ✅ APPROVE | ❌ 403 | ❌ 403 | ❌ 403 | Tahap verifikasi & commit data migrasi |
| **Dashboard Sistem** (`/dashboard`, `/`) | ✅ FULL | ✅ ISOLATED | ✅ ISOLATED | ✅ ISOLATED | ✅ VIEW | Widget statistik terisolasi per peran |
| **Master Barang** (`/barang`) | ✅ CRUD | ✅ CRUD | ✅ CRUD | 👁️ READ | 👁️ READ | UI & API menolak Create/Edit/Delete teknisi/viewer |
| **Master Jenis & Satuan** | ✅ CRUD | ✅ CRUD | ✅ CRUD | 👁️ READ | 👁️ READ | Standarisasi klasifikasi persediaan |
| **Spesifikasi (Merk, Watt)** | ✅ CRUD | ✅ CRUD | ✅ CRUD | 👁️ READ | 👁️ READ | Master wajib watt 20W s.d. 120W |
| **Wilayah (Kec., Kel., Lokasi)** | ✅ CRUD | ✅ CRUD | ✅ CRUD | 👁️ READ | 👁️ READ | Basis geospasial & titik tiang PJU |
| **Mitra (Supplier, Customer)** | ✅ CRUD | ✅ CRUD | ✅ CRUD | 👁️ READ | 👁️ READ | Rekanan vendor & unit penerima |
| **Barang Masuk** (`/barang-masuk`) | ✅ CREATE/VOID | 👁️ READ/VOID | ✅ CREATE/VOID | ❌ 403 | 👁️ READ | Posting otomatis ke Ledger Stok |
| **Barang Keluar** (`/barang-keluar`) | ✅ CREATE/VOID | 👁️ READ/VOID | ✅ CREATE/VOID | ❌ 403 | 👁️ READ | Validasi stok fisik & cegah saldo negatif |
| **Stock Opname (Catat Fisik)** | ✅ CREATE | 👁️ READ | ✅ CREATE | ❌ 403 | 👁️ READ | Perekaman selisih sistem vs fisik |
| **Stock Opname (Approval)** | ✅ APPROVE | ✅ APPROVE | ❌ 403 | ❌ 403 | ❌ 403 | Pembuat tidak boleh approve sendiri |
| **Mutasi Stok** (`/stock-mutasi`) | ✅ CREATE | 👁️ READ | ✅ CREATE | ❌ 403 | 👁️ READ | Perpindahan antar gudang / status |
| **Aset PJU Individual** (`/pju-asset`) | ✅ CRUD | ✅ CRUD | ✅ CRUD | 👁️ READ | 👁️ READ | Penomoran unik serial & kode aset |
| **Pemasangan PJU** (`/pemasangan-pju`) | ✅ CREATE | 👁️ READ | ✅ CREATE | ✅ CREATE | 👁️ READ | Transisi aset: GUDANG $\rightarrow$ TERPASANG |
| **Pencopotan PJU** (`/pencopotan-pju`) | ✅ CREATE | 👁️ READ | ✅ CREATE | ✅ CREATE | 👁️ READ | Transisi: TERPASANG $\rightarrow$ RUSAK/MAINTENANCE |
| **Maintenance Work Order** | ✅ CREATE/COMP | ✅ CREATE/COMP | 👁️ READ | ✅ CREATE/COMP | 👁️ READ | Pencatatan tindakan & konsumsi sparepart |
| **Garansi Vendor Tracking** | ✅ VIEW | ✅ VIEW | ✅ VIEW | 👁️ READ | 👁️ READ | Auto-status: BERLAKU, EXPIRING, EXPIRED |
| **Retur Vendor (Pengajuan)** | ✅ CREATE | 👁️ READ | ✅ CREATE | ❌ 403 | 👁️ READ | Aset rusak dikirim ke supplier |
| **Retur Vendor (Penyelesaian)** | ✅ COMPLETE | ✅ COMPLETE | ❌ 403 | ❌ 403 | ❌ 403 | Pemulihan aset kembali ke GUDANG |
| **Laporan Stok & Transaksi** | ✅ VIEW/EXP | ✅ VIEW/EXP | ✅ VIEW/EXP | ❌ 403 | ✅ VIEW/EXP | Filter dinamis, PDF stream, Excel download |
| **Ubah Password Sendiri** | ✅ ALLOWED | ✅ ALLOWED | ✅ ALLOWED | ✅ ALLOWED | ✅ ALLOWED | Verifikasi hash password aktif |

---

## 3. Keselarasan Frontend Directive vs Backend Enforcement

| Action / Endpoint | Frontend Blade Check | Middleware / Route | Controller / FormRequest Check |
|---|---|---|---|
| Tambah Barang | `@if(auth()->user()->isSuperAdmin() \|\| auth()->user()->hasRole(['admin gudang', 'kepala gudang']))` | `checkRole:superadmin,admin gudang,kepala gudang` | `BarangController::store` abort(403) |
| Edit/Hapus Barang | `canEdit`, `canDelete` JS flags via server role evaluate | `checkRole:superadmin,admin gudang,kepala gudang` | `BarangController::update/destroy` abort(403) |
| Inbound Masuk | Menu hanya tampil untuk Superadmin & Admin Gudang | `checkRole:superadmin,admin gudang` | `StoreBarangMasukRequest::authorize()` |
| Outbound Keluar | Menu hanya tampil untuk Superadmin & Admin Gudang | `checkRole:superadmin,admin gudang` | `StoreBarangKeluarRequest::authorize()` |
| Approve Opname | Tombol Setujui hanya di render untuk Manajemen | `checkRole:superadmin,kepala gudang` | `StockOpnameController::approve` abort(403) |
| Retur Complete | Modal Selesaikan hanya di render untuk Manajemen | `checkRole:superadmin,kepala gudang` | `ReturVendorController::complete` abort(403) |
| Pasang PJU | Menu Pemasangan untuk Teknisi, Admin, Superadmin | `checkRole:superadmin,admin gudang,teknisi` | `PemasanganPjuController::store` abort(403) |
| Maintenance WO | Menu Maintenance untuk Teknisi & Manajemen | `checkRole:superadmin,kepala gudang,teknisi` | `MaintenancePjuController::store/complete` |
| Laporan & Export | Menu Laporan disembunyikan dari Teknisi Lapangan | `checkRole:superadmin,kepala gudang,admin gudang,viewer` | Route group rejection (403) |
