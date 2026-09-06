# FINAL ROLE & PERMISSION MATRIX
## ERP LITE HYBRID INVENTORI & MAINTENANCE PJU — DISHUB

**Version**: 2.0 (Post-Compliance Verification)  
**Date**: 2026-09-06  
**Status**: VERIFIED & TESTED  

---

## 1. DAFTAR ROLE & KANONIKAL KELOMPOK

Sistem mendukung 8 peran (kombinasi nama arsitektural master dan nama operasional lapangan yang dipetakan secara kanonikal):

1. **Super Admin (`superadmin` / `Super Admin`)**: Pengendali mutlak arsitektur, konfigurasi sistem, RBAC, dan darurat bypass.
2. **System Administrator (`System Administrator`)**: Rekanan arsitektural Super Admin untuk pemeliharaan teknis, integrasi, dan audit.
3. **Admin Gudang (`admin gudang`)**: Operator persediaan harian untuk pencatatan barang masuk/keluar, master barang, dan opname fisik.
4. **Warehouse Manager (`Warehouse Manager` / `kepala gudang`)**: Penanggung jawab operasional gudang, verifikator persetujuan (approval) opname, penyelesaian retur vendor, dan inspeksi ledger.
5. **Teknisi (`teknisi`)**: Petugas lapangan penanganan tiang PJU, pemasangan unit, pencopotan unit rusak, dan pengerjaan Work Order maintenance.
6. **Department Manager (`Department Manager`)**: Pimpinan unit/dinas berwenang melakukan persetujuan administratif dan penarikan seluruh laporan rekonsiliasi tanpa hak manipulasi fisik gudang.
7. **Viewer (`viewer`)**: Akun auditor/pengawas eksternal murni baca (*read-only*) tanpa hak transaksi atau modifikasi data.

---

## 2. MATRIKS UTAMA: ROLE × MODUL × AKSI (10 AKSI)

**Legenda Status**:
- **✅ ALLOWED**: Diizinkan secara bisnis, ditegakkan oleh middleware/policy, dan teruji lolos.
- **❌ 403 / DENIED**: Dilarang oleh otorisasi backend dan tombol disembunyikan pada antarmuka.
- **👁️ VIEW ONLY**: Hanya dapat melihat ringkasan atau detail data.
- **N/A**: Aksi tidak relevan pada modul tersebut (contoh: persetujuan pada modul master data).

### 2.1 Super Admin & System Administrator
| Modul | VIEW | CREATE | READ DETAIL | UPDATE | DELETE | APPROVE | REJECT | VOID | EXPORT | IMPORT | Evaluasi Paritas (Doc vs Actual vs Tested) |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|---|
| **Dashboard** | ✅ | N/A | ✅ | N/A | N/A | N/A | N/A | N/A | N/A | N/A | MATCH (Akses menyeluruh) |
| **Master Barang & Spesifikasi** | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | N/A | N/A | ✅ | ✅ | MATCH (CRUD penuh) |
| **Barang Masuk** | ✅ | ✅ | ✅ | ✅ | ❌ (Gunakan Void) | ✅ | ✅ | ✅ | ✅ | ✅ | MATCH (Void menggantikan Delete) |
| **Barang Keluar** | ✅ | ✅ | ✅ | ✅ | ❌ (Gunakan Void) | ✅ | ✅ | ✅ | ✅ | ✅ | MATCH (Void menggantikan Delete) |
| **Stok (Ledger)** | ✅ | N/A | ✅ | ❌ (Append-Only) | ❌ (Immutable) | N/A | N/A | N/A | ✅ | N/A | MATCH (Integritas ledger terjaga) |
| **Stock Opname** | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ | N/A | ✅ | N/A | MATCH (Approve/Reject aktif) |
| **Mutasi Stok** | ✅ | ✅ | ✅ | N/A | ❌ | N/A | N/A | N/A | ✅ | N/A | MATCH (Mutasi tercatat di ledger) |
| **Maintenance & WO** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | N/A | MATCH (Kelola penuh WO) |
| **Pencopotan PJU** | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | N/A | N/A | ✅ | N/A | MATCH (Transisi ke RUSAK) |
| **Pemasangan PJU** | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | N/A | N/A | ✅ | N/A | MATCH (Transisi ke TERPASANG) |
| **Warranty / Garansi** | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | N/A | N/A | ✅ | N/A | MATCH (Tracking masa berlaku) |
| **Retur Vendor** | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ (Complete) | ✅ | N/A | ✅ | N/A | MATCH (Penyelesaian klaim) |
| **Reporting** | ✅ | N/A | ✅ | N/A | N/A | N/A | N/A | N/A | ✅ | N/A | MATCH (PDF & Excel aktif) |
| **Audit Trail** | ✅ | N/A | ✅ | ❌ | ❌ (Immutable) | N/A | N/A | N/A | ✅ | N/A | MATCH (Delete dilarang keras) |
| **User Management** | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | N/A | N/A | ✅ | N/A | MATCH (Khusus Superadmin) |
| **Integration / Excel** | ✅ | ✅ | ✅ | N/A | N/A | ✅ | ✅ | N/A | ✅ | ✅ | MATCH (Pipeline migrasi) |
| **Sync (Google Sheets)** | ✅ | ✅ (Trigger) | ✅ | N/A | N/A | N/A | N/A | N/A | N/A | N/A | MATCH (Async trigger) |

