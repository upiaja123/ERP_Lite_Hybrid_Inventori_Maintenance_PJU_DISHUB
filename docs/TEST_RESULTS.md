# TEST RESULTS REPORT
## ERP Lite Hybrid Inventori & Maintenance PJU — DISHUB

**Test Execution Date**: 2026-09-06  
**Environment**: Local Laragon (PHP 8.4.7 NTS, MySQL 8.0.30, Laravel 10.50.2)  
**Total Tests Executed**: 58 Feature & Unit Tests  
**Total Assertions**: 218 Assertions  
**Pass Rate**: 100% (58/58 Passed)  

---

## 1. Summary of Execution Results

| Test Suite / Category | Tests | Assertions | Status | Duration |
|---|:---:|:---:|:---:|:---:|
| **Unit Tests** (`Tests\Unit\ExampleTest`) | 1 | 1 | **PASS** | 0.04s |
| **Authentication & Session** (`Tests\Feature\Auth\*`) | 16 | 45 | **PASS** | 8.85s |
| **Critical Inventory Lifecycle** (`CriticalInventoryTest`) | 1 | 14 | **PASS** | 0.28s |
| **Inventory Ledger Concurrency** (`InventoryLedgerTest`) | 1 | 4 | **PASS** | 0.06s |
| **Master Data Integrity & Barcode** (`MasterDataTest`) | 4 | 18 | **PASS** | 0.38s |
| **PJU Asset Lifecycle Operations** (`PjuLifecycleTest`) | 1 | 24 | **PASS** | 0.29s |
| **User Profile Management** (`ProfileTest`) | 5 | 12 | **PASS** | 1.12s |
| **RBAC Core Logic** (`RbacTest`) | 3 | 7 | **PASS** | 0.21s |
| **Reporting & Audit Trail** (`ReportingAndAuditTest`) | 3 | 11 | **PASS** | 0.32s |
| **Role Permission Matrix & CRUD** (`RolePermissionMatrixTest`) | 10 | 40 | **PASS** | 2.12s |
| **Security, Rate Limit & API** (`SecurityAndApiTest`) | 4 | 18 | **PASS** | 0.32s |
| **UI Smoke Tests** (`SmokeTest`) | 2 | 4 | **PASS** | 0.12s |
| **Hybrid Sync & Integrations** (`SyncAndIntegrationTest`) | 4 | 20 | **PASS** | 0.33s |
| **TOTAL** | **58** | **218** | **100% PASS** | **15.26s** |

---

## 2. Detailed Test Matrix Register