---

### 2.2 Warehouse Manager (`kepala gudang`)
| Modul | VIEW | CREATE | READ DETAIL | UPDATE | DELETE | APPROVE | REJECT | VOID | EXPORT | IMPORT | Evaluasi Paritas (Doc vs Actual vs Tested) |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|---|
| **Dashboard** | ✅ | N/A | ✅ | N/A | N/A | N/A | N/A | N/A | N/A | N/A | MATCH (Widget operasional gudang) |
| **Master Barang & Spesifikasi** | ✅ | ✅ | ✅ | ✅ | ❌ 403 | N/A | N/A | N/A | ✅ | ❌ 403 | MATCH (Tidak dapat hapus master) |
| **Barang Masuk** | ✅ | 👁️ | ✅ | ❌ 403 | ❌ 403 | ✅ | ✅ | ✅ | ✅ | ❌ 403 | MATCH (Fokus supervisi/approval) |
| **Barang Keluar** | ✅ | 👁️ | ✅ | ❌ 403 | ❌ 403 | ✅ | ✅ | ✅ | ✅ | ❌ 403 | MATCH (Fokus supervisi/approval) |
| **Stok (Ledger)** | ✅ | N/A | ✅ | ❌ | ❌ | N/A | N/A | N/A | ✅ | N/A | MATCH (Audit trail stok) |
| **Stock Opname** | ✅ | 👁️ | ✅ | ❌ 403 | ❌ 403 | ✅ | ✅ | N/A | ✅ | N/A | MATCH (Pemegang hak approval) |
| **Mutasi Stok** | ✅ | 👁️ | ✅ | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ✅ | N/A | MATCH (Supervisi perpindahan) |
| **Maintenance & WO** | ✅ | ✅ | ✅ | ✅ | ❌ 403 | ✅ | ✅ | N/A | ✅ | N/A | MATCH (Pengawasan perbaikan) |
| **Pencopotan PJU** | ✅ | 👁️ | ✅ | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ✅ | N/A | MATCH (Monitoring copotan) |
| **Pemasangan PJU** | ✅ | 👁️ | ✅ | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ✅ | N/A | MATCH (Monitoring instalasi) |
| **Warranty / Garansi** | ✅ | 👁️ | ✅ | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ✅ | N/A | MATCH (Monitoring status garansi) |
| **Retur Vendor** | ✅ | 👁️ | ✅ | ❌ 403 | ❌ 403 | ✅ (Complete) | ✅ | N/A | ✅ | N/A | MATCH (Pelaksana selesai retur) |
| **Reporting** | ✅ | N/A | ✅ | N/A | N/A | N/A | N/A | N/A | ✅ | N/A | MATCH (Akses penuh seluruh laporan) |
| **Audit Trail** | ✅ | N/A | ✅ | ❌ | ❌ | N/A | N/A | N/A | ❌ | N/A | MATCH (Hanya view riwayat) |
| **User Management** | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ❌ 403 | N/A | MATCH (Dilarang keras) |
| **Integration / Excel** | ✅ | ❌ 403 | ✅ | N/A | N/A | ✅ (Verify) | ❌ 403 | N/A | ✅ | ❌ 403 | MATCH (Review & approve migrasi) |
| **Sync (Google Sheets)** | 👁️ | ❌ 403 | 👁️ | N/A | N/A | N/A | N/A | N/A | N/A | N/A | MATCH (Read status sync) |