| Test ID | Category | Role | Module | Scenario | Expected | Actual | Status | Defect Ref |
|---|---|---|---|---|---|---|:---:|:---:|
| **TC-AUTH-01** | Auth | Guest | Login | Render halaman login | HTTP 200 | HTTP 200 | **PASS** | - |
| **TC-AUTH-02** | Auth | User | Login | Autentikasi dengan kredensial valid | Redirect `/` / Dashboard | Redirect status 302 | **PASS** | - |
| **TC-AUTH-03** | Auth | User | Login | Autentikasi dengan password salah | Error session & stay login | HTTP 302 with errors | **PASS** | - |
| **TC-AUTH-04** | Auth | Guest | Register | Pendaftaran user baru dengan role `admin gudang` | User terbuat & redirect | User created, role assigned | **PASS** | DEF-004 |
| **TC-AUTH-05** | Auth | Guest | Register | Mencoba mendaftar dengan role `superadmin` | Validasi error 422 / forbidden | Rejection, no superadmin | **PASS** | - |
| **TC-RBAC-01** | Authorization | Superadmin | User Mgmt | Akses `/data-pengguna` | HTTP 200 OK | HTTP 200 | **PASS** | - |
| **TC-RBAC-02** | Authorization | Non-Super | User Mgmt | Akses `/data-pengguna` oleh Kepala Gudang, Admin, Teknisi, Viewer | HTTP 403 Forbidden | HTTP 403 | **PASS** | DEF-005 |
| **TC-RBAC-03** | Authorization | All Roles | Dashboard | Akses `/dashboard` oleh ke-5 role terdaftar | HTTP 200 OK untuk seluruh role | HTTP 200 untuk seluruh role | **PASS** | DEF-007 |
| **TC-RBAC-04** | Authorization | Teknisi | Reports | Akses `/laporan-stok` oleh Teknisi Lapangan | HTTP 403 Forbidden | HTTP 403 | **PASS** | DEF-005 |
| **TC-RBAC-05** | Authorization | Viewer | Reports | Akses `/laporan-stok` oleh Viewer / Pengawas | HTTP 200 OK | HTTP 200 | **PASS** | DEF-005 |
| **TC-RBAC-06** | Authorization | All Roles | Master Data | Akses `GET /barang` oleh ke-5 role terdaftar | HTTP 200 OK | HTTP 200 | **PASS** | DEF-005 |
| **TC-RBAC-07** | Authorization | Teknisi, Viewer | Master Data | Kirim `POST /barang` untuk manipulasi data master | HTTP 403 Forbidden | HTTP 403 | **PASS** | DEF-005 |
| **TC-RBAC-08** | Authorization | Admin, Teknisi | Opname Approval | Kirim `POST /stock-opname/{id}/approve` | HTTP 403 Forbidden | HTTP 403 | **PASS** | DEF-006 |
| **TC-RBAC-09** | Authorization | Kepala Gudang | Opname Approval | Kirim `POST /stock-opname/{id}/approve` | HTTP 200 OK & Ledger updated | HTTP 200 OK | **PASS** | DEF-006 |
| **TC-RBAC-10** | Authorization | Teknisi | Pemasangan | Kirim `POST /pemasangan-pju` untuk aset di gudang | HTTP 200 OK & status TERPASANG | HTTP 200 OK | **PASS** | - |
| **TC-RBAC-11** | Authorization | Viewer | Pemasangan | Kirim `POST /pemasangan-pju` oleh akun Viewer | HTTP 403 Forbidden | HTTP 403 | **PASS** | DEF-005 |
| **TC-RBAC-12** | Authorization | Teknisi, Viewer | Inbound | Kirim `POST /barang-masuk` | HTTP 403 Forbidden | HTTP 403 | **PASS** | DEF-005 |
| **TC-RBAC-13** | Authorization | Admin Gudang | Inbound | Kirim `POST /barang-masuk` dokumen valid | HTTP 200 OK & Stok bertambah | HTTP 200 OK | **PASS** | - |
| **TC-RBAC-14** | Authorization | Admin, Teknisi | Retur Complete | Kirim `POST /retur-vendor/{id}/complete` | HTTP 403 Forbidden | HTTP 403 | **PASS** | DEF-006 |
| **TC-RBAC-15** | Authorization | Kepala Gudang | Retur Complete | Kirim `POST /retur-vendor/{id}/complete` | HTTP 200 OK & Aset kembali GUDANG | HTTP 200 OK | **PASS** | DEF-006 |
| **TC-INV-01** | Inventory Logic | Admin Gudang | Ledger | Inbound +20, Outbound -30 pada saldo 100 (Saldo akhir = 90) | Saldo = 90 | Saldo = 90 | **PASS** | - |
| **TC-INV-02** | Inventory Logic | Admin Gudang | Negative Stock | Outbound 11 saat stok hanya 10 | Exception InsufficientStock & Rollback | Exception 422, stok tetap 10 | **PASS** | - |
| **TC-INV-03** | Inventory Logic | Kepala Gudang | Opname Diff | Fisik 88 vs Sistem 90, approved adjustment | Selisih -2 diposting, stok = 88 | Stok = 88 | **PASS** | - |
| **TC-PJU-01** | Lifecycle | Teknisi | PJU Pasang | Aset berstatus GUDANG dipasang di tiang | Status berubah TERPASANG, lokasi terisi | Status TERPASANG | **PASS** | - |
| **TC-PJU-02** | Lifecycle | Teknisi | PJU Copot | Aset TERPASANG dicopot karena RUSAK_BERAT | Status berubah RUSAK | Status RUSAK | **PASS** | - |
| **TC-PJU-03** | Lifecycle | Admin Gudang | Retur Inisiasi | Aset RUSAK diretur ke Vendor | Status berubah RETUR | Status RETUR | **PASS** | - |
| **TC-PJU-04** | Lifecycle | Kepala Gudang | Retur Selesai | Aset retur diselesaikan (SELESAI_GANTI) | Status dipulihkan ke GUDANG, lokasi NULL | Status GUDANG | **PASS** | - |
| **TC-SEC-01** | Security | Guest | Protected URL | Akses langsung `/barang` tanpa session | Redirect `/login` (302) | Redirect `/login` | **PASS** | - |
| **TC-SEC-02** | Security | Admin Gudang | File Upload | Upload payload PHP palsu (`exploit.php`) | HTTP 422 Validation Error | HTTP 422 | **PASS** | - |
| **TC-SEC-03** | Security | Guest | API v1 | Akses `/api/v1/barang` | HTTP 200, RateLimit Header, Tanpa Secret | 200 OK, No Passwords/Tokens | **PASS** | - |
| **TC-SYNC-01** | Integration | System | Google Sync | Simulasi API Google offline/timeout | Transaksi lokal sukses, sync flag logged | Transaksi sukses, no crash | **PASS** | - |
| **TC-SYNC-02** | Integration | System | Google Sync | Deteksi konflik versi remote vs lokal | Status CONFLICT dicatat, no silent overwrite | CONFLICT logged | **PASS** | - |