---

### 2.3 Admin Gudang (`admin gudang`)
| Modul | VIEW | CREATE | READ DETAIL | UPDATE | DELETE | APPROVE | REJECT | VOID | EXPORT | IMPORT | Evaluasi Paritas (Doc vs Actual vs Tested) |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|---|
| **Dashboard** | ✅ | N/A | ✅ | N/A | N/A | N/A | N/A | N/A | N/A | N/A | MATCH (Widget stok & transaksi) |
| **Master Barang & Spesifikasi** | ✅ | ✅ | ✅ | ✅ | ❌ 403 | N/A | N/A | N/A | ✅ | ❌ 403 | MATCH (Pengelola barang harian) |
| **Barang Masuk** | ✅ | ✅ | ✅ | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ✅ | ❌ 403 | MATCH (Entri transaksi fisik) |
| **Barang Keluar** | ✅ | ✅ | ✅ | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ✅ | ❌ 403 | MATCH (Validasi stok otomatis) |
| **Stok (Ledger)** | ✅ | N/A | ✅ | ❌ | ❌ | N/A | N/A | N/A | ✅ | N/A | MATCH (Melihat posisi persediaan) |
| **Stock Opname** | ✅ | ✅ (Fisik) | ✅ | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | N/A | ✅ | N/A | MATCH (Input fisik, no self-approve) |
| **Mutasi Stok** | ✅ | ✅ | ✅ | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ✅ | N/A | MATCH (Pencatatan perpindahan) |
| **Maintenance & WO** | 👁️ | ❌ 403 | 👁️ | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | N/A | 👁️ | N/A | MATCH (Hanya melihat WO) |
| **Pencopotan PJU** | 👁️ | ❌ 403 | 👁️ | ❌ 403 | ❌ 403 | N/A | N/A | N/A | 👁️ | N/A | MATCH (Melihat unit copotan) |
| **Pemasangan PJU** | 👁️ | ❌ 403 | 👁️ | ❌ 403 | ❌ 403 | N/A | N/A | N/A | 👁️ | N/A | MATCH (Melihat unit terpasang) |
| **Warranty / Garansi** | ✅ | ❌ 403 | ✅ | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ✅ | N/A | MATCH (Pengecekan garansi barang) |
| **Retur Vendor** | ✅ | ✅ (Inisiasi) | ✅ | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | N/A | ✅ | N/A | MATCH (Pengajuan retur barang rusak) |
| **Reporting** | ✅ | N/A | ✅ | N/A | N/A | N/A | N/A | N/A | ✅ | N/A | MATCH (Laporan stok harian) |
| **Audit Trail** | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ❌ 403 | N/A | MATCH (Isolasi penuh) |
| **User Management** | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ❌ 403 | N/A | MATCH (Dilarang keras) |
| **Integration / Excel** | ❌ 403 | ❌ 403 | ❌ 403 | N/A | N/A | ❌ 403 | ❌ 403 | N/A | ❌ 403 | ❌ 403 | MATCH (Dikelola manajemen) |
| **Sync (Google Sheets)** | ❌ 403 | ❌ 403 | ❌ 403 | N/A | N/A | N/A | N/A | N/A | N/A | N/A | MATCH (Dilarang) |

---

### 2.4 Teknisi (`teknisi`)
| Modul | VIEW | CREATE | READ DETAIL | UPDATE | DELETE | APPROVE | REJECT | VOID | EXPORT | IMPORT | Evaluasi Paritas (Doc vs Actual vs Tested) |
|---|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|---|
| **Dashboard** | ✅ | N/A | ✅ | N/A | N/A | N/A | N/A | N/A | N/A | N/A | MATCH (Widget tugas lapangan) |
| **Master Barang & Spesifikasi** | 👁️ | ❌ 403 | 👁️ | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ❌ 403 | ❌ 403 | MATCH (Read-only spesifikasi PJU) |
| **Barang Masuk** | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | MATCH (Bukan domain teknisi) |
| **Barang Keluar** | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | MATCH (Bukan domain teknisi) |
| **Stok (Ledger)** | ❌ 403 | N/A | ❌ 403 | ❌ | ❌ | N/A | N/A | N/A | ❌ 403 | N/A | MATCH (Bukan domain teknisi) |
| **Stock Opname** | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | N/A | ❌ 403 | N/A | MATCH (Bukan domain teknisi) |
| **Mutasi Stok** | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ❌ 403 | N/A | MATCH (Bukan domain teknisi) |
| **Maintenance & WO** | ✅ | ✅ | ✅ | ✅ (Selesai) | ❌ 403 | ❌ 403 | ❌ 403 | N/A | ❌ 403 | N/A | MATCH (Eksekusi perbaikan) |
| **Pencopotan PJU** | ✅ | ✅ | ✅ | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ❌ 403 | N/A | MATCH (Pencatatan pelepasan tiang) |
| **Pemasangan PJU** | ✅ | ✅ | ✅ | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ❌ 403 | N/A | MATCH (Pencatatan instalasi tiang) |
| **Warranty / Garansi** | 👁️ | ❌ 403 | 👁️ | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ❌ 403 | N/A | MATCH (Pengecekan di lapangan) |
| **Retur Vendor** | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | N/A | ❌ 403 | N/A | MATCH (Bukan domain teknisi) |
| **Reporting** | ❌ 403 | N/A | ❌ 403 | N/A | N/A | N/A | N/A | N/A | ❌ 403 | N/A | MATCH (Menu laporan disembunyikan) |
| **Audit Trail** | ❌ 403 | N/A | ❌ 403 | ❌ | ❌ | N/A | N/A | N/A | ❌ 403 | N/A | MATCH (Dilarang keras) |
| **User Management** | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | ❌ 403 | N/A | N/A | N/A | ❌ 403 | N/A | MATCH (Dilarang keras) |
| **Integration / Excel** | ❌ 403 | ❌ 403 | ❌ 403 | N/A | N/A | ❌ 403 | ❌ 403 | N/A | ❌ 403 | ❌ 403 | MATCH (Dilarang keras) |
| **Sync (Google Sheets)** | ❌ 403 | ❌ 403 | ❌ 403 | N/A | N/A | N/A | N/A | N/A | N/A | N/A | MATCH (Dilarang keras) |

---

### 2.5 Department Manager & Viewer
| Modul | Department Manager (Approval & Report) | Viewer (Read-Only Observer) | Evaluasi Paritas |
|---|:---:|:---:|---|
| **Dashboard** | ✅ VIEW Ringkasan Eksekutif | ✅ VIEW Ringkasan Eksekutif | MATCH |
| **Master Barang** | 👁️ READ ONLY | 👁️ READ ONLY | MATCH |
| **Barang Masuk/Keluar** | 👁️ READ + ✅ APPROVE | 👁️ READ ONLY (No Approve) | MATCH (Dept Mgr can approve) |
| **Stock Opname** | 👁️ READ + ✅ APPROVE | 👁️ READ ONLY (No Approve) | MATCH (Dept Mgr can approve) |
| **PJU Operations** | 👁️ READ ONLY | 👁️ READ ONLY | MATCH |
| **Reporting & Export** | ✅ VIEW & EXPORT (PDF/Excel) | ✅ VIEW ONLY (Export dilarang) | MATCH (Dokumentasi Bagian 5) |
| **Audit Trail** | ✅ VIEW Riwayat Sistem | ❌ 403 | MATCH |
| **User Management** | ❌ 403 | ❌ 403 | MATCH |

---

## 3. HASIL VERIFIKASI TEMUAN & PARITAS IMPLEMENTASI

1. **Seluruh Rute Terproteksi**: Tidak ditemukan endpoint tanpa proteksi middleware otentikasi dan otorisasi per peran.
2. **Kesesuaian Tombol UI vs Backend**:
   - Tombol **Tambah Barang**, **Edit**, **Hapus** hanya dirender jika `auth()->user()->isSuperAdmin() || auth()->user()->hasRole(['admin gudang', 'kepala gudang'])`.
   - Tombol **Setujui Opname** hanya dirender untuk manajemen (`kepala gudang`, `superadmin`, `Department Manager`).
   - Tombol **Selesaikan Retur** hanya dirender untuk manajemen gudang.
3. **Pemberian Hak Akses REST API**:
   - API `/api/v1/barang`, `/api/v1/pju-assets`, `/api/v1/stock` memverifikasi otorisasi Bearer Token Sanctum sesuai level peran pemanggil.
